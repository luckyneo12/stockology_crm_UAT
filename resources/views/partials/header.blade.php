<header
    class="dash-header {{ empty($company_settings['site_transparent']) || $company_settings['site_transparent'] == 'on' ? 'transprent-bg' : '' }} ">
    <div class="header-wrapper">
        <div class="me-auto dash-mob-drp">
            <ul class="list-unstyled">
                <li class="dash-h-item mob-hamburger">
                    <a href="#!" class="dash-head-link" id="mobile-collapse">
                        <div class="hamburger hamburger--arrowturn">
                            <div class="hamburger-box">
                                <div class="hamburger-inner"></div>
                            </div>
                        </div>
                    </a>
                </li>

                <li class="dropdown dash-h-item drp-company">
                    <a class="dash-head-link dropdown-toggle arrow-none m-0" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false"aria-expanded="false">
                        @if (!empty(Auth::user()->avatar))
                            <span class="theme-avtar">
                                <img alt="#"
                                    src="{{ check_file(Auth::user()->avatar) ? get_file(Auth::user()->avatar) : '' }}"
                                    class="rounded border-2  border-primary" style="width: 100% ; height: 100%">
                            </span>
                        @else
                            <span class="theme-avtar">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        @endif
                        <span class="hide-mob ms-2">{{ Auth::user()->name }}</span>
                        <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        @permission('user profile manage')
                            <a href="{{ route('profile') }}" class="dropdown-item">
                                <i class="ti ti-user"></i>
                                <span>{{ __('Profile') }}</span>
                            </a>
                        @endpermission
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('frm-logout').submit();"
                            class="dropdown-item">
                            <i class="ti ti-power"></i>
                            <span>{{ __('Logout') }}</span>
                        </a>
                        <form id="frm-logout" action="{{ route('logout') }}" method="POST" class="d-none">
                            {{ csrf_field() }}
                        </form>
                    </div>
                </li>

            </ul>
        </div>
        <div class="ms-auto">
            <ul class="list-unstyled">
                @impersonating($guard = null)
                    <li class="dropdown dash-h-item drp-company">
                        <a class="btn btn-danger btn-sm me-3" href="{{ route('exit.company') }}"><i class="ti ti-ban"></i>
                            {{ __('Exit Company Login') }}
                        </a>
                    </li>
                @endImpersonating
                @permission('user chat manage')
                    @php
                        $unseenCounter = App\Models\Message::where('to_id', Auth::user()->id)
                            ->where('is_seen', 0)
                            ->count();
                    @endphp
                    <li class="dash-h-item">
                        <a class="dash-head-link me-0" href="{{ route('messenger.index') }}">
                            <i class="ti ti-message-circle"></i>
                            <span
                                class="bg-danger dash-h-badge message-counter custom_messanger_counter">{{ $unseenCounter }}</span>
                        </a>
                    </li>
                @endpermission
                <li class="dropdown dash-h-item drp-notification">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="notification-bell">
                        <i class="ti ti-bell"></i>
                        @php
                            $notificationCount = App\Models\UserNotification::where('user_id', Auth::user()->id)
                                ->where('is_read', 0)
                                ->count();
                        @endphp
                        <span class="bg-danger dash-h-badge notification-counter">{{ $notificationCount }}</span>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown dropdown-menu-end" id="notification-dropdown">
                        <div class="noti-header">
                            <h5 class="m-0">{{ __('Notifications') }}</h5>
                            <a href="#!" id="mark-all-read" class="ms-2 text-primary">{{ __('Mark all as read') }}</a>
                        </div>
                        <div class="noti-body custom-scrollbar" style="max-height: 400px; overflow-y: auto;">
                            <!-- Loaded via AJAX -->
                        </div>
                    </div>
                </li>
                @permission('workspace create')
                    @if (PlanCheck('Workspace', Auth::user()->id) == true)
                        <li class="dash-h-item">
                            <a href="#!" class="dash-head-link dropdown-toggle arrow-none me-0 cust-btn"
                                data-url="{{ route('workspace.create') }}" data-ajax-popup="true" data-size="lg"
                                data-title="{{ __('Create New Workspace') }}">
                                <i class="ti ti-circle-plus"></i>
                                <span class="hide-mob">{{ __('Create Workspace') }}</span>
                            </a>
                        </li>
                    @endif
                @endpermission
                @permission('workspace manage')
                    <li class="dropdown dash-h-item drp-language">
                        <a class="dash-head-link dropdown-toggle arrow-none me-0 cust-btn" data-bs-toggle="dropdown"
                            href="#" role="button" aria-haspopup="false" aria-expanded="false"
                            data-bs-placement="bottom" data-bs-original-title="Select your bussiness">
                            <i class="ti ti-apps"></i>
                            <span class="hide-mob">{{ Auth::user()->ActiveWorkspaceName() }}</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                        </a>
                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end" style="">
                            @foreach (getWorkspace() as $workspace)
                                @if ($workspace->id == getActiveWorkSpace())
                                    <div class="d-flex justify-content-between bd-highlight">
                                        <a href=" # " class="dropdown-item ">
                                            <i class="ti ti-checks text-primary"></i>
                                            <span>{{ $workspace->name }}</span>
                                            @if ($workspace->created_by == Auth::user()->id)
                                                <span class="badge bg-dark">
                                                    {{ Auth::user()->roles->first()->name }}</span>
                                            @else
                                                <span class="badge bg-dark"> {{ __('Shared') }}</span>
                                            @endif
                                        </a>
                                        @if ($workspace->created_by == Auth::user()->id)
                                            @permission('workspace edit')
                                                <div class="action-btn mt-2">
                                                    <a data-url="{{ route('workspace.edit', $workspace->id) }}"
                                                        class="mx-3 btn" data-ajax-popup="true"
                                                        data-title="{{ __('Edit Workspace Name') }}" data-toggle="tooltip"
                                                        data-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-success"></i>
                                                    </a>
                                                </div>
                                            @endpermission
                                        @endif
                                    </div>
                                @else
                                @php
                                    $route = ($workspace->is_disable == 1) ?  route('workspace.change', $workspace->id) : '#';
                                @endphp
                                    <div class="d-flex justify-content-between bd-highlight">

                                    <a href="{{ $route }}" class="dropdown-item">
                                        <span>{{ $workspace->name }}</span>
                                        @if ($workspace->created_by == Auth::user()->id)
                                            <span class="badge bg-dark"> {{ Auth::user()->roles->first()->name }}</span>
                                        @else
                                            <span class="badge bg-dark"> {{ __('Shared') }}</span>
                                        @endif
                                    </a>
                                    @if ($workspace->is_disable == 0)
                                            <div class="action-btn mt-2">
                                                <i class="ti ti-lock"></i>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                            @if (getWorkspace()->count() > 1)
                                @permission('workspace delete')
                                    <hr class="dropdown-divider" />
                                        <a href="#!" data-url="{{route('company.info', Auth::user()->id)}}" class="dropdown-item" data-ajax-popup="true" data-size="lg" data-title="{{__('Workspace Info')}}">
                                            <i class="ti ti-circle-x"></i>
                                            <span>{{ __('View') }}</span> <br>
                                        </a>


                                    <hr class="dropdown-divider" />

                                    <form id="remove-workspace-form"
                                        action="{{ route('workspace.destroy', getActiveWorkSpace()) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <a href="#!" class="dropdown-item remove_workspace">
                                            <i class="ti ti-circle-x"></i>
                                            <span>{{ __('Remove') }}</span> <br>
                                            <small class="text-danger">{{ __('Active Workspace Will Consider') }}</small>
                                        </a>
                                    </form>
                                @endpermission
                            @endif
                        </div>
                    </li>
                @endpermission

                <li class="dropdown dash-h-item drp-language">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="ti ti-world nocolor"></i>
                        <span class="drp-text hide-mob">{{ Str::upper(getActiveLanguage()) }}</span>
                        <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">

                        @foreach (languages() as $key => $language)
                            <a href="{{ route('lang.change', $key) }}"
                                class="dropdown-item @if ($key == getActiveLanguage()) text-danger @endif">
                                <span>{{ Str::ucfirst($language) }}</span>
                            </a>
                        @endforeach
                        @if (Auth::user()->type == 'super admin')
                            @permission('language create')
                                <a href="#" data-url="{{ route('create.language') }}"
                                    class="dropdown-item border-top pt-3 text-primary" data-ajax-popup="true"
                                    data-title="{{ __('Create New Language') }}">
                                    <span>{{ __('Create Language') }}</span>
                                </a>
                            @endpermission
                            @permission('language manage')
                                <a href="{{ route('lang.index', [Auth::user()->lang]) }}"
                                    class="dropdown-item  pt-3 text-primary">
                                    <span>{{ __('Manage Languages') }}</span>
                                </a>
                            @endpermission
                        @endif
                    </div>
                </li>
            </ul>
        </div>
    </div>
