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
                        role="button" aria-haspopup="false" aria-expanded="false">
                        @if (!empty(Auth::user()->avatar))
                            <span class="theme-avtar">
                                <img alt="#"
                                    src="{{ check_file(Auth::user()->avatar) ? get_file(Auth::user()->avatar) : '' }}"
                                    class="rounded border-2  border-primary" width="35" height="35"
                                    style="width: 35px ; height: 35px">
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
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false" id="notification-bell">
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

                {{-- Stock Market Notification Bell --}}
                @include('stockmarket::partials._stock_bell')

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
                        href="#" role="button" aria-haspopup="false" aria-expanded="false" data-bs-placement="bottom"
                        data-bs-original-title="Select your bussiness">
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
                                            <a data-url="{{ route('workspace.edit', $workspace->id) }}" class="mx-3 btn"
                                                data-ajax-popup="true" data-title="{{ __('Edit Workspace Name') }}"
                                                data-toggle="tooltip" data-original-title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil text-success"></i>
                                            </a>
                                        </div>
                                        @endpermission
                                    @endif
                                </div>
                            @else
                                @php
                                    $route = ($workspace->is_disable == 1) ? route('workspace.change', $workspace->id) : '#';
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
                            <a href="#!" data-url="{{route('company.info', Auth::user()->id)}}" class="dropdown-item"
                                data-ajax-popup="true" data-size="lg" data-title="{{__('Workspace Info')}}">
                                <i class="ti ti-circle-x"></i>
                                <span>{{ __('View') }}</span> <br>
                            </a>


                            <hr class="dropdown-divider" />

                            <form id="remove-workspace-form" action="{{ route('workspace.destroy', getActiveWorkSpace()) }}"
                                method="POST">
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
        /* ===== NOTIFICATION BELL — PREMIUM UI ===== */
        .noti-header {
            padding: 14px 18px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #054734 0%, #198754 100%);
            border-radius: 12px 12px 0 0;
        }

        .noti-header h5 {
            color: #fff;
            font-size: 0.9rem;
            font-weight: 700;
            margin: 0;
        }

        .noti-header a {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.75rem;
            text-decoration: none;
        }

        .noti-header a:hover {
            color: #fff;
        }

        #notification-dropdown {
            min-width: 360px;
            border-radius: 14px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            padding: 0;
            overflow: hidden;
        }

        .noti-body {
            max-height: 420px;
            overflow-y: auto;
            background: #fff;
        }

        .noti-body::-webkit-scrollbar {
            width: 4px;
        }

        .noti-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .noti-body::-webkit-scrollbar-thumb {
            background: #dee2e6;
            border-radius: 2px;
        }

        .noti-card {
            display: flex;
            align-items: flex-start;
            padding: 14px 16px;
            border-bottom: 1px solid #f8f9fa;
            cursor: pointer;
            transition: background 0.18s ease;
            position: relative;
            text-decoration: none;
            color: inherit;
        }

        .noti-card:hover {
            background: #f8fdf9;
        }

        .noti-card.unread {
            background: rgba(25, 135, 84, 0.04);
        }

        .noti-card.unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, #198754, #20c997);
            border-radius: 0 2px 2px 0;
        }

        .noti-icon-wrap {
            flex-shrink: 0;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 15px;
        }

        .noti-content {
            flex: 1;
            min-width: 0;
        }

        .noti-lead-name {
            font-weight: 700;
            font-size: 0.82rem;
            color: #1a2e22;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 240px;
            display: block;
        }

        .noti-msg {
            font-size: 0.75rem;
            color: #555;
            margin-top: 2px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .noti-time {
            font-size: 0.65rem;
            color: #adb5bd;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .noti-footer {
            padding: 10px 16px;
            border-top: 1px solid #f0f0f0;
            text-align: center;
            background: #fafafa;
        }

        .noti-footer a {
            font-size: 0.75rem;
            color: #198754;
            text-decoration: none;
            font-weight: 600;
        }

        .noti-empty {
            padding: 40px 20px;
            text-align: center;
        }

        .noti-empty-icon {
            width: 56px;
            height: 56px;
            background: rgba(25, 135, 84, 0.08);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 22px;
            color: #198754;
        }
    </style>

    <script>
        $(document).ready(function () {
            // === Notification type config ===
            const notiConfig = {
                'lead_stage_change': { icon: 'ti-arrows-right-left', bg: '#fd7e14', light: 'rgba(253,126,20,0.12)', label: '{{ __('Stage Changed') }}' },
                'lead_transfer': { icon: 'ti-switch-horizontal', bg: '#6f42c1', light: 'rgba(111,66,193,0.12)', label: '{{ __('Lead Transferred') }}' },
                'lead_assigned': { icon: 'ti-user-plus', bg: '#198754', light: 'rgba(25,135,84,0.12)', label: '{{ __('Lead Assigned') }}' },
                'kyc_comment': { icon: 'ti-shield-check', bg: '#0dcaf0', light: 'rgba(13,202,240,0.12)', label: '{{ __('KYC Comment') }}' },
                'task_assignment': { icon: 'ti-list-check', bg: '#0d6efd', light: 'rgba(13,110,253,0.12)', label: '{{ __('Task Assigned') }}' },
            };
            const defaultConfig = { icon: 'ti-bell', bg: '#6c757d', light: 'rgba(108,117,125,0.12)', label: '{{ __('Notification') }}' };

            function timeAgo(dateStr) {
                const d = new Date(dateStr);
                const now = new Date();
                const diff = Math.floor((now - d) / 1000);
                if (diff < 60) return diff + '{{ __('s ago') }}';
                if (diff < 3600) return Math.floor(diff / 60) + '{{ __('m ago') }}';
                if (diff < 86400) return Math.floor(diff / 3600) + '{{ __('h ago') }}';
                return Math.floor(diff / 86400) + '{{ __('d ago') }}';
            }

            function buildNotiMessage(noti) {
                const d = noti.data || {};
                if (noti.type === 'lead_stage_change') {
                    return (d.changed_by || '{{ __('Someone') }}') + ' · ' + (d.message || '{{ __('Stage updated') }}');
                } else if (noti.type === 'lead_transfer') {
                    return (d.transferred_by_name || '{{ __('Someone') }}') + ' {{ __('transferred lead to you') }}';
                } else if (noti.type === 'lead_assigned') {
                    return d.message || '{{ __('A lead was assigned to you') }}';
                } else if (noti.type === 'kyc_comment') {
                    return (d.created_by_name || '{{ __('Someone') }}') + ' {{ __('added a KYC comment') }}';
                } else if (noti.type === 'task_assignment') {
                    return (d.assigned_by_name || '{{ __('Someone') }}') + ' {{ __('assigned a task') }}: ' + (d.task_name || '');
                }
                return d.message || '{{ __('New notification') }}';
            }

            function renderNotifications(notifications) {
                if (!notifications || notifications.length === 0) {
                    return `<div class="noti-empty">
                <div class="noti-empty-icon"><i class="ti ti-bell-off"></i></div>
                <p class="text-muted mb-0" style="font-size:0.82rem;">{{ __('You\'re all caught up!') }}</p>
                <small class="text-muted opacity-50">{{ __('No new notifications') }}</small>
            </div>`;
                }

                let html = '';
                notifications.forEach(noti => {
                    const cfg = notiConfig[noti.type] || defaultConfig;
                    const d = noti.data || {};
                    const leadName = d.lead_name || d.name || '';
                    const msg = buildNotiMessage(noti);
                    const url = d.url || '#';
                    const unreadCls = noti.is_read ? '' : 'unread';

                    html += `
            <a href="${url}" class="noti-card ${unreadCls}" data-id="${noti.id}">
                <div class="noti-icon-wrap" style="background:${cfg.light};">
                    <i class="ti ${cfg.icon}" style="color:${cfg.bg};"></i>
                </div>
                <div class="noti-content">
                    <div class="d-flex align-items-center gap-1 mb-1">
                        <span class="badge rounded-pill px-2 py-0" style="background:${cfg.light}; color:${cfg.bg}; font-size:0.6rem; font-weight:700; letter-spacing:0.3px;">${cfg.label}</span>
                        ${!noti.is_read ? '<span class="badge rounded-pill bg-success" style="width:7px;height:7px;min-width:0;padding:0;"></span>' : ''}
                    </div>
                    ${leadName ? `<span class="noti-lead-name"><i class="ti ti-user me-1" style="font-size:0.7rem;"></i>${leadName}</span>` : ''}
                    <div class="noti-msg">${msg}</div>
                    <div class="noti-time"><i class="ti ti-clock" style="font-size:0.65rem;"></i>${timeAgo(noti.created_at)}</div>
                </div>
            </a>`;
                });
                return html;
            }

            function updateCounts() {
                $.get('{{ route("messenger.unread.count") }}', function (data) {
                    $('.message-counter').text(data.count);
                });
                $.get('{{ route("notifications.count") }}', function (data) {
                    var cnt = data.count;
                    $('.notification-counter').text(cnt > 0 ? cnt : '');
                });
            }

            setInterval(updateCounts, 60000);

            $('#notification-bell').on('click', function () {
                $.get('{{ route("notifications.index") }}', function (notifications) {
                    $('#notification-dropdown .noti-body').html(renderNotifications(notifications));
                });
            });

            $(document).on('click', '#mark-all-read', function (e) {
                e.preventDefault();
                $.post('{{ route("notifications.read") }}', { _token: '{{ csrf_token() }}' }, function () {
                    updateCounts();
                    $('.noti-card').removeClass('unread');
                    $('.noti-card .badge.bg-success').remove();
                });
            });

            // Mark individual notification as read on click (handled by href navigation)
            $(document).on('click', '.noti-card', function () {
                let id = $(this).data('id');
                if (!id) return;
                $.post('{{ route("notifications.read") }}', { _token: '{{ csrf_token() }}', id: id }, function () {
                    updateCounts();
                });
            });
        });
    </script>
</header>