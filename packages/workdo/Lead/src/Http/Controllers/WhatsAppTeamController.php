<?php

namespace Workdo\Lead\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Workdo\Lead\Entities\WhatsAppTeam;
use Workdo\Lead\Entities\WhatsAppTeamMember;
use Workdo\Lead\Entities\WhatsAppConfig;

/**
 * WhatsAppTeamController
 *
 * Manages WhatsApp Teams:
 * - CRUD for teams
 * - Assign/remove members (with head/member role)
 * - Assign a WhatsApp number (config) to a team
 */
class WhatsAppTeamController extends Controller
{
    private function isCompanyOrAdmin(): bool
    {
        $user = Auth::user();
        return in_array($user->type, ['company', 'super admin']);
    }

    // ── List all teams ───────────────────────────────────────────────────────
    public function index()
    {
        $workspaceId = getActiveWorkSpace();
        $teams = WhatsAppTeam::where('workspace_id', $workspaceId)
            ->with(['members.user', 'config'])
            ->get();

        $configs = WhatsAppConfig::where('workspace_id', $workspaceId)->get();

        // Get all workspace users for member assignment
        $users = \App\Models\User::where('workspace_id', $workspaceId)
            ->orWhereHas('workspaces', fn($q) => $q->where('workspace_id', $workspaceId))
            ->where('type', '!=', 'company')
            ->get(['id', 'name', 'email']);

        return view('lead::whatsapp.teams', compact('teams', 'configs', 'users'));
    }

    // ── Create team ──────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        if (!$this->isCompanyOrAdmin()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $team = WhatsAppTeam::create([
            'name'                => $request->name,
            'description'         => $request->description,
            'whatsapp_config_id'  => null,
            'workspace_id'        => getActiveWorkSpace(),
            'created_by'          => Auth::id(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => __('Team created successfully.'),
            'team'    => $team,
        ]);
    }

    // ── Update team name/description ─────────────────────────────────────────
    public function update(Request $request, $id)
    {
        if (!$this->isCompanyOrAdmin()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $team = WhatsAppTeam::where('workspace_id', getActiveWorkSpace())->findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $team->update($request->only('name', 'description'));

        return response()->json([
            'status'  => 'success',
            'message' => __('Team updated successfully.'),
        ]);
    }

    // ── Delete team ──────────────────────────────────────────────────────────
    public function destroy($id)
    {
        if (!$this->isCompanyOrAdmin()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $team = WhatsAppTeam::where('workspace_id', getActiveWorkSpace())->findOrFail($id);
        $team->delete(); // Cascades to whatsapp_team_members

        return response()->json([
            'status'  => 'success',
            'message' => __('Team deleted successfully.'),
        ]);
    }

    // ── Assign WhatsApp number (config) to a team ────────────────────────────
    public function assignConfig(Request $request, $id)
    {
        if (!$this->isCompanyOrAdmin()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $team = WhatsAppTeam::where('workspace_id', getActiveWorkSpace())->findOrFail($id);

        $request->validate([
            'whatsapp_config_id' => 'nullable|integer',
        ]);

        $configId = $request->input('whatsapp_config_id');

        // Validate config belongs to workspace
        if ($configId) {
            $config = WhatsAppConfig::where('workspace_id', getActiveWorkSpace())->find($configId);
            if (!$config) {
                return response()->json(['error' => __('Invalid WhatsApp configuration.')], 422);
            }
        }

        $team->update(['whatsapp_config_id' => $configId ?: null]);

        return response()->json([
            'status'  => 'success',
            'message' => $configId
                ? __('WhatsApp number assigned to team.')
                : __('WhatsApp number unassigned from team.'),
        ]);
    }

    // ── Add member to team ───────────────────────────────────────────────────
    public function addMember(Request $request, $id)
    {
        if (!$this->isCompanyOrAdmin()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $team = WhatsAppTeam::where('workspace_id', getActiveWorkSpace())->findOrFail($id);

        $request->validate([
            'user_id' => 'required|integer',
            'role'    => 'required|in:head,member',
        ]);

        // Check if already in team
        $existing = WhatsAppTeamMember::where('team_id', $id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existing) {
            // Update role if different
            $existing->update(['role' => $request->role]);
            return response()->json([
                'status'  => 'success',
                'message' => __('Member role updated.'),
            ]);
        }

        $member = WhatsAppTeamMember::create([
            'team_id' => $id,
            'user_id' => $request->user_id,
            'role'    => $request->role,
        ]);

        $user = \App\Models\User::find($request->user_id);

        return response()->json([
            'status'  => 'success',
            'message' => __('Member added to team.'),
            'member'  => [
                'id'        => $member->id,
                'user_id'   => $member->user_id,
                'user_name' => $user?->name,
                'role'      => $member->role,
            ],
        ]);
    }

    // ── Remove member from team ──────────────────────────────────────────────
    public function removeMember(Request $request, $id, $userId)
    {
        if (!$this->isCompanyOrAdmin()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        WhatsAppTeam::where('workspace_id', getActiveWorkSpace())->findOrFail($id);

        WhatsAppTeamMember::where('team_id', $id)
            ->where('user_id', $userId)
            ->delete();

        return response()->json([
            'status'  => 'success',
            'message' => __('Member removed from team.'),
        ]);
    }

    // ── Get team details (for editing) ───────────────────────────────────────
    public function show($id)
    {
        $team = WhatsAppTeam::where('workspace_id', getActiveWorkSpace())
            ->with(['members.user', 'config'])
            ->findOrFail($id);

        return response()->json(['status' => 'success', 'team' => $team]);
    }
}
