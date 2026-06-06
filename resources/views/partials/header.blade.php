<header
    class="dash-header {{ empty($company_settings['site_transparent']) || $company_settings['site_transparent'] == 'on' ? 'transprent-bg' : '' }} ">
    <div class="header-wrapper">
        <div class="dash-mob-drp">
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

        @if(Request::routeIs('leads.index') || Request::routeIs('leads.list'))
            <div class="d-none d-md-flex align-items-center me-auto leads-header-filters-container ms-2">
                <!-- Search bar -->
                <div class="input-group input-group shadow-sm rounded position-relative" style="width: 180px; font-weight: normal; margin-left: 5px;">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px; border-color: rgba(206, 206, 206, 0.3);"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" id="lead_search" class="form-control border-start-0 border-end-0 ps-0"
                        placeholder="{{ __('Quick search...') }}" value="{{ request('search') }}" style="border-color: rgba(206, 206, 206, 0.3); font-size: 0.85rem; height: 38px;">
                    <button class="input-group-text bg-white border-start-0" type="button" data-bs-toggle="modal"
                        data-bs-target="#searchSettingsModal" title="{{ __('Search Settings') }}" style="border-radius: 0 12px 12px 0; border-color: rgba(206, 206, 206, 0.3); height: 38px;">
                        <i class="ti ti-settings text-muted"></i>
                    </button>
                </div>

                <!-- Hidden Entries Input to maintain original compatibility -->
                <div style="display: none !important;">
                    <select id="entries_per_page">
                        <option value="10" {{ (request('length') ?: 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ (request('length') ?: 10) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ (request('length') ?: 10) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ (request('length') ?: 10) == 100 ? 'selected' : '' }}>100</option>
                        <option value="500" {{ (request('length') ?: 10) == 500 ? 'selected' : '' }}>500</option>
                    </select>
                </div>



                <!-- Advanced Filter Button Styled as dash-head-link -->
                @php
                    $activeFilterCount = 0;
                    $filterKeys = ['responsible_person', 'stage_id', 'source_id', 'start_date', 'end_date', 'created_by', 'modified_by', 'duplicates', 'department_id', 'team_id', 'modified_start_date', 'modified_end_date'];
                    foreach ($filterKeys as $key) {
                        if (request()->has($key) && !empty(request($key)))
                            $activeFilterCount++;
                        elseif (request()->has($key . '[]') && !empty(request($key . '[]')))
                            $activeFilterCount++;
                    }
                @endphp
                <a href="#!" class="dash-head-link dropdown-toggle arrow-none position-relative" data-bs-toggle="modal"
                    data-bs-target="#leadFilterModal" id="advancedFilterBtn" style="padding: 0.6rem 0.8rem; margin: 0 5px;">
                    <i class="ti ti-adjustments-horizontal text-success me-1"></i>
                    <span style="font-size: 0.85rem; font-weight: 600;">{{ __('Advanced Filter') }}</span>
                    @if($activeFilterCount > 0)
                        <span class="badge rounded-pill bg-danger filter-count-badge shadow-sm" style="position: absolute; top: -5px; right: -5px; font-size: 9px; padding: 2px 5px; border: 1.5px solid #fff;">{{ $activeFilterCount }}</span>
                    @endif
                </a>

                <!-- Clear All Filters Button Styled as dash-head-link -->
                @if($activeFilterCount > 0 || request()->has('search'))
                    <a href="javascript:void(0)" class="dash-head-link text-danger" id="clearAllFiltersHome" data-bs-toggle="tooltip" title="{{ __('Clear All Filters') }}" style="padding: 0.6rem; margin: 0 5px; width: 36px; height: 36px; justify-content: center;">
                        <i class="ti ti-trash-x" style="font-size: 1.1rem;"></i>
                    </a>
                @endif

                <!-- Extension Dropdown Styled as dash-head-link -->
                @php
                    $headerUser = Auth::user();
                @endphp
                @if(!empty($headerUser->extension_1) || !empty($headerUser->extension_2))
                    <div class="dropdown shadow-sm rounded">
                        <a href="#" class="dash-head-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" id="activeExtensionDropdown" aria-expanded="false" style="padding: 0.6rem 0.8rem; margin: 0 5px;">
                            <i class="ti ti-phone-call text-success me-1"></i>
                            <span style="font-size: 0.85rem; font-weight: 600;">Ext {{ $headerUser->active_extension == 1 ? '1' : '2' }}: {{ $headerUser->active_extension == 1 ? $headerUser->extension_1 : $headerUser->extension_2 }}</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor ms-1" style="font-size: 0.75rem;"></i>
                        </a>
                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end shadow border-0" aria-labelledby="activeExtensionDropdown"
                            style="border-radius: 12px; z-index: 1050; min-width: 200px; padding: 8px 0;">
                            @if(!empty($headerUser->extension_1))
                                <a class="dropdown-item switch-extension-btn d-flex align-items-center justify-content-between py-2 px-3 {{ $headerUser->active_extension == 1 ? 'active bg-success text-white' : '' }}"
                                    href="javascript:void(0)" data-index="1">
                                    <span>Ext 1: <strong>{{ $headerUser->extension_1 }}</strong></span>
                                    @if($headerUser->active_extension == 1) <i class="ti ti-check ms-2"></i> @endif
                                </a>
                            @endif
                            @if(!empty($headerUser->extension_2))
                                <a class="dropdown-item switch-extension-btn d-flex align-items-center justify-content-between py-2 px-3 {{ $headerUser->active_extension == 2 ? 'active bg-success text-white' : '' }}"
                                    href="javascript:void(0)" data-index="2">
                                    <span>Ext 2: <strong>{{ $headerUser->extension_2 }}</strong></span>
                                    @if($headerUser->active_extension == 2) <i class="ti ti-check ms-2"></i> @endif
                                </a>
                            @endif
                            <hr class="dropdown-divider my-2">
                            <a class="dropdown-item d-flex align-items-center text-primary py-2 px-3" href="javascript:void(0)"
                                id="manualExtensionPrompt">
                                <i class="ti ti-pencil me-2"></i>{{ __('Manage Call Settings') }}
                            </a>
                        </div>
                    </div>
                @endif

                <!-- API Dropdown Switcher Styled as dash-head-link -->
                @php
                    $settings = getCompanyAllSetting($headerUser->id, $headerUser->workspace_id);
                    $availableApis = [];
                    // 1. User
                    for($i=1; $i<=2; $i++) {
                        if(!empty($settings['user_api_'.$i.'_url_'.$headerUser->id])) {
                            $availableApis[] = ['id' => 'user_'.$i, 'name' => $settings['user_api_'.$i.'_name_'.$headerUser->id] ?: 'User API '.$i];
                        }
                    }
                    // 2. Dept
                    if(empty($availableApis) && module_is_active('Hrm', $headerUser->workspace_id)) {
                        $employee = \Workdo\Hrm\Entities\Employee::where('user_id', $headerUser->id)->first();
                        if($employee && $employee->department_id) {
                            for($i=1; $i<=2; $i++) {
                                if(!empty($settings['dept_api_'.$i.'_url_'.$employee->department_id])) {
                                    $availableApis[] = ['id' => 'dept_'.$i, 'name' => $settings['dept_api_'.$i.'_name_'.$employee->department_id] ?: 'Dept API '.$i];
                                }
                            }
                        }
                    }
                    // 3. Global
                    if(empty($availableApis)) {
                        for($i=1; $i<=3; $i++) {
                            if(!empty($settings['global_calling_api_'.$i.'_url'])) {
                                    $availableApis[] = ['id' => 'global_'.$i, 'name' => $settings['global_calling_api_'.$i.'_name'] ?: 'Global API '.$i];
                            }
                        }
                    }
                    $activeExtIdx = $headerUser->active_extension == 2 ? 2 : 1;
                    $mappedApiId = $settings['user_ext_'.$activeExtIdx.'_api_id_'.$headerUser->id] ?? '';
                    $activeApiName = '';
                    foreach($availableApis as $api) {
                        if((string)$api['id'] === (string)$mappedApiId) {
                            $activeApiName = $api['name'];
                            break;
                        }
                    }
                @endphp
                @if(!empty($availableApis))
                    <div class="dropdown">
                        <a href="#" class="dash-head-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" id="activeApiDropdown" aria-expanded="false" style="padding: 0.6rem 0.8rem; margin: 0 5px;">
                            <i class="ti ti-server text-success me-1"></i>
                            <span style="font-size: 0.85rem; font-weight: 600;">API: {{ $activeApiName ?: __('Default') }}</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor ms-1" style="font-size: 0.75rem;"></i>
                        </a>
                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end shadow border-0" aria-labelledby="activeApiDropdown"
                            style="border-radius: 12px; z-index: 1050; min-width: 220px; padding: 8px 0;">
                            @foreach($availableApis as $api)
                                <a class="dropdown-item switch-api-btn d-flex align-items-center justify-content-between py-2 px-3 {{ (string)$mappedApiId === (string)$api['id'] ? 'active bg-success text-white' : '' }}"
                                    href="javascript:void(0)" data-id="{{ $api['id'] }}">
                                    <span><strong>{{ $api['name'] }}</strong></span>
                                    @if((string)$mappedApiId === (string)$api['id']) <i class="ti ti-check ms-2"></i> @endif
                                </a>
                            @endforeach
                            <hr class="dropdown-divider my-2">
                            <a class="dropdown-item d-flex align-items-center text-success py-2 px-3" href="javascript:void(0)"
                                id="manualExtensionPrompt2">
                                <i class="ti ti-pencil me-2"></i>{{ __('Manage Call Settings') }}
                            </a>
                        </div>
                    </div>
                @endif

                <!-- A thin vertical separator line -->
                <div class="vr mx-2" style="height: 24px; background-color: #dee2e6; width: 1px; opacity: 0.7;"></div>

                <!-- Pipeline Selector Styled as dropdown Menu with dash-head-link -->
                @if (isset($pipeline))
                    <div class="dropdown d-inline-block">
                        <a href="#" class="dash-head-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" id="pipelineDropdown" aria-expanded="false" style="padding: 0.6rem 0.8rem; margin: 0 5px;">
                            <i class="ti ti-git-fork text-success me-1"></i>
                            <span style="font-size: 0.85rem; font-weight: 600;">{{ $pipeline->name }}</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor ms-1" style="font-size: 0.75rem;"></i>
                        </a>
                        <div class="dropdown-menu dash-h-dropdown dropdown-menu-end shadow border-0" aria-labelledby="pipelineDropdown" style="border-radius: 12px; z-index: 1050; min-width: 140px; padding: 8px 0;">
                            @foreach($pipelines as $id => $name)
                                <a class="dropdown-item switch-pipeline-btn d-flex align-items-center justify-content-between py-2 px-3 {{ $pipeline->id == $id ? 'active bg-success text-white' : '' }}"
                                    href="javascript:void(0)" onclick="event.preventDefault(); document.getElementById('pipeline_form_{{ $id }}').submit();">
                                    <span><strong>{{ $name }}</strong></span>
                                    @if($pipeline->id == $id) <i class="ti ti-check ms-2"></i> @endif
                                </a>
                                <form id="pipeline_form_{{ $id }}" action="{{ route('deals.change.pipeline') }}" method="POST" style="display: none;">
                                    @csrf
                                    <input type="hidden" name="default_pipeline_id" value="{{ $id }}">
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif

                @stack('addButtonHook')

                <!-- Import Button Styled as dash-head-link -->
                @permission('lead import')
                    <a href="{{ route('leads.bulk.import') }}" class="dash-head-link me-0" data-bs-toggle="tooltip" title="{{ __('Import') }}" style="padding: 0.6rem; margin: 0 5px; width: 36px; height: 36px; justify-content: center;">
                        <i class="ti ti-file-import text-success" style="font-size: 1.1rem;"></i>
                    </a>
                @endpermission

                <!-- View Toggle Button (List/Kanban) Styled as dash-head-link -->
                @if(Request::routeIs('leads.index'))
                    <a href="{{ route('leads.list') }}" class="dash-head-link me-0" data-bs-toggle="tooltip" title="{{ __('List View') }}" style="padding: 0.6rem; margin: 0 5px; width: 36px; height: 36px; justify-content: center;">
                        <i class="ti ti-list text-success" style="font-size: 1.1rem;"></i>
                    </a>
                @else
                    <a href="{{ route('leads.index') }}" class="dash-head-link me-0" data-bs-toggle="tooltip" title="{{ __('Kanban View') }}" style="padding: 0.6rem; margin: 0 5px; width: 36px; height: 36px; justify-content: center;">
                        <i class="ti ti-table text-success" style="font-size: 1.1rem;"></i>
                    </a>
                @endif

                <!-- Create Lead Button Styled as dash-head-link -->
                @permission('lead create')
                    <a class="dash-head-link me-0" data-bs-toggle="tooltip" title="{{ __('Create Lead') }}" data-ajax-popup="true" data-size="lg" data-title="{{ __('Create Lead') }}"
                        data-url="{{ route('leads.create') }}" style="cursor: pointer; padding: 0.6rem; margin: 0 5px; width: 36px; height: 36px; justify-content: center;">
                        <i class="ti ti-plus text-success" style="font-size: 1.1rem;"></i>
                    </a>
                @endpermission

                <!-- Column Selection Dropdown Styled as dash-head-link -->
                @if(Request::routeIs('leads.list'))
                    <div class="dropdown d-inline-block">
                        <a href="#" class="dash-head-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" id="columnSelectorDropdown" aria-expanded="false" style="padding: 0.6rem; margin: 0 5px; width: 36px; height: 36px; justify-content: center;">
                            <i class="ti ti-layout-grid text-success" style="font-size: 1.1rem;"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 250px; z-index: 1050;">
                            <h6 class="dropdown-header px-0 mb-2">{{ __('Showing / Hiding Columns') }}</h6>
                            <div id="column-selector-list">
                                <!-- Will be populated by JS -->
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="me-auto"></div>
        @endif

        <div class="ms-auto">
            <ul class="list-unstyled">
                @impersonating($guard = null)
                <li class="dropdown dash-h-item drp-company">
                    <a class="btn btn-danger btn-sm me-3" href="{{ route('exit.company') }}"><i class="ti ti-ban"></i>
                        {{ __('Exit Company Login') }}
                    </a>
                </li>
                @endImpersonating
                <!-- Messenger functionality removed - was causing high CPU load -->
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
        /* Responsive Header Filters */
        @media (max-width: 1750px) {
            .leads-header-filters-container span {
                display: none !important;
            }
            .leads-header-filters-container i {
                margin-right: 0 !important;
            }
        }
        @media (max-width: 1200px) {
            .leads-header-filters-container {
                display: none !important;
            }
        }
        .header-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: nowrap;
        }

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
                // Messenger count removed - functionality disabled
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