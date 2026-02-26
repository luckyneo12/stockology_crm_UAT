<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\ChatGroup;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\MessengerGroup;
use App\Models\MessengerGroupMember;

class MessagingController extends Controller
{
    public function __construct()
    {
        // Remove permission check - allow all authenticated users to use messenger
        $this->middleware('auth');
    }

    public function index()
    {
        return view('messenger.index');
    }

    public function getUsers(Request $request)
    {
        try {
            $currentUser = Auth::user();
            $search = $request->get('search');

            if (!$currentUser) {
                return response()->json(['error' => 'No authenticated user'], 401);
            }

            $userType = strtolower($currentUser->type);

            if ($userType === 'super admin') {
                $usersQuery = User::where('id', '!=', $currentUser->id)
                    ->where('type', 'company');
            }
            else {
                $usersQuery = User::where('created_by', creatorId())
                    ->where('id', '!=', $currentUser->id);

                if ($currentUser->isAbleTo('workspace manage')) {
                    $usersQuery->where('workspace_id', getActiveWorkSpace());
                }
            }

            if (!empty($search)) {
                $usersQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            }

            $regularUsers = $usersQuery->get();

            $conversationUserIds = Message::where(function ($query) {
                $query->where('from_id', Auth::id())
                    ->orWhere('to_id', Auth::id());
            })->selectRaw('CASE WHEN from_id = ? THEN to_id ELSE from_id END as user_id', [Auth::id()])
                ->distinct()
                ->pluck('user_id');

            $conversationUsers = collect();
            if ($conversationUserIds->count() > 0) {
                $conversationUsers = User::whereIn('id', $conversationUserIds)
                    ->where('type', '!=', 'super admin')
                    ->where('type', '!=', 'Super Admin')
                    ->where('id', '!=', $currentUser->id)
                    ->get();
            }

            $allUsers = $regularUsers->merge($conversationUsers)->unique('id');

            $mappedUsers = $allUsers->map(function ($user) {
                if (!$user)
                    return null;

                $lastMessage = Message::where(function ($q) use ($user) {
                        $q->where(function ($query) use ($user) {
                                $query->where('from_id', Auth::id())->where('to_id', $user->id);
                            }
                            )->orWhere(function ($query) use ($user) {
                                $query->where('from_id', $user->id)->where('to_id', Auth::id());
                            }
                            );
                        }
                        )
                            ->latest()
                            ->first();

                        $unreadCount = Message::where('from_id', $user->id)
                            ->where('to_id', Auth::id())
                            ->where('is_seen', 0)
                            ->count();

                        $isOnline = false;
                        $statusText = 'Offline';
                        if ($user->last_seen) {
                            $isOnline = \Carbon\Carbon::parse($user->last_seen)->diffInMinutes() < 5;
                            $statusText = $isOnline ? 'Online' : 'Last seen ' . \Carbon\Carbon::parse($user->last_seen)->diffForHumans();
                        }

                        return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $user->avatar ? get_file($user->avatar) : asset('assets/images/user/avatar-1.jpg'),
                        'is_online' => $isOnline,
                        'last_seen' => $user->last_seen ?\Carbon\Carbon::parse($user->last_seen)->diffForHumans() : 'Never',
                        'status_text' => $statusText,
                        'unread_count' => $unreadCount,
                        'last_message' => $lastMessage ? ($lastMessage->body ?: 'Attachment') : 'Start a conversation...',
                        'last_message_time' => $lastMessage ? $lastMessage->created_at->timestamp : 0,
                        'last_message_datetime' => $lastMessage ? $lastMessage->created_at->diffForHumans() : null,
                        ];
                    })->filter()->sortByDesc('last_message_time')->values();

            \Log::info('Messenger Users Loaded Successfully', [
                'user_id' => $currentUser->id,
                'count' => $mappedUsers->count()
            ]);

            return response()->json(['users' => $mappedUsers]);

        }
        catch (\Throwable $e) {
            \Log::error('Messenger getUsers Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return response()->json(['error' => 'Internal Server Error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getMessages($userId, Request $request)
    {
        try {
            // First check if user exists and is not trying to message themselves
            $currentUser = Auth::user();
            if (!$currentUser) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            if ($currentUser->id == $userId) {
                return response()->json(['error' => 'Cannot message yourself'], 400);
            }

            // Check if target user exists
            $targetUser = User::find($userId);
            if (!$targetUser) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Get messages without relationships first to avoid potential issues
            $messages = Message::where(function ($query) use ($userId) {
                $query->where('from_id', Auth::id())->where('to_id', $userId)
                    ->orWhere('from_id', $userId)->where('to_id', Auth::id());
            })->orderBy('created_at', 'asc')
                ->get();

            // Add user information to each message
            $messagesWithUsers = $messages->map(function ($message) {
                if (!$message)
                    return null;

                $fromUser = User::find($message->from_id);
                $toUser = User::find($message->to_id);

                $replyMessage = null;
                if ($message->reply_to) {
                    $foundReply = Message::find($message->reply_to);
                    if ($foundReply) {
                        $replyMessage = $foundReply->load('fromUser');
                    }
                }

                return [
                'id' => $message->id,
                'from_id' => $message->from_id,
                'to_id' => $message->to_id,
                'body' => $message->body,
                'file_path' => $message->file_path,
                'file_url' => $message->file_path ?\Illuminate\Support\Facades\Storage::disk('public')->url($message->file_path) : null,
                'file_name' => $message->file_name,
                'file_type' => $message->file_type,
                'file_size' => $message->file_size ? $message->formatted_file_size : null,
                'created_at' => $message->created_at,
                'updated_at' => $message->updated_at,
                'from_user' => $fromUser ? [
                'id' => $fromUser->id,
                'name' => $fromUser->name,
                'email' => $fromUser->email,
                'avatar' => $fromUser->avatar ? get_file($fromUser->avatar) : null
                ] : null,
                'to_user' => $toUser ? [
                'id' => $toUser->id,
                'name' => $toUser->name,
                'email' => $toUser->email,
                'avatar' => $toUser->avatar ? get_file($toUser->avatar) : null
                ] : null,
                'reply_to' => $message->reply_to,
                'reply_message' => $replyMessage
                ];
            })->filter()->values();

            // Mark messages as read
            Message::where('from_id', $userId)
                ->where('to_id', Auth::id())
                ->where('is_seen', 0)
                ->update(['is_seen' => 1]);

            return response()->json($messagesWithUsers);

        }
        catch (\Throwable $e) {
            \Log::error('Messenger getMessages Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'target_user_id' => $userId
            ]);
            return response()->json(['error' => 'Internal Server Error', 'message' => $e->getMessage()], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'to_id' => 'required|exists:users,id',
            'message' => 'required_without:file|string|max:1000',
            'file' => 'nullable|file|max:20480|mimes:pdf,csv,xlsx,xls,jpg,jpeg,png,gif',
            'reply_to' => 'nullable|exists:user_messages,id' // Add reply validation
        ]);

        $message = new Message();
        $message->from_id = Auth::id();
        $message->to_id = $request->to_id;
        $message->body = $request->message;

        // Handle reply_to
        if ($request->has('reply_to')) {
            $message->reply_to = $request->reply_to;
        }

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Validate based on type
            $mime = $file->getMimeType();
            $size = $file->getSize();

            if (str_starts_with($mime, 'image/')) {
                if ($size > 20 * 1024 * 1024) { // 20MB
                    return response()->json(['error' => 'Image size must be less than 20MB'], 422);
                }
            }
            else {
                if ($size > 50 * 1024 * 1024) { // 50MB
                    return response()->json(['error' => 'File size must be less than 50MB'], 422);
                }
            }

            $filename = time() . '_' . $file->getClientOriginalName();
            $filepath = $file->storeAs('chat_files', $filename, 'public');
            $message->file_path = $filepath;
            $message->file_name = $file->getClientOriginalName();
            $message->file_size = $size;
            $message->file_type = $mime;
        }

        $message->save();

        // Update user's online status when sending message
        $user = Auth::user();
        $user->is_online = true;
        $user->last_seen = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => $message->load(['fromUser', 'toUser', 'replyMessage']) // Load reply relationship
        ]);
    }



    public function getUnreadCount()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['count' => 0]);
            }

            $unreadCount = Message::where('to_id', $user->id)
                ->where('is_seen', 0)
                ->count();

            return response()->json(['count' => $unreadCount]);
        }
        catch (\Exception $e) {
            \Log::error('Unread count error: ' . $e->getMessage());
            return response()->json(['count' => 0]);
        }
    }

    public function getLatestUnread()
    {
        // Get groups user belongs to
        $userGroupIds = GroupMember::where('user_id', Auth::id())->pluck('group_id');

        // Get latest unread messages (Direct + Group)
        $unreadMessages = Message::where(function ($q) use ($userGroupIds) {
            // Direct messages
            $q->where('to_id', Auth::id());

            // Group messages (if member)
            if ($userGroupIds->isNotEmpty()) {
                $q->orWhere(function ($gq) use ($userGroupIds) {
                            $gq->whereIn('group_id', $userGroupIds)
                                ->where('from_id', '!=', Auth::id());
                        }
                        );
                    }
                })
            ->where('is_seen', 0)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($message) {
            // Load sender info manually
            $fromUser = User::find($message->from_id);

            return [
            'id' => $message->id,
            'from_id' => $message->from_id,
            'from_name' => $fromUser ? $fromUser->name : 'Unknown User',
            'body' => $message->body ?: ($message->file_path ? 'Sent an attachment' : 'Message'),
            'created_at' => $message->created_at,
            'time_ago' => $message->created_at->diffForHumans()
            ];
        });

        return response()->json([
            'unread_messages' => $unreadMessages,
            'total_unread' => $unreadMessages->count()
        ]);
    }

    public function updateLastSeen()
    {
        // Update current user's last seen timestamp
        $user = Auth::user();
        $user->last_seen = now();
        $user->save();

        return response()->json(['success' => true, 'last_seen' => $user->last_seen]);
    }

    public function downloadFile($messageId)
    {
        $message = Message::where(function ($query) use ($messageId) {
            $query->where('id', $messageId)
                ->where(function ($q) {
                $q->where('from_id', Auth::id())
                    ->orWhere('to_id', Auth::id());
            }
            );
        })->whereNotNull('file_path')
            ->firstOrFail();

        $filePath = storage_path('app/public/' . $message->file_path);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, $message->file_name);
    }

    public function markRead($id)
    {
        Message::where('from_id', $id)
            ->where('to_id', Auth::user()->id)
            ->where('read_at', null)
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    // Group Methods
    public function getGroups(Request $request)
    {
        $currentUser = Auth::user();
        $search = $request->get('search');

        $groups = ChatGroup::whereHas('members', function ($query) use ($currentUser) {
            $query->where('user_id', $currentUser->id);
        });

        if (!empty($search)) {
            $groups->where('name', 'like', "%$search%");
        }

        // Debug logging
        \Log::info('Fetching groups for user: ' . $currentUser->id);
        \Log::info('Query SQL: ' . $groups->toSql());
        \Log::info('Query Bindings: ' . json_encode($groups->getBindings()));

        $groups = $groups->with(['members' => function ($query) {
            $query->select('user_id', 'group_id');
        }])->get();

        \Log::info('Groups found: ' . $groups->pluck('id')->implode(', '));

        // Add member count and unread count
        foreach ($groups as $group) {
            $group->member_count = $group->members->count();
            $group->unread_count = Message::where('group_id', $group->id)
                ->where('is_seen', 0)
                ->where('from_id', '!=', $currentUser->id)
                ->count();

            $lastMsg = Message::where('group_id', $group->id)->latest()->first();
            if ($lastMsg) {
                $group->last_message = $lastMsg->body ?: 'Attachment';
                $group->last_message_time = $lastMsg->created_at->diffForHumans();
                $group->last_message_datetime = $lastMsg->created_at->diffForHumans(); // For compatibility
            }
            else {
                $group->last_message = 'Start a group conversation...';
                $group->last_message_time = '';
                $group->last_message_datetime = null;
            }
        }

        return response()->json($groups);
    }

    public function createGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id'
        ]);

        $currentUser = Auth::user();

        // Check if user has permission to create groups
        if (!$currentUser->isAbleTo('messenger group create') && $currentUser->type !== 'super admin' && $currentUser->type !== 'company') {
            return response()->json(['error' => 'Permission Denied'], 403);
        }

        DB::beginTransaction();
        try {
            // Create group
            $group = new ChatGroup();
            $group->name = $request->name;
            $group->description = $request->description;
            $group->created_by = $currentUser->id;
            $group->workspace_id = $currentUser->active_workspace;

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('messenger/groups', $fileName, 'public');
                $group->avatar = $path;
            }

            $group->save();

            // Add creator as admin member
            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $currentUser->id,
                'role' => 'admin'
            ]);

            // Add selected members
            foreach ($request->members as $memberId) {
                GroupMember::create([
                    'group_id' => $group->id,
                    'user_id' => $memberId,
                    'role' => 'member'
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'group' => $group]);

        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create group'], 500);
        }
    }

    public function getGroupMessages($id)
    {
        $currentUser = Auth::user();

        // Check if user is member of the group
        $isMember = GroupMember::where('group_id', $id)
            ->where('user_id', $currentUser->id)
            ->exists();

        if (!$isMember) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Mark messages as read
        Message::where('group_id', $id)
            ->where('from_id', '!=', $currentUser->id)
            ->where('is_seen', 0)
            ->update(['is_seen' => 1]);

        $messages = Message::where('group_id', $id)
            ->with('fromUser')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function sendGroupMessage(Request $request)
    {
        $request->validate([
            'to_id' => 'required|exists:messenger_groups,id',
            'body' => 'required_without:attachment',
            'attachment' => 'nullable|file|max:10240'
        ]);

        $currentUser = Auth::user();
        $group = ChatGroup::findOrFail($request->to_id);

        // Check if user is member of the group
        $isMember = GroupMember::where('group_id', $request->to_id)
            ->where('user_id', $currentUser->id)
            ->exists();

        if (!$isMember) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $mime = $file->getMimeType();
            $isImage = str_starts_with($mime, 'image/');

            // Check group settings
            if ($isImage && !$group->allow_images) {
                return response()->json(['error' => 'Image sharing is disabled in this group'], 422);
            }
            if (!$isImage && !$group->allow_files) {
                return response()->json(['error' => 'File sharing is disabled in this group'], 422);
            }

            // Validate size
            $size = $file->getSize();
            if ($isImage) {
                if ($size > 20 * 1024 * 1024) { // 20MB
                    return response()->json(['error' => 'Image size must be less than 20MB'], 422);
                }
            }
            else {
                if ($size > 50 * 1024 * 1024) { // 50MB
                    return response()->json(['error' => 'File size must be less than 50MB'], 422);
                }
            }

            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('messenger/attachments', $fileName, 'public');
            $attachment = $path;
        }

        $message = new Message();
        $message->from_id = $currentUser->id;
        $message->group_id = $request->to_id;
        $message->body = $request->body;
        $message->file_path = $attachment ?? null;
        $message->file_name = $fileName ?? null;
        $message->file_type = $mime ?? null;
        $message->file_size = $size ?? null;
        $message->workspace_id = $currentUser->active_workspace;
        $message->save();

        return response()->json([
            'success' => true,
            'message' => $message->load(['fromUser']) // Load necessary relationships
        ]);
    }

    public function addUserMembers(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:messenger_groups,id',
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id'
        ]);

        $currentUser = Auth::user();
        $group = ChatGroup::findOrFail($request->group_id);

        // Check if current user is admin/creator
        $isAdmin = GroupMember::where('group_id', $group->id)
            ->where('user_id', $currentUser->id)
            ->where('role', 'admin')
            ->exists();

        if (!$isAdmin && $group->created_by != $currentUser->id) {
            return response()->json(['error' => 'Only admins can add members'], 403);
        }

        DB::beginTransaction();
        try {
            foreach ($request->members as $memberId) {
                // Check if already a member
                $exists = GroupMember::where('group_id', $group->id)
                    ->where('user_id', $memberId)
                    ->exists();

                if (!$exists) {
                    GroupMember::create([
                        'group_id' => $group->id,
                        'user_id' => $memberId,
                        'role' => 'member'
                    ]);
                }
            }
            DB::commit();
            return response()->json(['success' => true]);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to add members'], 500);
        }
    }

    public function updateGroupSettings(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:messenger_groups,id',
            'allow_images' => 'required|boolean',
            'allow_files' => 'required|boolean'
        ]);

        $currentUser = Auth::user();
        $group = ChatGroup::findOrFail($request->group_id);

        // Check if current user is admin/creator
        $isAdmin = GroupMember::where('group_id', $group->id)
            ->where('user_id', $currentUser->id)
            ->where('role', 'admin')
            ->exists();

        if (!$isAdmin && $group->created_by != $currentUser->id) {
            return response()->json(['error' => 'Only admins can update settings'], 403);
        }

        $group->allow_images = $request->allow_images;
        $group->allow_files = $request->allow_files;
        $group->save();

        return response()->json(['success' => true]);
    }

    public function deleteMessage($id)
    {
        $currentUser = Auth::user();
        $message = Message::findOrFail($id);

        // Check if user is authorized to delete (must be sender)
        if ($message->from_id !== $currentUser->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            // Create Audit Log
            \App\Models\MessageAuditLog::create([
                'message_id' => $message->id,
                'action' => 'soft_delete',
                'performed_by' => $currentUser->id,
                'message_content_snapshot' => $message->body,
                'file_path_snapshot' => $message->file_path,
            ]);

            // Perform Soft Delete
            $message->deleted_by = $currentUser->id;
            $message->save(); // Save deleted_by before deleting
            $message->delete();

            DB::commit();
            return response()->json(['success' => true]);

        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete message'], 500);
        }
    }

    public function adminMessageAudit(Request $request)
    {
        $currentUser = Auth::user();

        // Only Super Admin and Company Owner can access
        if ($currentUser->type !== 'super admin' && $currentUser->type !== 'company') {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $query = \App\Models\MessageAuditLog::with(['performer', 'message' => function ($q) {
            $q->withTrashed();
        }]);

        // Filters
        if ($request->has('user_id') && $request->user_id) {
            $query->where('performed_by', $request->user_id);
        }

        if ($request->has('date_start') && $request->date_start) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }

        if ($request->has('date_end') && $request->date_end) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);
        $users = User::where('created_by', creatorId())->orWhere('id', creatorId())->pluck('name', 'id');

        return view('messenger.audit', compact('logs', 'users'));
    }

    public function adminForceDelete($id)
    {
        $currentUser = Auth::user();

        if ($currentUser->type !== 'super admin' && $currentUser->type !== 'company') {
            return response()->json(['error' => 'Permission Denied'], 403);
        }

        try {
            DB::beginTransaction();

            // Find the log entry to get the message_id
            $log = \App\Models\MessageAuditLog::findOrFail($id);

            // Log the force delete action
            \App\Models\MessageAuditLog::create([
                'message_id' => $log->message_id,
                'action' => 'force_delete',
                'performed_by' => $currentUser->id,
                'message_content_snapshot' => $log->message_content_snapshot,
                'file_path_snapshot' => $log->file_path_snapshot,
            ]);

            // Force delete the message if it exists
            if ($log->message_id) {
                $message = Message::withTrashed()->find($log->message_id);
                if ($message) {
                    // Start: Delete physical file if exists
                    if ($message->file_path) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($message->file_path);
                    }
                    // End: Delete physical file

                    $message->forceDelete();
                }
            }

            // Optionally delete the soft-delete log or keep it for history. 
            // Requirement says "check ke delete bhi ker sakte he" -> implies deleting the log entry or the message? 
            // Assuming deleting the Message permanently. The log should probably stay for audit.
            // But if the admin deletes from the audit view, maybe they want to clear the log?
            // Let's assume force deleting the message and keeping the log to show "Force Deleted".

            DB::commit();
            return redirect()->back()->with('success', 'Message permanently deleted.');

        }
        catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete message.');
        }
    }

    public function getSharedFiles($id, $type)
    {
        $media = [];
        $files = [];
        $authId = Auth::id();
        $query = Message::where('workspace_id', getActiveWorkSpace())
            ->whereNotNull('file_path');

        if ($type == 'group') {
            // Check membership
            $isMember = GroupMember::where('group_id', $id)
                ->where('user_id', $authId)
                ->exists();

            if (!$isMember) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $query->where('group_id', $id);
        }
        else {
            $query->where(function ($q) use ($authId, $id) {
                $q->where(function ($qq) use ($authId, $id) {
                        $qq->where('from_id', $authId)->where('to_id', $id);
                    }
                    )->orWhere(function ($qq) use ($authId, $id) {
                        $qq->where('from_id', $id)->where('to_id', $authId);
                    }
                    );
                });
        }

        $messages = $query->orderBy('created_at', 'desc')->get();
        // Get user bio if individual chat
        $bio = '';
        $members = [];
        $settings = null;

        if ($type == 'group') {
            $group = ChatGroup::find($id);
            if ($group) {
                $settings = [
                    'allow_images' => $group->allow_images,
                    'allow_files' => $group->allow_files,
                    'created_by' => $group->created_by,
                ];

                $groupMembers = GroupMember::where('group_id', $id)->with('user')->get();
                foreach ($groupMembers as $gm) {
                    if ($gm->user) {
                        $members[] = [
                            'id' => $gm->user->id,
                            'name' => $gm->user->name,
                            'avatar' => $gm->user->avatar ? get_file($gm->user->avatar) : null,
                            'role' => $gm->role,
                        ];
                    }
                }
            }
        }
        else {
            $sUser = User::find($id);
            if ($sUser) {
                $bio = $sUser->bio;
            }
        }

        foreach ($messages as $msg) {
            $fileData = [
                'name' => $msg->file_name ?? basename($msg->file_path),
                'size' => $msg->formatted_file_size,
                'type' => $msg->file_type,
                'time' => $msg->created_at->diffForHumans(),
                'url' => asset('storage/' . $msg->file_path),
                'icon' => $msg->file_icon
            ];

            $isImage = ($msg->file_type && str_starts_with($msg->file_type, 'image/')) ||
                in_array(strtolower(pathinfo($msg->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);

            if ($isImage) {
                $media[] = $fileData;
            }
            else {
                $files[] = $fileData;
            }
        }

        return response()->json([
            'media' => $media,
            'files' => $files,
            'bio' => $bio,
            'members' => $members,
            'settings' => $settings
        ]);
    }

    public function removeMember(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:messenger_groups,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $currentUser = Auth::user();
        $group = ChatGroup::findOrFail($request->group_id);

        // Check if current user is admin/creator
        $isAdmin = GroupMember::where('group_id', $group->id)
            ->where('user_id', $currentUser->id)
            ->where('role', 'admin')
            ->exists();

        if (!$isAdmin && $group->created_by != $currentUser->id) {
            return response()->json(['error' => 'Only admins can remove members'], 403);
        }

        // Cannot remove the creator
        if ($request->user_id == $group->created_by) {
            return response()->json(['error' => 'Cannot remove the group creator'], 422);
        }

        // Cannot remove yourself if you are the creator
        if ($request->user_id == $currentUser->id && $group->created_by == $currentUser->id) {
            return response()->json(['error' => 'Creator cannot leave the group from here'], 422);
        }

        $deleted = GroupMember::where('group_id', $group->id)
            ->where('user_id', $request->user_id)
            ->delete();

        if ($deleted) {
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Member not found in group'], 404);
    }
}
