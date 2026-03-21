@extends('layouts.main')

@section('page-title', __('Messenger'))

@section('page-breadcrumb', 'Messenger')

@push('scripts')
    <style>
        :root {
            --messenger-primary: #6366f1;
            --messenger-secondary: #f8fafc;
            --messenger-border: #e2e8f0;
            --messenger-text: #334155;
            --messenger-text-muted: #64748b;
            --messenger-bg: #ffffff;
            --messenger-hover: #f1f5f9;
        }

        .dark-theme {
            --messenger-secondary: #1e293b;
            --messenger-border: #334155;
            --messenger-text: #e2e8f0;
            --messenger-text-muted: #94a3b8;
            --messenger-bg: #0f172a;
            --messenger-hover: #1e293b;
        }

        .messenger-container {
            height: calc(100vh - 200px);
            background: var(--messenger-bg);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            position: relative;
            /* Ensure absolute children are contained */
        }

        .conversations-panel {
            width: 350px;
            border-right: 1px solid var(--messenger-border);
            background: var(--messenger-secondary);
            display: flex;
            flex-direction: column;
        }

        .chat-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--messenger-bg);
        }

        .conversations-header {
            padding: 20px;
            border-bottom: 1px solid var(--messenger-border);
            background: var(--messenger-bg);
        }

        .conversations-header h5 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            color: var(--messenger-text);
            letter-spacing: -0.025em;
        }

        .conversations-list {
            flex: 1;
            overflow-y: auto;
            padding: 0;
        }

        .chat-item {
            padding: 12px 20px;
            border-bottom: 1px solid var(--messenger-border);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
            border-left: 3px solid transparent;
        }

        .chat-item:hover {
            background: var(--messenger-hover);
        }

        .chat-item.active {
            background: #eef2ff;
            border-left-color: var(--messenger-primary);
        }

        .dark-theme .chat-item.active {
            background: #312e81;
        }

        .avatar-container {
            position: relative;
            flex-shrink: 0;
        }

        .chat-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
        }

        .status-dot {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid var(--messenger-bg);
        }

        .status-dot.online {
            background: #10b981;
        }

        .status-dot.offline {
            background: #94a3b8;
        }

        .chat-info {
            flex: 1;
            min-width: 0;
        }

        .chat-info h6 {
            margin: 0 0 4px 0;
            font-size: 14px;
            font-weight: 600;
            color: var(--messenger-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-info p {
            margin: 0;
            font-size: 13px;
            color: var(--messenger-text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .chat-time {
            font-size: 11px;
            color: var(--messenger-text-muted);
        }

        .unread-badge {
            background: var(--messenger-primary);
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: 600;
            min-width: 18px;
            text-align: center;
        }

        .chat-header {
            padding: 20px;
            border-bottom: 1px solid var(--messenger-border);
            background: var(--messenger-bg);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chat-header-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .chat-header-info {
            flex: 1;
        }

        .chat-header-info h6 {
            margin: 0 0 2px 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--messenger-text);
        }

        .chat-status {
            font-size: 12px;
            color: var(--messenger-text-muted);
            margin: 0;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .message {
            display: flex;
            gap: 8px;
            max-width: 70%;
        }

        .message.sent {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .message-content {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .message-header {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sender-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--messenger-text);
        }

        .message-time {
            font-size: 10px;
            color: var(--messenger-text-muted);
        }

        .message-body {
            background: var(--messenger-secondary);
            padding: 10px 16px;
            border-radius: 18px;
            border-top-left-radius: 4px;
            font-size: 14px;
            color: var(--messenger-text);
            line-height: 1.5;
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            position: relative;
            transition: all 0.2s ease;
        }

        .message.sent .message-body {
            background: linear-gradient(135deg, var(--messenger-primary), #4f46e5);
            color: white;
            border-radius: 18px;
            border-top-right-radius: 4px;
            border-top-left-radius: 18px;
        }

        .message:hover .message-actions {
            opacity: 1 !important;
            visibility: visible !important;
        }

        .message-actions {
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            display: flex !important;
            gap: 8px;
            padding: 0 8px;
            align-items: center;
        }

        .message-actions button {
            width: 28px;
            height: 28px;
            border-radius: 50% !important;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--messenger-secondary) !important;
            border: 1px solid var(--messenger-border) !important;
            color: var(--messenger-text-muted) !important;
            transition: all 0.2s ease;
            padding: 0 !important;
        }

        .message-actions button:hover {
            background: var(--messenger-hover) !important;
            color: var(--messenger-primary) !important;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .message-actions button.delete-btn:hover {
            color: #ef4444 !important;
        }

        .chat-input {
            padding: 20px;
            border-top: 1px solid var(--messenger-border);
            background: var(--messenger-bg);
        }

        .message-input-container {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--messenger-secondary);
            border: 1px solid var(--messenger-border);
            border-radius: 24px;
            padding: 4px;
        }

        .message-input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 12px 16px;
            font-size: 14px;
            color: var(--messenger-text);
            outline: none;
        }

        .message-input::placeholder {
            color: var(--messenger-text-muted);
        }

        .send-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--messenger-primary);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease;
        }

        .send-btn:hover {
            background: #5855eb;
        }

        .send-btn:disabled {
            background: var(--messenger-text-muted);
            cursor: not-allowed;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            color: var(--messenger-text-muted);
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state h5 {
            margin: 0 0 8px 0;
            font-size: 18px;
        }

        .empty-state p {
            margin: 0;
            font-size: 14px;
        }

        .loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
        }

        .spinner-border {
            width: 40px;
            height: 40px;
        }

        @media (max-width: 768px) {
            .messenger-container {
                flex-direction: column;
                height: calc(100vh - 150px);
            }

            .conversations-panel {
                width: 100%;
                height: 300px;
                border-right: none;
                border-bottom: 1px solid var(--messenger-border);
            }

            .chat-panel {
                height: calc(100vh - 450px);
            }
        }

        .user-profile-sidebar {
            position: absolute;
            top: 0;
            right: -320px;
            /* Fully outside */
            width: 320px;
            height: 100%;
            background: var(--messenger-bg);
            border-left: 1px solid var(--messenger-border);
            transition: right 0.3s ease;
            z-index: 100;
            display: flex;
            flex-direction: column;
            box-shadow: -4px 0 15px rgba(0, 0, 0, 0.05);
            /* Add shadow for better separation */
        }

        .user-profile-sidebar.active {
            right: 0;
        }

        .user-profile-sidebar .header {
            padding: 20px;
            border-bottom: 1px solid var(--messenger-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .user-profile-sidebar .body {
            padding: 20px;
            flex: 1;
            overflow-y: auto;
        }

        .user-profile-sidebar .shared-files-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .user-profile-sidebar .shared-file-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .user-profile-sidebar .shared-file-icon {
            margin-right: 10px;
            font-size: 20px;
            color: var(--messenger-primary);
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="messenger-container">
                        <!-- Conversations Panel -->
                        <div class="conversations-panel">
                            <div class="conversations-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="ti ti-messages me-2"></i>{{ __('Conversations') }}</h5>
                                @permission('messenger group create')
                                <button class="btn btn-sm btn-primary-light" data-bs-toggle="modal"
                                    data-bs-target="#createGroupModal" title="{{ __('Create Group Chat') }}">
                                    <i class="ti ti-plus"></i>
                                </button>
                                @endpermission
                            </div>
                            <div class="px-3 pb-3">
                                <div class="message-input-container">
                                    <input type="text" id="messenger-search" class="message-input"
                                        placeholder="{{ __('Search conversations...') }}"
                                        style="padding: 8px 12px; height: auto;">
                                    <i class="ti ti-search text-muted me-2"></i>
                                </div>
                            </div>
                            <div id="conversations-list" class="conversations-list">
                                <!-- Conversations will be loaded here -->
                            </div>
                        </div>

                        <!-- Chat Panel -->
                        <div class="chat-panel">
                            <!-- Chat Header -->
                            <div id="chat-header" class="chat-header" style="display: none; cursor: pointer;">
                                <img id="chat-avatar" src="" alt="" class="chat-header-avatar">
                                <div class="chat-header-info">
                                    <h6 id="chat-name"></h6>
                                    <p id="chat-status" class="chat-status">Active now</p>
                                </div>
                                <div class="ms-auto">
                                    <i class="ti ti-info-circle text-muted" style="font-size: 20px;"></i>
                                </div>
                            </div>

                            <!-- Chat Messages -->
                            <div id="chat-messages" class="chat-messages">
                                <div class="empty-state">
                                    <i class="ti ti-message-circle"></i>
                                    <h5>{{ __('Select a conversation') }}</h5>
                                    <p>{{ __('Choose a user to start chatting') }}</p>
                                </div>
                            </div>

                            <!-- Profile Sidebar -->
                            <div id="user-profile-sidebar" class="user-profile-sidebar">
                                <div class="header">
                                    <h6 class="mb-0">{{ __('Details') }}</h6>
                                    <button type="button" class="btn-close" id="close-sidebar"></button>
                                </div>
                                <div class="body">
                                    <div class="text-center mb-4">
                                        <img id="sidebar-avatar" src="" alt="" class="rounded-circle mb-2"
                                            style="width: 80px; height: 80px; object-fit: cover;">
                                        <h5 id="sidebar-name" class="mb-1"></h5>
                                        <p id="sidebar-email" class="text-muted small mb-0"></p>
                                        <div id="sidebar-bio" class="text-muted small mt-2 px-3"></div>
                                    </div>

                                    <div id="group-admin-options" style="display: none;" class="mb-4">
                                        <h6 class="mb-3">{{ __('Group Settings') }}</h6>
                                        <div class="d-flex gap-2 mb-3">
                                            <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal"
                                                data-bs-target="#addMemberModal">
                                                <i class="ti ti-user-plus me-1"></i> {{ __('Add Member') }}
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary w-100" data-bs-toggle="modal"
                                                data-bs-target="#groupSettingsModal">
                                                <i class="ti ti-settings me-1"></i> {{ __('Settings') }}
                                            </button>
                                        </div>
                                    </div>

                                    <h6 class="mb-3">{{ __('Group Members') }} (<span id="member-count">0</span>)</h6>
                                    <ul id="group-members-list" class="shared-files-list mb-4">
                                        <li class="text-center text-muted small py-3">{{ __('Loading...') }}</li>
                                    </ul>

                                    <h6 class="mb-3 mt-4">{{ __('Shared Media') }}</h6>
                                    <div id="shared-media-list" class="row g-2 mb-4 px-2">
                                        <!-- Media items will be loaded here -->
                                    </div>

                                    <h6 class="mb-3">{{ __('Shared Files') }}</h6>
                                    <ul id="shared-files-list" class="shared-files-list">
                                        <li class="text-center text-muted small py-3">{{ __('Loading...') }}</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Chat Input -->
                            <div id="message-input-container" class="chat-input" style="display: none;">
                                <div id="image-preview" class="image-preview"
                                    style="display: none; padding: 10px; background: #f0f2f5; border-top: 1px solid #e4e6eb;">
                                    <div style="position: relative; display: inline-block;">
                                        <img src="" alt="Preview"
                                            style="max-height: 100px; border-radius: 8px; border: 1px solid #ddd;">
                                        <button onclick="cancelImage()"
                                            style="position: absolute; top: -8px; right: -8px; background: #fff; border-radius: 50%; border: 1px solid #ddd; width: 20px; height: 20px; line-height: 18px; text-align: center; cursor: pointer; color: #666;">&times;</button>
                                    </div>
                                </div>
                                <div class="message-input-container">
                                    <button id="file-btn" class="file-btn"
                                        style="border: none; background: none; color: #6c757d; padding: 0 10px; cursor: pointer;">
                                        <i class="ti ti-paperclip"></i>
                                    </button>
                                    <input type="file" id="message-file" style="display: none;"
                                        accept="image/*,application/pdf,text/csv,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                                    <input type="text" id="message-input" class="message-input"
                                        placeholder="{{ __('Type a message...') }}">
                                    <button id="send-btn" class="send-btn" disabled>
                                        <i class="ti ti-send"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @permission('messenger group create')
    <!-- Group Creation Modal -->
    <div class="modal fade" id="createGroupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Create Group Chat') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createGroupForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __('Group Name') }}</label><x-required></x-required>
                                <input type="text" name="name" class="form-control"
                                    placeholder="{{ __('Enter Group Name') }}" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" class="form-control" rows="2"
                                    placeholder="{{ __('Enter Group Description') }}"></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __('Group Avatar') }}</label>
                                <input type="file" name="avatar" class="form-control" accept="image/*">
                            </div>
                            <div class="col-12 mt-2">
                                <h6>{{ __('Select Members') }}</h6>
                                <hr class="my-2">
                                <div id="groupMembersList"
                                    style="max-height: 250px; overflow-y: auto; padding: 10px; border: 1px solid #eee; border-radius: 8px;">
                                    <div class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                        <p class="small mt-2 mb-0">{{ __('Loading users...') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Create Group') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Member Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add Members to Group') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addMemberForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <h6>{{ __('Select New Members') }}</h6>
                                <hr class="my-2">
                                <div id="addMembersList"
                                    style="max-height: 250px; overflow-y: auto; padding: 10px; border: 1px solid #eee; border-radius: 8px;">
                                    <div class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                        <p class="small mt-2 mb-0">{{ __('Loading users...') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Add Members') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Group Settings Modal -->
    <div class="modal fade" id="groupSettingsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Group Settings') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="groupSettingsForm">
                    <div class="modal-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="allow_images" name="allow_images">
                            <label class="form-check-label" for="allow_images">{{ __('Allow Image Sharing') }}</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="allow_files" name="allow_files">
                            <label class="form-check-label" for="allow_files">{{ __('Allow File Sharing') }}</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endpermission

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script>
        // Define show_toastr function if not already defined
        if (typeof show_toastr === 'undefined') {
            window.show_toastr = function (title, message, type) {
                // Try to use toastr library if available
                if (typeof toastr !== 'undefined') {
                    toastr[type](message, title);
                    return;
                }

                // Fallback: Use Bootstrap toast or alert
                if (typeof bootstrap !== 'undefined') {
                    // Create a simple toast notification
                    var toastHtml = '<div class="toast align-items-center text-white bg-' + (type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info') + ' border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">' +
                        '<div class="d-flex">' +
                        '<div class="toast-body">' +
                        '<strong>' + title + ':</strong> ' + message +
                        '</div>' +
                        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                        '</div>' +
                        '</div>';

                    $('body').append(toastHtml);
                    var toastElement = $('body').children().last()[0];
                    var toast = new bootstrap.Toast(toastElement);
                    toast.show();

                    // Remove toast after it's hidden
                    $(toastElement).on('hidden.bs.toast', function () {
                        $(this).remove();
                    });
                } else {
                    // Final fallback: Use browser alert
                    alert(title + ': ' + message);
                }
            };
        }

        $(document).ready(function () {
            let currentChatId = null;
            let currentChatType = 'user';
            let currentUser = {{ Auth::user()->id }};
            let userType = '{{ strtolower(Auth::user()->type) }}';
            let messagePolling = null;
            let lastMessageTime = null; // Track last message timestamp for real-time updates
            let notificationPolling = null; // Simple toast notification polling
            let statusPolling = null; // Polling for user status updates
            let usersData = []; // Store users data globally



            // Simple notification checker using toast
            function startToastNotifications() {
                // Clear existing polling
                if (notificationPolling) {
                    clearInterval(notificationPolling);
                }

                // Start checking for new messages every 60 seconds
                notificationPolling = setInterval(function () {
                    checkForToastNotifications();
                }, 60000);

                // Start polling for status updates every 60 seconds
                if (statusPolling) clearInterval(statusPolling);
                statusPolling = setInterval(function () {
                    loadConversations(false); // Silent refresh
                }, 60000);
            }

            // Check for new unread messages and show toast
            function checkForToastNotifications() {
                // Only show notifications when not actively chatting
                if (!currentChatId) {
                    $.get('{{ route("messenger.latest.unread") }}', function (data) {
                        if (data.unread_messages && data.unread_messages.length > 0) {
                            // Show toast for the latest unread message
                            const latestMessage = data.unread_messages[0];
                            const lastNotifiedId = localStorage.getItem('last_notified_message_id');

                            // Check if we already notified this message
                            if (!lastNotifiedId || latestMessage.id > parseInt(lastNotifiedId)) {
                                show_toastr(
                                    'New Message from ' + latestMessage.from_name,
                                    latestMessage.body.length > 30 ? latestMessage.body.substring(0, 30) + '...' : latestMessage.body,
                                    'info'
                                );

                                // Update local storage
                                localStorage.setItem('last_notified_message_id', latestMessage.id);
                                console.log('🔔 Toast notification shown for message ID:', latestMessage.id);
                            } else {
                                console.log('🔕 Skipping duplicate notification for message ID:', latestMessage.id);
                            }
                        }
                    }).fail(function (xhr) {
                        console.log('Toast notification check error:', xhr.status);
                    });
                }
            }

            // Load users for group creation
            function loadUsersForGroup() {
                $('#groupMembersList').html(`
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <p class="small mt-2 mb-0">{{ __('Loading users...') }}</p>
                </div>
            `);

                $.get('{{ route("messenger.users") }}', function (response) {
                    const users = response.users || response;
                    let html = '';

                    if (users && users.length > 0) {
                        users.forEach(user => {
                            html += `
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="group_members[]" value="${user.id}" id="group_user_${user.id}">
                                <label class="form-check-label d-flex align-items-center" for="group_user_${user.id}">
                                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=6366f1&color=fff&size=24" class="rounded-circle me-2" style="width: 24px; height: 24px;">
                                    <span>${user.name}</span>
                                </label>
                            </div>
                        `;
                        });
                    } else {
                        html = '<p class="text-center text-muted my-3">No users found</p>';
                    }

                    $('#groupMembersList').html(html);
                });
            }

            // Load users when modal opens
            $('#createGroupModal').on('show.bs.modal', function () {
                loadUsersForGroup();
            });

            // Handle group creation form submission
            $('#createGroupForm').on('submit', function (e) {
                e.preventDefault();

                const form = this;
                const formData = new FormData(form);
                const members = [];
                $('input[name="group_members[]"]:checked').each(function () {
                    members.push($(this).val());
                });

                if (members.length === 0) {
                    show_toastr('Error', 'Please select at least one member', 'error');
                    return;
                }

                // Use the same array format as requested by the controller
                members.forEach(id => formData.append('members[]', id));

                const submitBtn = $(form).find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Creating...');

                $.ajax({
                    url: '{{ route("messenger.groups.create") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            show_toastr('Success', 'Group created successfully', 'success');
                            $('#createGroupModal').modal('hide');
                            form.reset();
                            loadConversations(); // Reload conversations to show new group
                        } else {
                            show_toastr('Error', response.error || 'Failed to create group', 'error');
                        }
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).text('Create Group');
                    },
                    error: function (xhr) {
                        const error = xhr.responseJSON ? xhr.responseJSON.error : 'Failed to create group';
                        show_toastr('Error', error, 'error');
                    }
                });
            });

            // Load conversations from server
            function loadConversations(showLoading = true) {
                // Show loading state
                if (showLoading) {
                    $('#conversations-list').html(`
                    <div class="loading-state">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ __('Loading...') }}</span>
                        </div>
                        <p class="mt-2">{{ __('Loading conversations...') }}</p>
                    </div>
                `);
                }

                // Use Promise.all to fetch both users and groups
                Promise.all([
                    $.get('{{ route("messenger.users") }}'),
                    $.get('{{ route("messenger.groups") }}')
                ]).then(([usersResponse, groupsResponse]) => {
                    let conversationsHtml = '';

                    const users = usersResponse.users || usersResponse;
                    const groups = groupsResponse.groups || groupsResponse;

                    usersData = users; // Store for global search reference

                    // Render Groups first
                    if (groups && groups.length > 0) {
                        groups.forEach(group => {
                            const avatarUrl = group.avatar
                                ? `{{ url('/') }}/storage/${group.avatar}`
                                : `https://ui-avatars.com/api/?name=${encodeURIComponent(group.name)}&background=10b981&color=fff&size=48`;

                            conversationsHtml += `
                            <div class="chat-item ${currentChatId == 'group_' + group.id ? 'active' : ''}" 
                                data-group-id="${group.id}" 
                                data-chat-type="group"
                                data-user-name="${group.name}" 
                                data-user-avatar="${avatarUrl}">
                                <div class="avatar-container">
                                    <img src="${avatarUrl}" class="chat-avatar" alt="${group.name}">
                                    <span class="status-dot online" title="Group Chat"></span>
                                </div>
                                <div class="chat-info">
                                    <h6 class="d-flex align-items-center">
                                        <i class="ti ti-users-group text-primary me-1" style="font-size: 14px;"></i>
                                        ${group.name}
                                    </h6>
                                    <p>${group.last_message || 'Start a group conversation...'}</p>
                                </div>
                                <div class="chat-meta">
                                    <span class="chat-time">${group.last_message_time || ''}</span>
                                    ${group.unread_count > 0 ? `<span class="unread-badge">${group.unread_count}</span>` : ''}
                                </div>
                            </div>
                        `;
                        });
                    }

                    // Render Users
                    if (users && users.length > 0) {
                        users.forEach(user => {
                            // Always use default avatar as primary choice to prevent 404 errors
                            const defaultAvatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=6366f1&color=fff&size=48`;

                            // For now, always use default avatar to avoid 404 issues
                            let avatarUrl = defaultAvatar;

                            // Format timestamp - show time ago or "Just now"
                            let timeDisplay = 'Just now';
                            if (user.last_message_datetime && user.last_message_datetime !== 'Just now') {
                                timeDisplay = user.last_message_datetime; // Already formatted as "2 hours ago" etc.
                            }

                            // Format online status with last seen
                            let statusText = user.status_text || 'Offline';
                            let statusClass = user.is_online ? 'online' : 'offline';
                            let statusTooltip = user.last_seen ? `Last seen: ${user.last_seen}` : statusText;

                            conversationsHtml += `
                            <div class="chat-item ${currentChatId == user.id ? 'active' : ''}" data-user-id="${user.id}" data-user-name="${user.name}" data-user-avatar="${avatarUrl}" data-user-type="${user.type}">
                                <div class="avatar-container">
                                    <img src="${avatarUrl}" class="chat-avatar" alt="${user.name}">
                                    <span class="status-dot ${statusClass}" title="${statusTooltip}"></span>
                                </div>
                                <div class="chat-info">
                                    <h6>${user.name}</h6>
                                    <p>${user.last_message || 'Start a conversation...'}</p>
                                </div>
                                <div class="chat-meta">
                                    <span class="chat-time" title="${user.last_message_datetime || ''}">${timeDisplay}</span>
                                    ${user.unread_count > 0 ? `<span class="unread-badge">${user.unread_count}</span>` : ''}
                                </div>
                            </div>
                        `;
                        });
                    }

                    // Show empty state ONLY if both are empty
                    if (!conversationsHtml) {
                        conversationsHtml = `
                        <div class="empty-state">
                            <i class="ti ti-users"></i>
                            <h5>{{ __('No conversations found') }}</h5>
                            <p>{{ __('Start a chat or create a group!') }}</p>
                            <button class="btn btn-primary btn-sm mt-3" onclick="location.reload()">
                                <i class="ti ti-refresh me-1"></i> {{ __('Refresh') }}
                            </button>
                        </div>
                    `;
                    }

                    // Capture scroll position
                    const scrollPos = $('#conversations-list').scrollTop();

                    $('#conversations-list').html(conversationsHtml);

                    // Restore scroll position
                    if (scrollPos > 0) {
                        $('#conversations-list').scrollTop(scrollPos);
                    }
                }).catch(error => {
                    console.error('Failed to load conversations:', error);

                    // Show error message
                    $('#conversations-list').html(`
                    <div class="empty-state">
                        <i class="ti ti-alert-triangle" style="color: #dc3545;"></i>
                        <h5>{{ __('Failed to load conversations') }}</h5>
                        <p>{{ __('A network error occurred. Please try again.') }}</p>
                        <button class="btn btn-primary btn-sm mt-3" onclick="loadConversations()">
                            <i class="ti ti-refresh me-1"></i> {{ __('Retry') }}
                        </button>
                    </div>
                `);
                });
            }

            // Real-time message polling function
            function startMessagePolling(id, type = 'user') {
                // Clear existing polling
                if (messagePolling) {
                    clearInterval(messagePolling);
                }

                // Start polling every 10 seconds to reduce server load
                messagePolling = setInterval(function () {
                    checkForNewMessages(id, type);
                }, 10000); // 10 seconds is manageable
            }

            // Check for new messages
            function checkForNewMessages(id, type = 'user') {
                if (!id) return;

                const url = type === 'group'
                    ? '{{ url("messenger/group-messages") }}/' + id
                    : '{{ url("messenger/messages") }}/' + id;

                $.get(url, function (messages) {
                    if (messages && messages.length > 0) {
                        // Check if there are new messages (compare with last message time)
                        let hasNewMessages = false;
                        let latestMessageTime = lastMessageTime;

                        messages.forEach(message => {
                            const messageTime = new Date(message.created_at).getTime();
                            if (latestMessageTime === null || messageTime > latestMessageTime) {
                                hasNewMessages = true;
                                latestMessageTime = Math.max(latestMessageTime || 0, messageTime);
                            }
                        });

                        if (hasNewMessages) {
                            console.log('New messages detected, updating chat...');
                            updateChatWithNewMessages(messages);
                            lastMessageTime = latestMessageTime;
                        }
                    }
                }).fail(function (xhr) {
                    console.log('Polling error:', xhr.status);
                    // Don't show error for polling failures to avoid spam
                });
            }

            // Helper function to create message HTML
            function createMessageHtml(message) {
                const isMe = message.from_id == currentUser;
                const fromUser = message.from_user || { name: 'Unknown', avatar: null };
                const senderName = fromUser.name;
                // Use provided avatar or generate one if missing
                const senderAvatar = fromUser.avatar ? fromUser.avatar :
                    `https://ui-avatars.com/api/?name=${encodeURIComponent(senderName)}&background=6366f1&color=fff&size=32`;

                // Format timestamp
                const timeAgo = moment(message.created_at).fromNow();
                const fullTime = moment(message.created_at).format('MMM D, YYYY h:mm A');
                const timeDisplay = message.is_optimistic ? message.created_at : timeAgo; // Use raw time for optimistic

                // Build reply preview
                let replyHtml = '';
                if (message.reply_to && message.reply_message) {
                    const replyTo = message.reply_message;
                    const replySender = replyTo.from_user ? replyTo.from_user.name : 'Unknown';
                    const replyText = replyTo.body ? (replyTo.body.length > 50 ? replyTo.body.substring(0, 50) + '...' : replyTo.body) : '';
                    replyHtml = `
                    <div class="message-reply" data-reply-id="${replyTo.id}" style="
                        background: rgba(99, 102, 241, 0.1);
                        border-left: 3px solid var(--messenger-primary);
                        padding: 8px 12px;
                        margin-bottom: 8px;
                        border-radius: 8px;
                        font-size: 12px;
                        cursor: pointer;
                    " onclick="scrollToMessage(${replyTo.id})">
                        <div style="font-weight: 600; color: var(--messenger-primary);">${replySender}</div>
                        <div style="color: var(--messenger-text-muted);">${replyText}</div>
                    </div>
                `;
                }

                // Handle file attachments
                let attachmentHtml = '';
                const filePath = message.file_path || message.attachment; // Support both names
                if (filePath) {
                    const fileName = message.file_name || filePath.split('/').pop();
                    const fileExtension = fileName.split('.').pop().toLowerCase();
                    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension) || (message.file_type && message.file_type.startsWith('image/'));

                    // Fix URL for XAMPP
                    let fileUrl = message.file_url;
                    if (!fileUrl) {
                        // Determine base URL
                        const baseUrl = window.location.origin + (window.location.pathname.startsWith('/crm') ? '/crm' : '');
                        fileUrl = `${baseUrl}/storage/${filePath}`;
                    }

                    if (isImage) {
                        attachmentHtml = `
                        <div class="message-image" style="margin-top: 5px;">
                            <a href="javascript:void(0)" onclick="viewImage('${fileUrl}')">
                                <img src="${fileUrl}" alt="Image" style="max-width: 100%; max-height: 250px; border-radius: 8px;">
                            </a>
                        </div>
                    `;
                    } else {
                        attachmentHtml = `
                        <div class="file-attachment" style="margin-top: 5px; background: rgba(0,0,0,0.05); padding: 8px; border-radius: 8px; display: flex; align-items: center; gap: 8px;">
                            <i class="ti ti-file" style="font-size: 20px;"></i>
                            <div style="flex: 1; min-width: 0;">
                                <a href="${fileUrl}" target="_blank" style="text-decoration: none; color: inherit; font-size: 13px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    ${fileName}
                                </a>
                            </div>
                        </div>
                    `;
                    }
                }

                // Optimistic file handling (base64)
                if (message.is_optimistic && message.file_data) {
                    if (message.file_type && message.file_type.startsWith('image/')) {
                        attachmentHtml = `
                        <div class="message-image" style="margin-top: 5px;">
                            <img src="${message.file_data}" alt="Image" style="max-width: 100%; max-height: 250px; border-radius: 8px; opacity: 0.7;">
                        </div>
                    `;
                    } else {
                        attachmentHtml = `
                         <div class="file-attachment" style="margin-top: 5px; background: rgba(0,0,0,0.05); padding: 8px; border-radius: 8px; display: flex; align-items: center; gap: 8px;">
                            <i class="ti ti-file" style="font-size: 20px;"></i>
                            <span style="font-size: 13px;">${message.file_name || 'Loading...'}</span>
                        </div>
                     `;
                    }
                }

                return `
                <div class="message ${isMe ? 'sent' : 'received'}" data-message-id="${message.id}" id="${message.temp_id || ''}">
                    ${!isMe ? `<img src="${senderAvatar}" class="message-avatar" alt="${senderName}">` : ''}
                    <div class="message-content">
                        <div class="message-header" style="justify-content: ${isMe ? 'flex-end' : 'flex-start'};">
                            ${!isMe ? `<span class="sender-name">${senderName}</span>` : ''}
                            <span class="message-time" title="${fullTime}">${timeDisplay}</span>
                        </div>
                        ${replyHtml}
                        <div class="d-flex align-items-center" style="gap: 8px; flex-direction: ${isMe ? 'row-reverse' : 'row'};">
                            <div class="message-body" style="${!message.body && attachmentHtml ? 'background: transparent; box-shadow: none; padding: 0;' : ''}">
                                ${message.body ? message.body.replace(/\n/g, '<br>') : ''}
                                ${attachmentHtml}
                            </div>
                            <div class="message-actions">
                                <button title="Reply" onclick="replyToMessage(${message.id}, '${senderName.replace(/'/g, "\\'")}', '${message.body ? message.body.replace(/'/g, "\\'").replace(/\n/g, " ") : "Attachment"}')">
                                    <i class="ti ti-corner-up-left"></i>
                                </button>
                                ${isMe ? `
                                <button title="Delete" class="delete-btn" onclick="deleteMessage(${message.id})">
                                    <i class="ti ti-trash"></i>
                                </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            }

            // Update chat with new messages
            function updateChatWithNewMessages(messages) {
                if (!messages || messages.length === 0) return;

                let messagesHtml = '';

                messages.forEach(message => {
                    messagesHtml += createMessageHtml(message);
                });

                // Append new messages and scroll to bottom
                $('#chat-messages').append(messagesHtml);
                $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);

                // Update conversation list to show latest message
                updateConversationPreview();

                // Update chat header with online status
                let statusHtml = '<span class="text-muted">Select a conversation</span>';
                if (currentChatId) {
                    const user = usersData.find(u => u.id == currentChatId);
                    if (user) {
                        const statusText = user.is_online ? 'Online' : (user.last_seen ? `Last seen ${user.last_seen}` : 'Offline');
                        const statusColor = user.is_online ? '#10b981' : '#94a3b8';
                        statusHtml = `<span style="color: ${statusColor}; font-size: 13px;"><i class="ti ti-circle" style="font-size: 8px; margin-right: 4px;"></i>${statusText}</span>`;

                        // Update conversation preview with latest message
                        $('#conversations-list .chat-item.active .chat-info p').text(user.last_message || 'Start a conversation...');
                        $('#conversations-list .chat-item.active .chat-time').text(user.last_message_datetime || 'Just now');
                    }
                }

                // Update chat header status
                $('.chat-header .chat-status').html(statusHtml);
            }

            // Update conversation preview with latest message
            function updateConversationPreview() {
                // Refresh conversation list to show updated last messages
                loadConversations();
            }

            // Load chat messages (modified to return promise for real-time integration)
            function loadChatMessages(id, type = 'user') {
                if (!id) return $.Deferred().resolve([]).promise();

                const url = type === 'group'
                    ? '{{ url("messenger/group-messages") }}/' + id
                    : '{{ url("messenger/messages") }}/' + id;

                return $.get(url, function (messages) {
                    let messagesHtml = '';

                    if (messages.length > 0) {
                        messages.forEach(message => {
                            messagesHtml += createMessageHtml(message);
                        });
                    } else {
                        messagesHtml = `
                        <div class="empty-state">
                            <i class="ti ti-message-circle"></i>
                            <h5>{{ __('No messages yet') }}</h5>
                            <p>{{ __('Start the conversation!') }}</p>
                        </div>
                    `;
                    }

                    $('#chat-messages').html(messagesHtml);

                    // Scroll to bottom
                    $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
                }).fail(function () {
                    $('#chat-messages').html(`
                    <div class="empty-state">
                        <i class="ti ti-message-circle"></i>
                        <h5>{{ __('Failed to load messages') }}</h5>
                        <p>{{ __('Try selecting the conversation again') }}</p>
                    </div>
                `);
                });
            }

            // Search functionality
            $('#messenger-search').on('keyup', function () {
                const value = $(this).val().toLowerCase();
                $('.conversations-list .chat-item').filter(function () {
                    const userName = $(this).data('user-name').toLowerCase();
                    $(this).toggle(userName.indexOf(value) > -1);
                });
            });

            // Reply functionality variables
            let replyingTo = null;
            let replyMessagePreview = null;
            let selectedFile = null;

            // Show reply options when clicking on a message
            window.showReplyOptions = function (messageId) {
                // Hide all other action menus
                $('.message-actions').hide();
                $(`#actions-${messageId}`).toggle();
            };

            // Reply to a specific message
            window.replyToMessage = function (messageId, senderName, messageBody) {
                replyingTo = messageId;

                // Show reply preview
                const previewText = messageBody.length > 30 ? messageBody.substring(0, 30) + '...' : messageBody;
                replyMessagePreview = `
                <div id="reply-preview" style="
                    background: rgba(99, 102, 241, 0.1);
                    border-left: 3px solid var(--messenger-primary);
                    padding: 8px 12px;
                    margin-bottom: 8px;
                    border-radius: 8px;
                    font-size: 12px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                ">
                    <div>
                        <div style="font-weight: 600; color: var(--messenger-primary);">Replying to ${senderName}</div>
                        <div style="color: var(--messenger-text-muted);">${previewText}</div>
                    </div>
                    <button onclick="cancelReply()" style="background: none; border: none; color: var(--messenger-text-muted); cursor: pointer;">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            `;

                // Insert reply preview before input
                $('#reply-preview').remove();
                $(replyMessagePreview).insertBefore('#message-input-container .message-input-container');

                // Focus input
                $('#message-input').focus();

                // Hide action menu
                $(`#actions-${messageId}`).hide();
            };

            // Cancel reply
            window.cancelReply = function () {
                replyingTo = null;
                replyMessagePreview = null;
                $('#reply-preview').remove();
            };

            // Delete a message
            window.deleteMessage = function (messageId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This message will be deleted permanently!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#6366f1',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    background: document.body.classList.contains('dark-theme') ? '#1e293b' : '#fff',
                    color: document.body.classList.contains('dark-theme') ? '#e2e8f0' : '#334155'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route("messenger.delete", ["id" => ":id"]) }}'.replace(':id', messageId),
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                if (response.success) {
                                    $(`[data-message-id="${messageId}"]`).fadeOut(function () {
                                        $(this).remove();
                                    });
                                    show_toastr('Success', 'Message deleted successfully', 'success');

                                    // Update conversation preview if needed
                                    loadConversations();
                                } else {
                                    show_toastr('Error', response.error || 'Failed to delete message', 'error');
                                }
                            },
                            error: function (xhr) {
                                show_toastr('Error', 'Failed to delete message', 'error');
                            }
                        });
                    }
                });
            };

            // View image in a beautiful popup
            window.viewImage = function (url) {
                Swal.fire({
                    imageUrl: url,
                    imageAlt: 'Message Image',
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: 'auto',
                    padding: '0',
                    background: 'transparent',
                    customClass: {
                        image: 'img-fluid rounded shadow-lg'
                    },
                    showClass: {
                        popup: 'animate__animated animate__zoomIn'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__zoomOut'
                    }
                });
            };

            // File selection handling
            $('#file-btn').click(function () {
                $('#message-file').click();
            });

            $('#message-file').change(function () {
                const file = this.files[0];
                if (file) {
                    selectedFile = file;
                    const reader = new FileReader();

                    reader.onload = function (e) {
                        // Show preview
                        $('#image-preview img').attr('src', e.target.result);
                        $('#image-preview').show();
                        $('#send-btn').prop('disabled', false); // Enable send button
                    };

                    if (file.type.startsWith('image/')) {
                        reader.readAsDataURL(file);
                    } else {
                        // Generic file preview
                        // You might want a generic icon here
                        $('#image-preview img').attr('src', 'https://via.placeholder.com/100?text=File'); // Placeholder
                        $('#image-preview').show();
                        $('#send-btn').prop('disabled', false);
                    }
                }
            });

            // Cancel image
            window.cancelImage = function () {
                selectedFile = null;
                $('#message-file').val('');
                $('#image-preview').hide();
                $('#image-preview img').attr('src', '');

                // Disable send if no text
                if (!$('#message-input').val().trim()) {
                    $('#send-btn').prop('disabled', true);
                }
            };

            // Scroll to original message when clicking reply preview
            window.scrollToMessage = function (messageId) {
                const messageElement = $(`[data-message-id="${messageId}"]`);
                if (messageElement.length) {
                    $('#chat-messages').animate({
                        scrollTop: messageElement.position().top + $('#chat-messages').scrollTop() - 100
                    }, 500);

                    // Highlight the message briefly
                    messageElement.css('background', 'rgba(99, 102, 241, 0.2)');
                    setTimeout(() => {
                        messageElement.css('background', '');
                    }, 2000);
                }
            };

            // Updated send message function with reply support and optimistic UI and file support
            function sendMessage() {
                const messageText = $('#message-input').val().trim();

                if ((!messageText && !selectedFile) || !currentChatId) return;

                // Optimistic UI: Create temp message object
                const tempId = 'temp-' + Date.now();
                const timeNow = moment().format('h:mm A');
                let replyHtml = '';

                if (replyingTo && replyMessagePreview) {
                    const replySender = $(replyMessagePreview).find('div[style*="font-weight: 600"]').text().replace('Replying to ', '');
                    const replyText = $(replyMessagePreview).find('div[style*="color: var(--messenger-text-muted)"]').text();
                    replyHtml = `
                    <div class="message-reply" style="
                        background: rgba(99, 102, 241, 0.1);
                        border-left: 3px solid var(--messenger-primary);
                        padding: 8px 12px;
                        margin-bottom: 8px;
                        border-radius: 8px;
                        font-size: 12px;
                    ">
                        <div style="font-weight: 600; color: var(--messenger-primary);">${replySender}</div>
                        <div style="color: var(--messenger-text-muted);">${replyText}</div>
                    </div>
                `;
                }

                // Optimistic file data for preview
                let fileData = null;
                if (selectedFile && $('#image-preview img').attr('src')) {
                    fileData = $('#image-preview img').attr('src');
                }

                const tempMessage = {
                    id: Date.now(), // Temporary ID for interaction
                    temp_id: tempId,
                    from_id: currentUser,
                    body: messageText,
                    created_at: timeNow,
                    is_optimistic: true,
                    reply_html: replyHtml,
                    file_data: fileData,
                    file_name: selectedFile ? selectedFile.name : null,
                    file_type: selectedFile ? selectedFile.type : null,
                    // Mock user object for display if needed
                    from_user: {
                        name: 'You',
                        avatar: null
                    }
                };

                const messageHtml = createMessageHtml(tempMessage);

                $('#chat-messages').append(messageHtml);
                $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);

                // Clear input and reply preview immediately
                $('#message-input').val('');
                const replyToId = replyingTo; // Store for request

                const formData = new FormData();
                formData.append('to_id', currentChatId);

                if (currentChatType === 'group') {
                    if (messageText) formData.append('body', messageText);
                    if (selectedFile) formData.append('attachment', selectedFile);
                } else {
                    if (messageText) formData.append('message', messageText);
                    if (selectedFile) formData.append('file', selectedFile);
                    if (replyToId) formData.append('reply_to', replyToId);
                }

                // Reset UI states
                cancelReply();
                cancelImage();

                const sendUrl = currentChatType === 'group'
                    ? '{{ route("messenger.group.send") }}'
                    : '{{ route("messenger.send") }}';

                $.ajax({
                    url: sendUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            // Remove temp message and load actual messages to ensure sync and correct ID
                            $(`#${tempId}`).remove();
                            // Load conversation to update last message
                            loadConversations();
                            // Optionally refresh messages to get the real one with server logic
                            loadChatMessages(currentChatId, currentChatType);
                        } else {
                            $(`#${tempId}`).remove();
                            show_toastr('Error', 'Failed to send message', 'error');
                            $('#message-input').val(messageText); // Restore message
                        }
                    },
                    error: function () {
                        $(`#${tempId}`).remove();
                        show_toastr('Error', 'Failed to send message', 'error');
                        $('#message-input').val(messageText); // Restore message
                    }
                });
            }

            // Message input handlers
            $('#message-input').on('input', function () {
                const message = $(this).val().trim();
                $('#send-btn').prop('disabled', !message && !selectedFile);
            });

            $('#message-input').on('keypress', function (e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            $('#send-btn').on('click', function () {
                sendMessage();
            });

            // Handle chat item click
            $(document).on('click', '.chat-item', function () {
                // Remove active class from all items
                $('.chat-item').removeClass('active');
                // Add active class to clicked item
                $(this).addClass('active');

                const type = $(this).data('chat-type') || 'user';
                const id = type === 'group' ? $(this).data('group-id') : $(this).data('user-id');
                const name = $(this).data('user-name');
                const avatar = $(this).data('user-avatar');

                currentChatId = id;
                currentChatType = type;

                // Update chat header
                $('#chat-name').text(name);
                $('#chat-avatar').attr('src', avatar);

                // Update header icon based on type
                if (type === 'group') {
                    $('#chat-status').html('<span class="status-dot online"></span> Group Chat');
                } else {
                    const statusClass = $(this).find('.status-dot').hasClass('online') ? 'online' : 'offline';
                    const statusText = statusClass === 'online' ? 'Online' : 'Offline';
                    $('#chat-status').html(`<span class="status-dot ${statusClass}"></span> ${statusText}`);
                }

                // Show chat area elements
                $('#chat-header').show();
                $('#message-input-container').show();
                $('.empty-state').hide(); // Hide empty state in messages area

                // Load messages
                loadChatMessages(id, type);

                // Start polling for this chat
                startMessagePolling(id, type);

                // Close sidebar on new chat selection
                $('#user-profile-sidebar').removeClass('active');
            });

            // Toggle Profile Sidebar
            $('#chat-header').on('click', function () {
                const sidebar = $('#user-profile-sidebar');
                sidebar.toggleClass('active');

                if (sidebar.hasClass('active')) {
                    // Populate sidebar info
                    $('#sidebar-name').text($('#chat-name').text());
                    $('#sidebar-avatar').attr('src', $('#chat-avatar').attr('src'));

                    // Fetch shared files
                    loadSharedFiles(currentChatId, currentChatType);
                }
            });

            $('#close-sidebar').on('click', function () {
                $('#user-profile-sidebar').removeClass('active');
            });

            function loadSharedFiles(id, type) {
                $('#shared-media-list').html('<div class="col-12 text-center text-muted small py-3">{{ __("Loading...") }}</div>');
                $('#shared-files-list').html('<li class="text-center text-muted small py-3">{{ __("Loading...") }}</li>');
                $('#group-members-list').html('<li class="text-center text-muted small py-3">{{ __("Loading...") }}</li>');
                $('#sidebar-bio').text('');
                $('#group-admin-options').hide();

                $.get('{{ url("messenger/shared-files") }}/' + id + '/' + type, function (data) {
                    // Bio
                    if (data.bio) {
                        $('#sidebar-bio').text(data.bio);
                    }

                    // Group Members & Settings
                    if (type === 'group' && data.members) {
                        $('#member-count').text(data.members.length);

                        // Show admin options if current user is admin/creator or high-level user
                        const currentUserMember = data.members.find(m => m.id == currentUser);
                        const userRole = (currentUserMember ? currentUserMember.role : '').toLowerCase();
                        const isCreator = data.settings && data.settings.created_by == currentUser;
                        const isAdmin = userRole === 'admin' || isCreator || userType === 'super admin' || userType === 'company';

                        if (isAdmin) {
                            $('#group-admin-options').show();

                            // Pre-fill settings modal
                            if (data.settings) {
                                $('#allow_images').prop('checked', !!data.settings.allow_images);
                                $('#allow_files').prop('checked', !!data.settings.allow_files);
                            }
                        }

                        let membersHtml = '';
                        data.members.forEach(member => {
                            const avatar = member.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(member.name)}&background=6366f1&color=fff&size=32`;
                            const role = (member.role || '').toLowerCase();
                            const isMemberCreator = data.settings && data.settings.created_by == member.id;

                            // Show delete button if current user is admin/creator AND 
                            // the member is NOT the current user AND 
                            // the member is NOT the creator
                            let deleteBtn = '';
                            if (isAdmin && member.id != currentUser && !isMemberCreator) {
                                deleteBtn = `
                                <button class="btn btn-sm btn-icon btn-outline-danger" onclick="removeGroupMember(${id}, ${member.id}, '${member.name.replace(/'/g, "\\'")}')" title="Remove Member">
                                    <i class="ti ti-trash" style="font-size: 12px;"></i>
                                </button>
                            `;
                            }

                            membersHtml += `
                            <li class="shared-file-item d-flex align-items-center mb-2">
                                <img src="${avatar}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <div class="small fw-bold">${member.name}</div>
                                    <div class="text-muted" style="font-size: 10px; text-transform: capitalize;">${isMemberCreator ? 'Creator' : role}</div>
                                </div>
                                ${deleteBtn}
                            </li>
                        `;
                        });
                        $('#group-members-list').html(membersHtml);

                        // Apply settings to UI
                        if (data.settings) {
                            if (!data.settings.allow_images && !data.settings.allow_files) {
                                $('#file-btn').hide();
                            } else {
                                $('#file-btn').show();
                            }
                        }
                    } else {
                        $('#group-members-list').html('<li class="text-center text-muted small py-3">{{ __("Not a group") }}</li>');
                        $('#file-btn').show(); // Always show for individual chats
                    }

                    // Media
                    let mediaHtml = '';
                    if (data.media && data.media.length > 0) {
                        data.media.forEach(item => {
                            mediaHtml += `
                            <div class="col-4">
                                <a href="javascript:void(0)" onclick="viewImage('${item.url}')">
                                    <img src="${item.url}" class="img-fluid rounded" style="height: 60px; width: 100%; object-fit: cover;" title="${item.name}">
                                </a>
                            </div>
                        `;
                        });
                    } else {
                        mediaHtml = '<div class="col-12 text-center text-muted small py-3">{{ __("No media shared yet") }}</div>';
                    }
                    $('#shared-media-list').html(mediaHtml);

                    // Files
                    let filesHtml = '';
                    if (data.files && data.files.length > 0) {
                        data.files.forEach(item => {
                            filesHtml += `
                            <li class="shared-file-item d-flex align-items-center mb-2">
                                <i class="${item.icon} shared-file-icon me-2"></i>
                                <div class="flex-grow-1 overflow-hidden">
                                    <a href="${item.url}" target="_blank" class="text-decoration-none text-dark small d-block text-truncate">${item.name}</a>
                                    <div class="text-muted" style="font-size: 10px;">${item.size} • ${item.time}</div>
                                </div>
                            </li>
                        `;
                        });
                    } else {
                        filesHtml = '<li class="text-center text-muted small py-3">{{ __("No files shared yet") }}</li>';
                    }
                    $('#shared-files-list').html(filesHtml);
                });
            }

            // Load users for Add Member modal
            $('#addMemberModal').on('show.bs.modal', function () {
                $('#addMembersList').html(`
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <p class="small mt-2 mb-0">{{ __('Loading users...') }}</p>
                </div>
            `);

                $.get('{{ route("messenger.users") }}', function (response) {
                    const users = response.users || response;
                    let html = '';

                    if (users && users.length > 0) {
                        users.forEach(user => {
                            // Don't show current user
                            if (user.id == currentUser) return;

                            html += `
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="new_members[]" value="${user.id}" id="add_user_${user.id}">
                                <label class="form-check-label d-flex align-items-center" for="add_user_${user.id}">
                                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=6366f1&color=fff&size=24" class="rounded-circle me-2" style="width: 24px; height: 24px;">
                                    <span>${user.name}</span>
                                </label>
                            </div>
                        `;
                        });
                    } else {
                        html = '<p class="text-center text-muted my-3">No users found</p>';
                    }

                    $('#addMembersList').html(html);
                });
            });

            // Handle Add Members form submission
            $('#addMemberForm').on('submit', function (e) {
                e.preventDefault();
                const members = [];
                $('input[name="new_members[]"]:checked').each(function () {
                    members.push($(this).val());
                });

                if (members.length === 0) {
                    show_toastr('Error', 'Please select at least one member', 'error');
                    return;
                }

                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Adding...');

                $.ajax({
                    url: '{{ route("messenger.groups.add_members") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        group_id: currentChatId,
                        members: members
                    },
                    success: function (response) {
                        if (response.success) {
                            show_toastr('Success', 'Members added successfully', 'success');
                            $('#addMemberModal').modal('hide');
                            loadSharedFiles(currentChatId, currentChatType); // Refresh members list
                        } else {
                            show_toastr('Error', response.error || 'Failed to add members', 'error');
                        }
                    },
                    error: function (xhr) {
                        show_toastr('Error', xhr.responseJSON ? xhr.responseJSON.error : 'Failed to add members', 'error');
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).text('Add Members');
                    }
                });
            });

            // Remove member from group
            window.removeGroupMember = function (groupId, userId, userName) {
                if (!confirm(`Are you sure you want to remove ${userName} from this group?`)) {
                    return;
                }

                $.ajax({
                    url: '{{ route("messenger.groups.remove_member") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        group_id: groupId,
                        user_id: userId
                    },
                    success: function (response) {
                        if (response.success) {
                            show_toastr('Success', 'Member removed successfully', 'success');
                            loadSharedFiles(groupId, 'group'); // Refresh members list
                        } else {
                            show_toastr('Error', response.error || 'Failed to remove member', 'error');
                        }
                    },
                    error: function (xhr) {
                        show_toastr('Error', xhr.responseJSON ? xhr.responseJSON.error : 'Failed to remove member', 'error');
                    }
                });
            }

            // Handle Group Settings form submission
            $('#groupSettingsForm').on('submit', function (e) {
                e.preventDefault();
                const allowImages = $('#allow_images').is(':checked') ? 1 : 0;
                const allowFiles = $('#allow_files').is(':checked') ? 1 : 0;

                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                $.ajax({
                    url: '{{ route("messenger.groups.update_settings") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        group_id: currentChatId,
                        allow_images: allowImages,
                        allow_files: allowFiles
                    },
                    success: function (response) {
                        if (response.success) {
                            show_toastr('Success', 'Settings updated successfully', 'success');
                            $('#groupSettingsModal').modal('hide');
                            loadSharedFiles(currentChatId, currentChatType); // Refresh to get updated settings
                        } else {
                            show_toastr('Error', response.error || 'Failed to update settings', 'error');
                        }
                    },
                    error: function (xhr) {
                        show_toastr('Error', xhr.responseJSON ? xhr.responseJSON.error : 'Failed to update settings', 'error');
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).text('Save Changes');
                    }
                });
            });

            // Initialize
            loadConversations();

            // Start toast notifications for real-time alerts
            startToastNotifications();

            // Remove old 30-second polling - we now use real-time polling when conversations are active
            // The real-time polling starts when a conversation is selected and stops when unselected


        });
    </script>
@endpush