<style>
.noti-header {
    padding: 15px 20px;
    border-bottom: 1px solid #f1f1f1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.notification-item {
    padding: 15px 20px;
    border-bottom: 1px solid #f8f9fa;
    transition: background 0.2s;
    cursor: pointer;
}
.notification-item:hover {
    background: #f8f9fa;
}
.notification-item.unread {
    background: rgba(5, 71, 52, 0.03);
}
.notification-item .title {
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 3px;
    color: #054734;
}
.notification-item .desc {
    font-size: 12px;
    color: #666;
}
.notification-item .time {
    font-size: 10px;
    color: #999;
    margin-top: 5px;
}
</style>

<script>
$(document).ready(function() {
    function updateCounts() {
        $.get('{{ route("messenger.unread.count") }}', function(data) {
            $('.message-counter').text(data.count);
        });
        $.get('{{ route("notifications.count") }}', function(data) {
            $('.notification-counter').text(data.count);
        });
    }

    setInterval(updateCounts, 10000); // Every 10 seconds

    $('#notification-bell').on('click', function() {
        $.get('{{ route("notifications.index") }}', function(notifications) {
            let html = '';
            if (notifications.length > 0) {
                notifications.forEach(noti => {
                    let title = '';
                    let desc = '';
                    if (noti.type === 'kyc_comment') {
                        title = 'New KYC Comment';
                        desc = (noti.data.created_by_name || 'System') + ' added a KYC comment on lead "' + noti.data.lead_name + '"';
                    } else if (noti.type === 'lead_transfer') {
                        title = 'Lead Transferred';
                        desc = (noti.data.transferred_by_name || 'System') + ' transferred lead "' + noti.data.lead_name + '" to you';
                    } else if (noti.type === 'task_assignment') {
                        title = 'Task Assigned';
                        desc = (noti.data.assigned_by_name || 'System') + ' assigned task "' + noti.data.task_name + '" on lead "' + noti.data.lead_name + '"';
                    }
                    
                    html += `
                        <div class="notification-item ${noti.is_read ? '' : 'unread'}" data-id="${noti.id}">
                            <div class="title">${title}</div>
                            <div class="desc">${desc}</div>
                            <div class="time">${new Date(noti.created_at).toLocaleString()}</div>
                        </div>
                    `;
                });
            } else {
                html = '<div class="p-3 text-center text-muted">No notifications</div>';
            }
            $('#notification-dropdown .noti-body').html(html);
        });
    });

    $(document).on('click', '#mark-all-read', function(e) {
        e.preventDefault();
        $.post('{{ route("notifications.read") }}', { _token: '{{ csrf_token() }}' }, function() {
            updateCounts();
            $('.notification-item').removeClass('unread');
        });
    });

    $(document).on('click', '.notification-item', function() {
        let id = $(this).data('id');
        let $this = $(this);
        $.post('{{ route("notifications.read") }}', { _token: '{{ csrf_token() }}', id: id }, function() {
            $this.removeClass('unread');
            updateCounts();
        });
    });
});
</script>
</header>
