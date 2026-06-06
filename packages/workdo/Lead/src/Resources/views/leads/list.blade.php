@extends('layouts.main')

@section('page-title')
    {{ __('Leads') }}
@endsection
@push('css')
    @include('layouts.includes.datatable-css')
    <link rel="stylesheet" href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}">
    <style>
        .dash-header {
            position: fixed !important;
        }

        .page-header {
            display: none !important;
        }

        .leads-filter-bar-row {
            position: sticky !important;
            top: 124px !important;
            z-index: 1010 !important;
            background: #ffffff !important;
            padding: 10px 20px !important;
            margin-top: 0 !important;
            margin-bottom: 15px !important;
            border: 1px solid #e2e8f0 !important;
            border-top: none !important;
            border-bottom-left-radius: 12px !important;
            border-bottom-right-radius: 12px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.02) !important;
        }

        /* Card Hover Effects */
        .card-modern {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            background: linear-gradient(145deg, #ffffff, #fcfcfc);
            border-radius: 12px;
        }
        .card-modern:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
        }
        
        /* Icon Shapes */
        .icon-shape {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .card-modern:hover .icon-shape {
            transform: scale(1.15) rotate(5deg);
        }
        
        /* Funnel Items */
        .hover-glow {
            transition: all 0.2s ease-in-out;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }
        .hover-glow:hover {
            background: #ffffff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transform: scale(1.02);
            border-color: #dee2e6 !important;
        }
        
        /* Progress bars */
        .progress {
            background-color: #f1f3f5 !important;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .progress-bar {
            background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent);
            background-size: 1rem 1rem;
            animation: progress-bar-stripes 1s linear infinite;
        }
        
        /* Typography */
        .fw-bolder {
            font-weight: 800 !important;
            letter-spacing: -0.5px;
        }
        
        /* Offcanvas Sidebar */
        .offcanvas-body .list-group-item {
            padding: 12px 15px;
            border-radius: 8px !important;
            margin-bottom: 8px;
            border: 1px solid #f1f1f1;
            transition: all 0.2s;
            background-color: #ffffff;
        }
        .offcanvas-body .list-group-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border-color: #e9ecef;
        }
        .sidebar-selection-badge {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: #ffffff;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            box-shadow: 0 3px 10px rgba(78, 115, 223, 0.3);
        }
        
        /* Animations */
        @keyframes progress-bar-stripes {
            from { background-position: 1rem 0; }
            to { background-position: 0 0; }
        }
    </style>
@endpush


@section('page-action')
@endsection

@section('content')
    @include('lead::leads.filter_bar')

    @php
        $usr = Auth::user();
        $workspaceId = getActiveWorkSpace();
        $accessibleUserIds = $usr->getAccessibleUserIds();

        // Base query matching LeadDataTable logic
        $leadsQuery = \Workdo\Lead\Entities\Lead::where('leads.pipeline_id', '=', $pipeline->id)
            ->where('leads.workspace_id', '=', $workspaceId);

        // Apply visibility restrictions
        if ($usr->type != 'company' && $usr->visibility_level != 'all') {
            $leadsQuery->where(function ($q) use ($accessibleUserIds) {
                $q->whereIn('leads.user_id', $accessibleUserIds)
                    ->orWhereExists(function ($subQ) use ($accessibleUserIds) {
                        $subQ->select(\DB::raw(1))
                            ->from('user_leads')
                            ->whereColumn('user_leads.lead_id', 'leads.id')
                            ->whereIn('user_leads.user_id', $accessibleUserIds);
                    });
            });
        }

        // Apply Stage-based visibility (Restrict leads from hidden/restricted stages)
        if ($usr->type != 'company') {
            $hiddenStageIds = [];
            $allStagesInPipeline = \Workdo\Lead\Entities\LeadStage::where('pipeline_id', $pipeline->id)->where('workspace_id', $workspaceId)->get();
            
            $filteredStages = [];
            if (request()->has('stage_id') && !empty(request('stage_id'))) {
                $filteredStages = (array) request('stage_id');
            }

            foreach ($allStagesInPipeline as $s) {
                // If can_view is false, always hide the stage (cannot see it at all)
                if (!$s->permissions($usr)->can_view) {
                    $hiddenStageIds[] = $s->id;
                    continue;
                }
                
                // If can_edit is false:
                // Hide it by default, UNLESS the user has explicitly selected it in the filter dropdown.
                if (!$s->permissions($usr)->can_edit) {
                    if (!in_array($s->id, $filteredStages)) {
                        $hiddenStageIds[] = $s->id;
                    }
                }
            }
            if (!empty($hiddenStageIds)) {
                $leadsQuery->whereNotIn('leads.stage_id', $hiddenStageIds);
            }
        }

        // Apply Custom Filters from request
        if (request()->has('responsible_person') && !empty(request('responsible_person'))) {
            $respIds = (array) request('responsible_person');
            $leadsQuery->where(function ($q) use ($respIds) {
                $q->whereIn('leads.user_id', $respIds)
                    ->orWhereExists(function ($subQ) use ($respIds) {
                        $subQ->select(\DB::raw(1))
                            ->from('user_leads')
                            ->whereColumn('user_leads.lead_id', 'leads.id')
                            ->whereIn('user_leads.user_id', $respIds);
                    });
            });
        }

        if (request()->has('stage_id') && !empty(request('stage_id'))) {
            $leadsQuery->whereIn('leads.stage_id', (array) request('stage_id'));
        }

        if (request()->has('source_id') && !empty(request('source_id'))) {
            $leadsQuery->where(function ($q) {
                foreach ((array) request('source_id') as $source) {
                    $q->orWhereRaw('FIND_IN_SET(?, leads.sources)', [$source]);
                }
            });
        }

        if (request()->has('start_date') && !empty(request('start_date'))) {
            $leadsQuery->where('leads.created_at', '>=', request('start_date') . ' 00:00:00');
        }

        if (request()->has('end_date') && !empty(request('end_date'))) {
            $leadsQuery->where('leads.created_at', '<=', request('end_date') . ' 23:59:59');
        }

        if (request()->has('modified_start_date') && !empty(request('modified_start_date'))) {
            $leadsQuery->where('leads.updated_at', '>=', request('modified_start_date') . ' 00:00:00');
        }

        if (request()->has('modified_end_date') && !empty(request('modified_end_date'))) {
            $leadsQuery->where('leads.updated_at', '<=', request('modified_end_date') . ' 23:59:59');
        }

        if (request()->has('created_by') && !empty(request('created_by'))) {
            $leadsQuery->whereIn('leads.created_by', (array) request('created_by'));
        }

        if (request()->has('modified_by') && !empty(request('modified_by'))) {
            $leadsQuery->whereIn('leads.updated_by', (array) request('modified_by'));
        }

        if (request()->has('department_id') && !empty(request('department_id'))) {
            $departmentIds = (array) request('department_id');
            $childTeamIds = \Workdo\Hrm\Entities\Department::whereIn('parent_id', $departmentIds)
                ->where('type', 'team')
                ->where('workspace', $workspaceId)
                ->pluck('id')
                ->toArray();
            $allDeptAndTeamIds = array_merge($departmentIds, $childTeamIds);
            $deptUserIds = \Workdo\Hrm\Entities\Employee::whereIn('department_id', $allDeptAndTeamIds)
                ->where('workspace', $workspaceId)
                ->pluck('user_id')
                ->toArray();
            $leadsQuery->where(function ($q) use ($deptUserIds) {
                $q->whereIn('leads.user_id', $deptUserIds)
                    ->orWhereExists(function ($subQ) use ($deptUserIds) {
                        $subQ->select(\DB::raw(1))
                            ->from('user_leads')
                            ->whereColumn('user_leads.lead_id', 'leads.id')
                            ->whereIn('user_leads.user_id', $deptUserIds);
                    });
            });
        }

        if (request()->has('team_id') && !empty(request('team_id'))) {
            $teamIds = (array) request('team_id');
            $desigUserIds = \Workdo\Hrm\Entities\Employee::whereIn('department_id', $teamIds)
                ->where('workspace', $workspaceId)
                ->pluck('user_id')
                ->toArray();
            $leadsQuery->where(function ($q) use ($desigUserIds) {
                $q->whereIn('leads.user_id', $desigUserIds)
                    ->orWhereExists(function ($subQ) use ($desigUserIds) {
                        $subQ->select(\DB::raw(1))
                            ->from('user_leads')
                            ->whereColumn('user_leads.lead_id', 'leads.id')
                            ->whereIn('user_leads.user_id', $desigUserIds);
                    });
            });
        }

        if (request()->has('duplicates') && request('duplicates') == 1) {
            $leadsQuery->where(function ($q) use ($workspaceId) {
                $q->whereRaw("leads.email IN (SELECT email FROM (SELECT email FROM leads WHERE workspace_id = ? AND email IS NOT NULL AND email != '' GROUP BY email HAVING COUNT(email) > 1) as temp_email)", [$workspaceId])
                    ->orWhereRaw("leads.phone IN (SELECT phone FROM (SELECT phone FROM leads WHERE workspace_id = ? AND phone IS NOT NULL AND phone != '' GROUP BY phone HAVING COUNT(phone) > 1) as temp_phone)", [$workspaceId])
                    ->orWhereRaw("leads.name IN (SELECT name FROM (SELECT name FROM leads WHERE workspace_id = ? AND name IS NOT NULL AND name != '' GROUP BY name HAVING COUNT(name) > 1) as temp_name)", [$workspaceId]);
            });
        }

        if (request()->has('search') && !empty(request('search'))) {
            $search = request('search');
            if (is_array($search)) {
                $search = isset($search['value']) ? $search['value'] : null;
            }
            if ($search) {
                $leadsQuery->where(function ($q) use ($search) {
                    $q->where('leads.name', 'like', "%$search%")
                        ->orWhere('leads.subject', 'like', "%$search%");
                });
            }
        }

        // Clone queries for counts to ensure performance
        $totalLeads = (clone $leadsQuery)->count();
        $convertedLeads = (clone $leadsQuery)->where('is_converted', '>', 0)->count();
        $pendingLeads = $totalLeads - $convertedLeads;
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        $leadCustomFields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $workspaceId)->get();
        $leadStages = \Workdo\Lead\Entities\LeadStage::where('pipeline_id', $pipeline->id)->where('workspace_id', $workspaceId)->get();
        $cardsConfigJson = company_setting('leads_stats_cards_config');
        $cardsConfig = $cardsConfigJson ? json_decode($cardsConfigJson, true) : [];

        $defaultConfig = [
            ['type' => 'system', 'value' => 'total_leads'],
            ['type' => 'system', 'value' => 'converted_leads'],
            ['type' => 'system', 'value' => 'pending_leads'],
            ['type' => 'system', 'value' => 'conversion_rate'],
        ];
        if (empty($cardsConfig) || count($cardsConfig) < 4) {
            $cardsConfig = array_merge($cardsConfig, array_slice($defaultConfig, count($cardsConfig)));
        }

        $cardsData = [];
        $cardColors = [
            0 => ['border' => '#198754', 'bg' => 'bg-success-subtle', 'text' => 'text-success', 'progress_bg' => 'bg-success'],
            1 => ['border' => '#ffc107', 'bg' => 'bg-warning-subtle', 'text' => 'text-warning', 'progress_bg' => 'bg-warning'],
            2 => ['border' => '#3b82f6', 'bg' => 'bg-primary-subtle', 'text' => 'text-primary', 'progress_bg' => 'bg-primary'],
            3 => ['border' => '#20c997', 'bg' => 'bg-info-subtle', 'text' => 'text-info', 'progress_bg' => 'bg-info'],
        ];

        foreach ($cardsConfig as $index => $card) {
            $type = $card['type'] ?? 'system';
            $val = $card['value'] ?? '';
            $title = '';
            $displayValue = '';
            $subText = '';
            $icon = 'ti ti-chart-bar';
            $progressPercent = null;

            if ($type === 'system') {
                if ($val === 'total_leads') {
                    $title = __('Total Leads');
                    $displayValue = number_format($totalLeads);
                    $subText = __('Pipeline active');
                    $icon = 'ti ti-users';
                } elseif ($val === 'converted_leads') {
                    $title = __('Converted Leads');
                    $displayValue = number_format($convertedLeads);
                    $subText = __('Converted to Deals');
                    $icon = 'ti ti-trophy';
                } elseif ($val === 'pending_leads') {
                    $title = __('Pending Leads');
                    $displayValue = number_format($pendingLeads);
                    $subText = __('Awaiting Conversion');
                    $icon = 'ti ti-timeline';
                } elseif ($val === 'conversion_rate') {
                    $title = __('Conversion Rate');
                    $displayValue = $conversionRate . '%';
                    $subText = __('Conversion Percentage');
                    $icon = 'ti ti-chart-line';
                    $progressPercent = $conversionRate;
                }
            } elseif ($type === 'stage') {
                $stageObj = $leadStages->firstWhere('id', $val);
                $stageName = $stageObj ? $stageObj->name : __('Unknown Stage');
                $title = $stageName;
                $stageCount = (clone $leadsQuery)->where('leads.stage_id', '=', $val)->count();
                $displayValue = number_format($stageCount);
                $subText = __('Leads in stage');
                $icon = 'ti ti-arrow-right-circle';
                if ($totalLeads > 0) {
                    $progressPercent = round(($stageCount / $totalLeads) * 100, 1);
                    $subText = $progressPercent . '% ' . __('of total leads');
                }
            } elseif ($type === 'custom_field') {
                $cfObj = $leadCustomFields->firstWhere('id', $val);
                $cfName = $cfObj ? $cfObj->name : __('Unknown Field');
                $title = $cfName;
                $filledCount = (clone $leadsQuery)->whereExists(function ($sub) use ($val) {
                    $sub->select(\DB::raw(1))
                        ->from('lead_custom_field_values')
                        ->whereColumn('lead_custom_field_values.lead_id', 'leads.id')
                        ->where('lead_custom_field_values.field_id', $val)
                        ->whereNotNull('lead_custom_field_values.value')
                        ->where('lead_custom_field_values.value', '!=', '');
                })->count();
                $displayValue = number_format($filledCount);
                $icon = 'ti ti-file-text';
                if ($totalLeads > 0) {
                    $progressPercent = round(($filledCount / $totalLeads) * 100, 1);
                    $subText = $progressPercent . '% ' . __('data completed');
                } else {
                    $subText = __('Custom field populated');
                }
            }

            $cardsData[$index] = [
                'title' => $title,
                'value' => $displayValue,
                'subText' => $subText,
                'icon' => $icon,
                'progressPercent' => $progressPercent,
                'colors' => $cardColors[$index] ?? $cardColors[0],
                'type' => $type,
                'val' => $val,
            ];
        }
    @endphp

    @if ($pipeline)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 text-muted fw-bold">{{ __('Pipeline Statistics') }}</h5>
            <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#configureStatsModal" title="{{ __('Configure Metrics Cards') }}">
                <i class="ti ti-settings fs-6"></i>
                <span class="d-none d-sm-inline">{{ __('Configure Cards') }}</span>
            </button>
        </div>
        <!-- Advanced Metrics Dashboard -->
        <div class="row mb-3">
            @foreach($cardsData as $index => $card)
                <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                    <div class="card card-modern border-0 shadow-sm h-100 overflow-hidden position-relative hover-glow" style="border-left: 3px solid {{ $card['colors']['border'] }} !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                <span class="text-muted text-uppercase fw-bold text-xs" style="letter-spacing: 0.5px; font-size: 0.72rem;" title="{{ $card['title'] }}">{{ $card['title'] }}</span>
                                <div class="icon-shape {{ $card['colors']['bg'] }} {{ $card['colors']['text'] }} rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                    <i class="{{ $card['icon'] }} fs-6"></i>
                                </div>
                            </div>
                            <h4 class="mb-1 fw-bold text-dark" style="font-size: 1.35rem;">{{ $card['value'] }}</h4>
                            <div class="d-flex align-items-center text-xs mt-1.5 w-100">
                                @if($card['progressPercent'] !== null)
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-muted" style="font-size: 0.7rem;">{{ $card['subText'] }}</span>
                                        </div>
                                        <div class="progress" style="height: 4px; border-radius: 10px;">
                                            <div class="progress-bar {{ $card['colors']['progress_bg'] }}" role="progressbar" style="width: {{ $card['progressPercent'] }}%" aria-valuenow="{{ $card['progressPercent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                @else
                                    <span class="{{ $card['colors']['text'] }} fw-bold me-1" style="font-size: 0.72rem;">
                                        <i class="ti ti-arrow-right"></i> {{ $card['subText'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-xl-9 col-lg-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body table-border-style">
                        <h5></h5>
                        <div class="table-responsive">
                            {{ $dataTable->table(['width' => '100%']) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-bottom bg-transparent py-3">
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                            <i class="ti ti-chart-bar me-2 text-primary fs-4"></i>{{ __('Stage Funnel') }}
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        @php
                            $stageData = [];
                            $stageColors = [
                                '#6366f1', // Indigo
                                '#0ea5e9', // Sky blue
                                '#10b981', // Emerald
                                '#f59e0b', // Amber
                                '#f43f5e', // Rose
                                '#8b5cf6', // Violet
                                '#ec4899', // Pink
                                '#14b8a6', // Teal
                                '#f97316', // Orange
                            ];
                            foreach ($leadStages as $idx => $stage) {
                                $count = (clone $leadsQuery)->where('leads.stage_id', '=', $stage->id)->count();
                                $pct = $totalLeads > 0 ? round(($count / $totalLeads) * 100, 1) : 0;
                                $stageData[] = [
                                    'name' => $stage->name,
                                    'count' => $count,
                                    'pct' => $pct,
                                    'color' => $stageColors[$idx % count($stageColors)],
                                ];
                            }
                        @endphp
                        
                        @if(!empty($stageData))
                            <div class="d-flex flex-column gap-3">
                                @foreach($stageData as $sd)
                                    <div class="p-2 rounded border border-light bg-light-subtle hover-glow transition-all">
                                        <div class="d-flex justify-content-between align-items-center mb-1 text-xs">
                                            <span class="fw-bold text-muted text-truncate" style="max-width: 65%;" title="{{ $sd['name'] }}">
                                                <span class="d-inline-block rounded-circle me-1" style="width: 8px; height: 8px; background-color: {{ $sd['color'] }};"></span>
                                                {{ $sd['name'] }}
                                            </span>
                                            <span class="badge bg-light text-dark fw-bold border">{{ $sd['count'] }} ({{ $sd['pct'] }}%)</span>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 3px; background-color: #e9ecef;">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $sd['pct'] }}%; background-color: {{ $sd['color'] }};" aria-valuenow="{{ $sd['pct'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <span class="text-muted text-xs">{{ __('No stages configured for this pipeline') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Bulk Action Sidebar (Offcanvas) - Only visible if user has edit or delete permissions -->
    @if(Auth::user()->isAbleTo('lead edit') || Auth::user()->isAbleTo('lead delete'))
    <div class="offcanvas offcanvas-end" tabindex="-1" id="bulkActionSidebar" aria-labelledby="bulkActionSidebarLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold" id="bulkActionSidebarLabel">
                <i class="ti ti-layers-subtract me-2 text-primary"></i>{{ __('Bulk Actions') }}
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <!-- Selection Info -->
            <div class="card border-0 bg-light mb-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small text-uppercase fw-bold">{{ __('Current Selection') }}</span>
                        <span class="sidebar-selection-badge" id="selected-count">0</span>
                    </div>
                    <div id="select-all-banner" style="display: none;" class="mt-2 p-2 bg-white rounded border">
                        <p class="small mb-1 text-dark">{{ __('All leads on this page are selected.') }}</p>
                        <a href="javascript:void(0)" id="select-all-matching-link" class="btn btn-sm btn-link p-0 fw-bold text-primary text-decoration-underline">
                            {{ __('Select all matching records') }}
                        </a>
                    </div>
                    <div id="all-selected-banner" style="display: none;" class="mt-2 p-2 bg-white rounded border text-success">
                        <i class="ti ti-check-double me-1"></i><span class="small fw-bold">{{ __('All matching records are selected.') }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Action List -->
            <h6 class="text-muted small text-uppercase fw-bold mb-3">{{ __('Available Actions') }}</h6>
            <div class="list-group list-group-flush mb-4">
                @permission('lead edit')
                <button class="list-group-item list-group-item-action d-flex align-items-center" id="bulk-change-stage">
                    <div class="bg-soft-primary p-2 rounded me-3">
                        <i class="ti ti-arrow-forward-up text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="fw-bold">{{ __('Change Stage') }}</div>
                        <small class="text-muted">{{ __('Move selected leads to another stage') }}</small>
                    </div>
                </button>
                <button class="list-group-item list-group-item-action d-flex align-items-center" id="bulk-change-owner">
                    <div class="bg-soft-info p-2 rounded me-3">
                        <i class="ti ti-user text-info fs-4"></i>
                    </div>
                    <div>
                        <div class="fw-bold">{{ __('Change Responsible') }}</div>
                        <small class="text-muted">{{ __('Reassign these leads to another team member') }}</small>
                    </div>
                </button>
                <button class="list-group-item list-group-item-action d-flex align-items-center" id="bulk-task-reminder">
                    <div class="bg-soft-warning p-2 rounded me-3">
                        <i class="ti ti-calendar-event text-warning fs-4"></i>
                    </div>
                    <div>
                        <div class="fw-bold">{{ __('Add Task/Reminder') }}</div>
                        <small class="text-muted">{{ __('Schedule follow-ups for all selected') }}</small>
                    </div>
                </button>
                @endpermission
                <button class="list-group-item list-group-item-action d-flex align-items-center text-success" id="bulk-export">
                    <div class="bg-soft-success p-2 rounded me-3">
                        <i class="ti ti-download text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-success">{{ __('Export Selected') }}</div>
                        <small class="text-muted">{{ __('Download selected leads as CSV') }}</small>
                    </div>
                </button>
                @permission('lead delete')
                <button class="list-group-item list-group-item-action d-flex align-items-center text-danger" id="bulk-delete">
                    <div class="bg-soft-danger p-2 rounded me-3">
                        <i class="ti ti-trash text-danger fs-4"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-danger">{{ __('Delete Selected') }}</div>
                        <small class="text-muted">{{ __('Permanently remove these leads') }}</small>
                    </div>
                </button>
                @endpermission
            </div>
            
            <div class="mt-auto pt-3 border-top">
                <button class="btn btn-outline-secondary w-100 rounded-pill" id="clear-selection">
                    <i class="ti ti-x me-1"></i> {{ __('Clear Selection & Close') }}
                </button>
            </div>
        </div>
    </div>
    @endif
    <div class="modal fade" id="bulkProgressModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                            <span class="visually-hidden">Processing...</span>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-2">{{ __('Processing Bulk Action') }}</h5>
                    <p class="text-muted small mb-3" id="bulk-progress-status">{{ __('Initializing...') }}</p>
                    <div class="progress mb-2" style="height: 12px; border-radius: 10px; background-color: #e9ecef;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                             role="progressbar" id="bulk-progress-bar" 
                             style="width: 0%; border-radius: 10px; transition: width 0.4s ease;"></div>
                    </div>
                    <span class="fw-bold text-primary" id="bulk-progress-percent">0%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Column Selector Modal -->
    <div class="modal fade" id="exportColumnsModal" tabindex="-1" aria-labelledby="exportColumnsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom px-4 py-3" style="background: linear-gradient(135deg, #f8f9fa, #ffffff);">
                    <h5 class="modal-title fw-bold text-dark" id="exportColumnsModalLabel">
                        <i class="ti ti-table-export me-2 text-success"></i>{{ __('Select Columns to Export') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        {{ __('Choose which columns you want to include in the exported CSV file. Custom fields are included below.') }}
                    </p>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary me-2" id="export-select-all-cols">
                                <i class="ti ti-check me-1"></i>{{ __('Select All') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="export-deselect-all-cols">
                                <i class="ti ti-x me-1"></i>{{ __('Deselect All') }}
                            </button>
                        </div>
                        <small class="text-muted" id="export-col-count">0 {{ __('selected') }}</small>
                    </div>

                    <div class="row g-2" id="export-columns-list">
                        <!-- Standard Columns -->
                        <div class="col-12 mb-1">
                            <span class="text-muted small text-uppercase fw-bold">{{ __('Standard Fields') }}</span>
                        </div>
                        @php
                            $exportableColumns = [
                                'id'                => __('Lead ID'),
                                'name'              => __('Name'),
                                'email'             => __('Email'),
                                'phone'             => __('Phone'),
                                'pipeline'          => __('Pipeline'),
                                'stage_id'          => __('Stage'),
                                'user_id'           => __('Responsible Person'),
                                'created_at'        => __('Created At'),
                                'updated_at'        => __('Modified At'),
                                'subject'           => __('Subject'),
                                'follow_up_date'    => __('Follow Up Date'),
                                'sources'           => __('Sources'),
                                'created_by'        => __('Created By'),
                                'updated_by'        => __('Modified By'),
                                'team'              => __('Team / Department'),
                            ];
                        @endphp
                        @foreach($exportableColumns as $colKey => $colLabel)
                            <div class="col-md-4 col-6">
                                <div class="form-check form-switch p-0">
                                    <label class="d-flex align-items-center gap-2 p-2 rounded border bg-light-subtle cursor-pointer export-col-item" style="cursor:pointer;">
                                        <input type="checkbox" class="form-check-input export-col-checkbox mt-0" value="{{ $colKey }}"
                                            {{ in_array($colKey, ['id','name','email','phone','pipeline','stage_id','user_id','created_at']) ? 'checked' : '' }}>
                                        <span class="small fw-medium">{{ $colLabel }}</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach

                        @if(isset($leadCustomFields) && $leadCustomFields->count() > 0)
                        <div class="col-12 mb-1 mt-3">
                            <span class="text-muted small text-uppercase fw-bold">{{ __('Custom Fields') }}</span>
                        </div>
                        @foreach($leadCustomFields as $cf)
                            <div class="col-md-4 col-6">
                                <div class="form-check form-switch p-0">
                                    <label class="d-flex align-items-center gap-2 p-2 rounded border bg-primary-subtle cursor-pointer export-col-item" style="cursor:pointer;">
                                        <input type="checkbox" class="form-check-input export-col-checkbox mt-0" value="custom_{{ $cf->id }}"
                                            data-custom-field-id="{{ $cf->id }}" data-custom-field-name="{{ $cf->name }}">
                                        <span class="small fw-medium">{{ $cf->name }}</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                        @endif
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-success px-4" id="confirm-export-columns">
                        <i class="ti ti-download me-2"></i>{{ __('Export CSV') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Configure Stats Modal -->
    <div class="modal fade" id="configureStatsModal" tabindex="-1" aria-labelledby="configureStatsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark" id="configureStatsModalLabel">
                        <i class="ti ti-settings me-2 text-primary"></i>{{ __('Configure Statistics Cards') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="statsConfigForm" action="{{ route('leads.stats.config.save') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="text-muted small mb-4">
                            {{ __('Customize the four metrics cards at the top of the Leads list. You can choose to display default system stats, lead counts for a specific stage, or populated counts for custom fields.') }}
                        </p>
                        
                        <div class="d-flex flex-column gap-3">
                            @for($i = 1; $i <= 4; $i++)
                                <div class="row align-items-center bg-light p-3 rounded-3 mx-0">
                                    <div class="col-md-3">
                                        <span class="fw-bold text-dark">{{ __('Card') }} {{ $i }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select card-type-select shadow-sm" data-card-index="{{ $i - 1 }}" id="card_{{ $i }}_type" name="cards[{{ $i - 1 }}][type]" style="border-radius: 8px;">
                                            <option value="system" {{ ($cardsConfig[$i - 1]['type'] ?? 'system') === 'system' ? 'selected' : '' }}>{{ __('System Stat') }}</option>
                                            <option value="stage" {{ ($cardsConfig[$i - 1]['type'] ?? '') === 'stage' ? 'selected' : '' }}>{{ __('Pipeline Stage Count') }}</option>
                                            <option value="custom_field" {{ ($cardsConfig[$i - 1]['type'] ?? '') === 'custom_field' ? 'selected' : '' }}>{{ __('Custom Field populated') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5 mt-2 mt-md-0">
                                        <select class="form-select card-value-select shadow-sm" id="card_{{ $i }}_value" name="cards[{{ $i - 1 }}][value]" style="border-radius: 8px;">
                                            <!-- Will be populated dynamically by JavaScript -->
                                        </select>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3 bg-light" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" id="saveConfigBtn" class="btn btn-primary rounded-pill px-4 shadow-sm">{{ __('Save Configuration') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Action Report Modal -->
    <div class="modal fade" id="bulkReportModal" tabindex="-1" aria-labelledby="bulkReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header border-bottom px-4 py-3" style="background: linear-gradient(135deg, #1d4ed8, #3b82f6); color: white;">
                    <h5 class="modal-title fw-bold text-white" id="bulkReportModalLabel">
                        <i class="ti ti-report me-2"></i>{{ __('Lead Action Summary Report') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Summary Badges -->
                    <div class="row g-3 mb-4 text-center">
                        <div class="col-md-4">
                            <div class="card border-0 bg-light p-3 h-100" style="border-radius: 12px;">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">{{ __('Total Processed') }}</span>
                                <h3 class="fw-bold text-dark mb-0" id="report-total-count">0</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-success-subtle p-3 h-100" style="border-radius: 12px;">
                                <span class="text-success small fw-bold text-uppercase d-block mb-1">{{ __('Successfully Actioned') }}</span>
                                <h3 class="fw-bold text-success mb-0" id="report-success-count">0</h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-danger-subtle p-3 h-100" style="border-radius: 12px;">
                                <span class="text-danger small fw-bold text-uppercase d-block mb-1">{{ __('Skipped / Failed') }}</span>
                                <h3 class="fw-bold text-danger mb-0" id="report-skipped-count">0</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Tabs -->
                    <ul class="nav nav-pills nav-fill mb-3 bg-light p-1" id="reportTabs" role="tablist" style="border-radius: 10px;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold text-dark rounded-3" id="success-tab" data-bs-toggle="pill" data-bs-target="#success-pane" type="button" role="tab" aria-controls="success-pane" aria-selected="true">
                                <i class="ti ti-circle-check text-success me-1"></i>{{ __('Successful') }} (<span id="success-tab-count">0</span>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold text-dark rounded-3" id="skipped-tab" data-bs-toggle="pill" data-bs-target="#skipped-pane" type="button" role="tab" aria-controls="skipped-pane" aria-selected="false">
                                <i class="ti ti-circle-x text-danger me-1"></i>{{ __('Skipped / Failed') }} (<span id="skipped-tab-count">0</span>)
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="reportTabsContent">
                        <!-- Success Pane -->
                        <div class="tab-pane fade show active" id="success-pane" role="tabpanel" aria-labelledby="success-tab">
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>{{ __('Lead ID') }}</th>
                                            <th>{{ __('Lead Name') }}</th>
                                            <th>{{ __('Original Stage') }}</th>
                                            <th id="report-success-target-header">{{ __('Transferred To') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="report-success-tbody">
                                        <!-- Populated dynamically -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="report-success-empty" class="text-center py-4 text-muted d-none">
                                <i class="ti ti-info-circle fs-2 mb-2"></i>
                                <p class="mb-0">{{ __('No successful actions in this report.') }}</p>
                            </div>
                        </div>

                        <!-- Skipped Pane -->
                        <div class="tab-pane fade" id="skipped-pane" role="tabpanel" aria-labelledby="skipped-tab">
                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>{{ __('Lead ID') }}</th>
                                            <th>{{ __('Lead Name') }}</th>
                                            <th>{{ __('Current Stage') }}</th>
                                            <th>{{ __('Reason for Failure') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="report-skipped-tbody">
                                        <!-- Populated dynamically -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="report-skipped-empty" class="text-center py-4 text-muted d-none">
                                <i class="ti ti-info-circle fs-2 mb-2"></i>
                                <p class="mb-0">{{ __('No skipped or failed actions in this report.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3 bg-light" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    @include('layouts.includes.datatable-js')
    {{ $dataTable->scripts() }}

    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
    <script>
        var selectedLeads = [];
        var selectAllMatching = false;
        var totalRecordsOnPage = 0;
        var totalRecordsTotal = 0;

        // Bulk Processor Utility
        class BulkProcessor {
            constructor(options) {
                this.action = options.action; // 'delete', 'change_stage', etc.
                this.value = options.value || null; // target stage ID, owner ID, etc.
                this.ids = options.ids; // Array of IDs
                this.chunkSize = 50;
                this.currentIndex = 0;
                this.onComplete = options.onComplete || ((msg, report) => {});
                this.lastMessage = null;
                this.url = options.url || '{{ route("leads.bulk.action") }}';
                this.extraData = options.extraData || {};
                
                this.modal = $('#bulkProgressModal');
                this.bar = document.getElementById('bulk-progress-bar');
                this.status = document.getElementById('bulk-progress-status');
                this.percentText = document.getElementById('bulk-progress-percent');

                // Accumulated report data
                this.accumulatedReport = {
                    action: this.action,
                    success_count: 0,
                    skipped_count: 0,
                    success_details: [],
                    skipped_details: []
                };
            }

            async start() {
                const showModal = this.ids.length > 1;
                if (showModal) {
                    this.modal.modal('show');
                    this.updateUI(0);
                }
                
                const chunks = this.getChunks();
                for (const chunk of chunks) {
                    try {
                        const response = await this.processChunk(chunk);
                        if (!response.success) {
                             if (showModal) {
                                 // Add a small delay to ensure Bootstrap 'shown' transition finished
                                 setTimeout(() => this.modal.modal('hide'), 300);
                             }
                             toastrs('error', response.message || 'Unknown error', 'error');
                             return;
                        }

                        // Accumulate chunk report
                        if (response.report) {
                            if (response.report.success_count) {
                                this.accumulatedReport.success_count += response.report.success_count;
                            }
                            if (response.report.skipped_count) {
                                this.accumulatedReport.skipped_count += response.report.skipped_count;
                            }
                            if (response.report.success_details) {
                                this.accumulatedReport.success_details = this.accumulatedReport.success_details.concat(response.report.success_details);
                            }
                            if (response.report.skipped_details) {
                                this.accumulatedReport.skipped_details = this.accumulatedReport.skipped_details.concat(response.report.skipped_details);
                            }
                        }

                        this.lastMessage = response.message;
                        this.currentIndex += chunk.length;
                        if (showModal) {
                            this.updateUI((this.currentIndex / this.ids.length) * 100);
                        }
                    } catch (error) {
                        if (showModal) {
                            setTimeout(() => this.modal.modal('hide'), 300);
                        }
                        toastrs('error', error.message || 'Error processing chunk', 'error');
                        return;
                    }
                }

                if (showModal) {
                    setTimeout(() => {
                        this.modal.modal('hide');
                        this.onComplete(this.lastMessage, this.accumulatedReport);
                    }, 500);
                } else {
                    this.onComplete(this.lastMessage, this.accumulatedReport);
                }
            }

            getChunks() {
                const chunks = [];
                for (let i = 0; i < this.ids.length; i += this.chunkSize) {
                    chunks.push(this.ids.slice(i, i + this.chunkSize));
                }
                return chunks;
            }

            processChunk(chunkIds) {
                return $.ajax({
                    url: this.url,
                    type: 'POST',
                    data: $.extend({
                        ids: chunkIds,
                        action: this.action,
                        value: this.value,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }, this.extraData)
                });
            }

            updateUI(percent) {
                percent = Math.round(percent);
                this.bar.style.width = percent + '%';
                this.percentText.innerText = percent + '%';
                this.status.innerText = `Processed ${this.currentIndex} of ${this.ids.length} leads...`;
            }
        }

        // Helper to show the dynamic bulk report modal
        function showBulkReportModal(report) {
            const total = (report.success_count || 0) + (report.skipped_count || 0);
            $('#report-total-count').text(total);
            $('#report-success-count').text(report.success_count || 0);
            $('#report-skipped-count').text(report.skipped_count || 0);
            
            $('#success-tab-count').text(report.success_count || 0);
            $('#skipped-tab-count').text(report.skipped_count || 0);

            // Set column header dynamically based on action
            if (report.action === 'change_owner') {
                $('#report-success-target-header').text('{{ __("Transferred To") }}');
            } else if (report.action === 'change_stage') {
                $('#report-success-target-header').text('{{ __("New Stage") }}');
            } else {
                $('#report-success-target-header').text('{{ __("Action Result") }}');
            }

            // Populate Success Tbody
            const successTbody = $('#report-success-tbody');
            successTbody.empty();
            if (report.success_details && report.success_details.length > 0) {
                $('#report-success-empty').addClass('d-none');
                successTbody.parent().show();
                
                report.success_details.forEach(item => {
                    const targetVal = report.action === 'change_owner' ? item.target_owner : item.target_stage;
                    successTbody.append(`
                        <tr>
                            <td><span class="badge bg-light text-dark font-monospace">${item.id}</span></td>
                            <td class="fw-bold">${item.name}</td>
                            <td><span class="badge bg-light text-secondary border">${item.stage}</span></td>
                            <td><span class="badge bg-success-subtle text-success fw-bold">${targetVal}</span></td>
                        </tr>
                    `);
                });
            } else {
                successTbody.parent().hide();
                $('#report-success-empty').removeClass('d-none');
            }

            // Populate Skipped Tbody
            const skippedTbody = $('#report-skipped-tbody');
            skippedTbody.empty();
            if (report.skipped_details && report.skipped_details.length > 0) {
                $('#report-skipped-empty').addClass('d-none');
                skippedTbody.parent().show();

                report.skipped_details.forEach(item => {
                    skippedTbody.append(`
                        <tr>
                            <td><span class="badge bg-light text-dark font-monospace">${item.id}</span></td>
                            <td class="fw-bold">${item.name}</td>
                            <td><span class="badge bg-light text-secondary border">${item.stage}</span></td>
                            <td><span class="text-danger small fw-semibold">${item.reason}</span></td>
                        </tr>
                    `);
                });
            } else {
                skippedTbody.parent().hide();
                $('#report-skipped-empty').removeClass('d-none');
            }

            // Auto-switch tabs
            if (report.success_count === 0 && report.skipped_count > 0) {
                const triggerEl = document.querySelector('#skipped-tab');
                if (triggerEl) {
                    bootstrap.Tab.getOrCreateInstance(triggerEl).show();
                }
            } else {
                const triggerEl = document.querySelector('#success-tab');
                if (triggerEl) {
                    bootstrap.Tab.getOrCreateInstance(triggerEl).show();
                }
            }

            // Show the Modal
            const reportModal = new bootstrap.Modal(document.getElementById('bulkReportModal'));
            reportModal.show();
        }

        $(document).ready(function () {
            var bulkSidebar = null;
            if (document.getElementById('bulkActionSidebar')) {
                bulkSidebar = new bootstrap.Offcanvas(document.getElementById('bulkActionSidebar'), {
                    backdrop: false,
                    scroll: true
                });
            }

            function updateBulkBar() {
                var count = selectedLeads.length;
                $('#selected-count').text(selectAllMatching ? totalRecordsTotal : count);
                
                if (count > 0 || selectAllMatching) {
                    if (bulkSidebar && !document.getElementById('bulkActionSidebar').classList.contains('show')) {
                        bulkSidebar.show();
                    }
                    
                    if (!selectAllMatching && count >= totalRecordsOnPage && totalRecordsTotal > count) {
                        $('#select-all-banner').show();
                        $('#all-selected-banner').hide();
                    } else if (selectAllMatching) {
                        $('#select-all-banner').hide();
                        $('#all-selected-banner').show();
                    } else {
                        $('#select-all-banner').hide();
                        $('#all-selected-banner').hide();
                    }
                } else {
                    if (bulkSidebar && document.getElementById('bulkActionSidebar').classList.contains('show')) {
                        bulkSidebar.hide();
                    }
                }
            }

            $(document).on('draw.dt', '#leads-table', function (e, settings) {
                totalRecordsOnPage = settings.aiDisplay.length;
                totalRecordsTotal = settings._iRecordsDisplay;
                updateBulkBar();
            });

            $(document).on('click', '#select-all-matching-link', function() {
                selectAllMatching = true;
                updateBulkBar();
            });

            $(document).on('change', '#checkAll', function () {
                var isChecked = $(this).prop('checked');
                $('.lead-checkbox').prop('checked', isChecked);

                $('.lead-checkbox').each(function () {
                    var id = $(this).val();
                    if (isChecked) {
                        if (!selectedLeads.includes(id)) selectedLeads.push(id);
                    } else {
                        selectedLeads = selectedLeads.filter(item => item !== id);
                    }
                });

                if (!isChecked) {
                    selectAllMatching = false;
                }

                updateBulkBar();
            });

            $(document).on('change', '.lead-checkbox', function () {
                var id = $(this).val();
                if ($(this).prop('checked')) {
                    if (!selectedLeads.includes(id)) selectedLeads.push(id);
                } else {
                    selectedLeads = selectedLeads.filter(item => item !== id);
                    $('#checkAll').prop('checked', false);
                    selectAllMatching = false;
                }
                updateBulkBar();
            });

            $(document).on('click', '#clear-selection', function () {
                selectedLeads = [];
                selectAllMatching = false;
                $('.lead-checkbox, #checkAll').prop('checked', false);
                updateBulkBar();
            });

            // Enhanced execution logic with progress bar
            async function executeBulkAction(type, value = null) {
                if (selectedLeads.length === 0 && !selectAllMatching) return;

                var message = @json(__('Are you sure you want to perform this action on selected leads?'));
                if (type === 'delete') message = @json(__('Are you sure you want to delete selected leads? This action cannot be undone.'));

                if (confirm(message)) {
                    let idsToProcess = selectedLeads;

                    if (selectAllMatching) {
                        // Fetch all IDs first
                        var filterData = {
                            action: 'get_ids',
                            _token: $('meta[name="csrf-token"]').attr('content')
                        };

                        // Add filters
                        var urlParams = new URL(window.location.href).searchParams;
                        urlParams.forEach(function(val, key) {
                            if (key.endsWith("[]")) {
                                var cleanKey = key.replace("[]", "");
                                if (!filterData[cleanKey]) filterData[cleanKey] = [];
                                filterData[cleanKey].push(val);
                            } else {
                                filterData[key] = val;
                            }
                        });

                        var pipeline = $("#default_pipeline_id").val();
                        if (pipeline) filterData.default_pipeline_id = pipeline;

                        // Show initial loader
                        $('#bulk-progress-status').text('{{ __("Fetching matching leads...") }}');
                        $('#bulk-progress-bar').css('width', '10%');
                        var progModal = $('#bulkProgressModal');
                        progModal.modal('show');

                        try {
                            const response = await $.ajax({
                                url: '{{ route("leads.bulk.action") }}',
                                type: 'POST',
                                data: filterData
                            });
                            progModal.modal('hide');
                            if (response.success) {
                                idsToProcess = response.ids;
                            } else {
                                toastrs('error', response.message, 'error');
                                return;
                            }
                        } catch (e) {
                            progModal.hide();
                            toastrs('error', 'Failed to fetch lead IDs', 'error');
                            return;
                        }
                    }

                    const processor = new BulkProcessor({
                        action: type,
                        value: value,
                        ids: idsToProcess,
                        onComplete: function(msg, report) {
                            if (report && (report.action === 'change_owner' || report.action === 'change_stage')) {
                                showBulkReportModal(report);
                            } else {
                                toastrs('success', msg || 'Bulk action completed successfully.', 'success');
                            }
                            window.LaravelDataTables["leads-table"].ajax.reload();
                            selectedLeads = [];
                            selectAllMatching = false;
                            updateBulkBar();
                        }
                    });
                    processor.start();
                }
            }

            $('#bulk-delete').on('click', function () {
                executeBulkAction('delete');
            });

            // Update export column counter on change
            $(document).on('change', '.export-col-checkbox', function() {
                var count = $('.export-col-checkbox:checked').length;
                $('#export-col-count').text(count + ' {{ __("selected") }}');
            });
            // Initialize count on modal show
            $('#exportColumnsModal').on('show.bs.modal', function() {
                var count = $('.export-col-checkbox:checked').length;
                $('#export-col-count').text(count + ' {{ __("selected") }}');
            });
            $('#export-select-all-cols').on('click', function() {
                $('.export-col-checkbox').prop('checked', true).trigger('change');
            });
            $('#export-deselect-all-cols').on('click', function() {
                $('.export-col-checkbox').prop('checked', false).trigger('change');
            });

            // Store pending export IDs and show modal
            var pendingExportIds = [];
            var pendingExportAllMatching = false;

            $('#bulk-export').on('click', async function () {
                if (selectedLeads.length === 0 && !selectAllMatching) return;
                // Store pending state
                pendingExportIds = selectedLeads.slice();
                pendingExportAllMatching = selectAllMatching;
                // Show column selection modal
                var colModal = new bootstrap.Modal(document.getElementById('exportColumnsModal'));
                colModal.show();
            });

            $(document).on('click', '#confirm-export-columns', async function () {
                // Collect selected column keys
                var selectedCols = [];
                $('.export-col-checkbox:checked').each(function() {
                    selectedCols.push($(this).val());
                });
                if (selectedCols.length === 0) {
                    toastrs('error', '{{ __("Please select at least one column to export.") }}', 'error');
                    return;
                }

                // Close column modal
                var colModalEl = bootstrap.Modal.getInstance(document.getElementById('exportColumnsModal'));
                if (colModalEl) colModalEl.hide();

                var exportId = 'export_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                let idsToProcess = pendingExportIds;

                if (pendingExportAllMatching) {
                    // Fetch all IDs first
                    var filterData = {
                        action: 'get_ids',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    };

                    // Add filters
                    var urlParams = new URL(window.location.href).searchParams;
                    urlParams.forEach(function(val, key) {
                        if (key.endsWith("[]")) {
                            var cleanKey = key.replace("[]", "");
                            if (!filterData[cleanKey]) filterData[cleanKey] = [];
                            filterData[cleanKey].push(val);
                        } else {
                            filterData[key] = val;
                        }
                    });

                    var pipeline = $("#default_pipeline_id").val();
                    if (pipeline) filterData.default_pipeline_id = pipeline;

                    $('#bulk-progress-status').text('{{ __("Fetching matching leads...") }}');
                    $('#bulk-progress-bar').css('width', '10%');
                    var progModal = $('#bulkProgressModal');
                    progModal.modal('show');

                    try {
                        const response = await $.ajax({
                            url: '{{ route("leads.bulk.action") }}',
                            type: 'POST',
                            data: filterData
                        });
                        progModal.modal('hide');
                        if (response.success) {
                            idsToProcess = response.ids;
                        } else {
                            toastrs('error', response.message, 'error');
                            return;
                        }
                    } catch (e) {
                        progModal.modal('hide');
                        toastrs('error', 'Failed to fetch lead IDs', 'error');
                        return;
                    }
                }

                const processor = new BulkProcessor({
                    action: 'export',
                    ids: idsToProcess,
                    extraData: { export_id: exportId, export_columns: selectedCols },
                    onComplete: function() {
                        toastrs('success', '{{ __("Export completed successfully.") }}', 'success');
                        window.location.href = '{{ route("leads.bulk.export.download") }}?export_id=' + exportId;
                        selectedLeads = [];
                        selectAllMatching = false;
                        pendingExportIds = [];
                        pendingExportAllMatching = false;
                        updateBulkBar();
                    }
                });
                processor.start();
            });

            $('#bulk-change-stage').on('click', function () {
                var stages = @json($stages);
                var options = '';
                $.each(stages, function (id, name) {
                    options += `<option value="${id}">${name}</option>`;
                });

                var html = `
                        <div class="modal-body p-3" style="min-height: 200px;">
                            <div class="form-group mb-3 text-start">
                                <label class="form-label text-dark fw-bold">@json(__('Select Target Stage'))</label>
                                <select class="form-control" id="target_stage">
                                    ${options}
                                </select>
                            </div>
                            <div class="text-end mt-4 border-top pt-3">
                                <button class="btn btn-secondary me-2 px-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button class="btn btn-primary px-4" id="confirm-stage-change">{{ __('Apply Changes') }}</button>
                            </div>
                        </div>
                    `;
                $('#commonModal .modal-title').html('{{ __("Bulk Change Lead Stage") }}');
                $('#commonModal .body').html(html);
                $('#commonModal').modal('show');

                setTimeout(function () {
                    new Choices('#target_stage', { searchEnabled: true, shouldSort: false });
                }, 100);
            });

            $(document).on('click', '#confirm-stage-change', function () {
                var stageId = $('#target_stage').val();
                if (!stageId) {
                    toastrs('error', 'Please select a stage', 'error');
                    return;
                }
                $('#commonModal').modal('hide');
                executeBulkAction('change_stage', stageId);
            });

            $('#bulk-change-owner').on('click', function () {
                var users = @json($users);
                var options = '';
                $.each(users, function (id, name) {
                    options += `<option value="${id}">${name}</option>`;
                });

                var html = `
                        <div class="modal-body p-3" style="min-height: 200px;">
                            <div class="form-group mb-3 text-start">
                                <label class="form-label text-dark fw-bold">@json(__('Select Responsible Person'))</label>
                                <select class="form-control" id="target_owner">
                                    ${options}
                                </select>
                            </div>
                            <div class="text-end mt-4 border-top pt-3">
                                <button class="btn btn-secondary me-2 px-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                <button class="btn btn-primary px-4" id="confirm-owner-change">{{ __('Reassign Leads') }}</button>
                            </div>
                        </div>
                    `;
                $('#commonModal .modal-title').html('{{ __("Bulk Change Responsible Person") }}');
                $('#commonModal .body').html(html);
                $('#commonModal').modal('show');

                setTimeout(function () {
                    new Choices('#target_owner', { searchEnabled: true, shouldSort: false });
                }, 100);
            });

            $(document).on('click', '#confirm-owner-change', function () {
                var userId = $('#target_owner').val();
                if (!userId) {
                    toastrs('error', 'Please select a user', 'error');
                    return;
                }
                $('#commonModal').modal('hide');
                executeBulkAction('change_owner', userId);
            });

            // Handling Task/Reminder specifically as it has its own store endpoint
            $('#bulk-task-reminder').on('click', async function () {
                if (selectedLeads.length === 0 && !selectAllMatching) return;

                let idsParam = selectAllMatching ? 'all' : selectedLeads.join(',');

                $.ajax({
                    url: '{{ route("leads.bulk.task.reminder.create") }}',
                    type: 'POST',
                    data: {
                        ids: selectAllMatching ? 'all' : selectedLeads,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.error) {
                            toastrs('error', response.error, 'error');
                        } else {
                            $('#commonModal .modal-title').html('{{ __("Bulk Task & Reminder Creation") }}');
                            $('#commonModal .body').html(response);
                            $('#commonModal').modal('show');

                            // Intercept the form submission for progress tracking
                            $(document).on('submit', '#bulk-task-reminder-form', async function(e) {
                                e.preventDefault();
                                var form = $(this);
                                var formData = form.serializeArray();
                                var dataObj = {};
                                formData.forEach(item => dataObj[item.name] = item.value);

                                $('#commonModal').modal('hide');

                                let idsToProcess = selectedLeads;
                                if (selectAllMatching) {
                                    // Fetch all IDs first with filters
                                    var filterData = {
                                        action: 'get_ids',
                                        _token: $('meta[name="csrf-token"]').attr('content')
                                    };

                                    // Add filters
                                    var urlParams = new URL(window.location.href).searchParams;
                                    urlParams.forEach(function(val, key) {
                                        if (key.endsWith("[]")) {
                                            var cleanKey = key.replace("[]", "");
                                            if (!filterData[cleanKey]) filterData[cleanKey] = [];
                                            filterData[cleanKey].push(val);
                                        } else {
                                            filterData[key] = val;
                                        }
                                    });

                                    var pipeline = $("#default_pipeline_id").val();
                                    if (pipeline) filterData.default_pipeline_id = pipeline;

                                    const resp = await $.ajax({
                                        url: '{{ route("leads.bulk.action") }}',
                                        type: 'POST',
                                        data: filterData
                                    });
                                    idsToProcess = resp.ids;
                                }

                                const processor = new BulkProcessor({
                                    url: '{{ route("leads.bulk.task.reminder.store") }}',
                                    action: 'create',
                                    ids: idsToProcess,
                                    extraData: dataObj,
                                    onComplete: function(msg) {
                                        toastrs('success', msg || 'Tasks/Reminders created successfully.', 'success');
                                        window.LaravelDataTables["leads-table"].draw();
                                        selectedLeads = [];
                                        selectAllMatching = false;
                                        updateBulkBar();
                                    }
                                });
                                processor.start();
                            });
                        }
                    }
                });
            });
        });

        $(document).on('click', '.reveal-link', function (e) {
            e.preventDefault();
            var $this = $(this);
            var url = $this.data('url');
            var target = $this.data('target');

            $.ajax({
                url: url,
                type: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    if (response.is_success) {
                        $(target).text(response.value).removeClass('masked-value');
                        $this.remove();
                    } else {
                        toastrs('error', response.error || 'Failed to reveal field', 'error');
                    }
                }
            });
        });

        $(document).on('change', '#change-pipeline select[name=default_pipeline_id]', function () {
            $('#change-pipeline').submit();
        });

        function initColumnSelector() {
            var table = window.LaravelDataTables["leads-table"];
            var columns = table.columns().settings()[0].aoColumns;
            var listHtml = '';
            columns.forEach(function (col, index) {
                if (col.name === 'batch' || col.name === 'DT_RowIndex' || col.name === 'action' || !col.sTitle) return;
                var isVisible = table.column(index).visible();
                listHtml += `
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="col_toggle_${index}" data-column="${index}" ${isVisible ? 'checked' : ''}>
                            <label class="form-check-label" for="col_toggle_${index}">${col.sTitle}</label>
                        </div>
                    `;
            });
            $('#column-selector-list').html(listHtml);
        }

        $(document).on('change', '.column-toggle', function () {
            var table = window.LaravelDataTables["leads-table"];
            table.column($(this).data('column')).visible($(this).prop('checked'));
        });

        window.exportLeadsDataTable = function(actionType) {
            var table = window.LaravelDataTables["leads-table"];
            var visibleCols = [];
            table.columns().every(function(idx) {
                if (this.visible()) {
                    var colDef = table.column(idx).settings()[0].aoColumns[idx];
                    if (colDef.name) {
                        visibleCols.push(colDef.name);
                    }
                }
            });
            
            var url = new URL(window.location.href);
            url.searchParams.set("action", actionType);
            
            url.searchParams.delete("visible_columns[]");
            visibleCols.forEach(function(col) {
                url.searchParams.append("visible_columns[]", col);
            });
            
            var pipeline = $("select[name=default_pipeline_id]").val();
            if (pipeline) url.searchParams.set("default_pipeline_id", pipeline);
            
            // Redirect to download
            window.location.href = url.toString();
        };

        $('#leads-table').on('init.dt', function () { initColumnSelector(); });
        if ($.fn.DataTable.isDataTable('#leads-table')) { initColumnSelector(); }

        // Configurable Statistics Card Script
        const systemOptions = [
            { value: 'total_leads', label: "{{ __('Total Leads') }}" },
            { value: 'converted_leads', label: "{{ __('Converted Leads') }}" },
            { value: 'pending_leads', label: "{{ __('Pending Leads') }}" },
            { value: 'conversion_rate', label: "{{ __('Conversion Rate') }}" }
        ];

        const stageOptions = [
            @foreach($leadStages as $stage)
                { value: "{{ $stage->id }}", label: "{{ addslashes($stage->name) }}" },
            @endforeach
        ];

        const customFieldOptions = [
            @foreach($leadCustomFields as $cf)
                { value: "{{ $cf->id }}", label: "{{ addslashes($cf->name) }}" },
            @endforeach
        ];

        function updateValueDropdown(cardIndex, selectedValue = null) {
            const typeSelect = document.getElementById(`card_${cardIndex + 1}_type`);
            const valueSelect = document.getElementById(`card_${cardIndex + 1}_value`);
            if (!typeSelect || !valueSelect) return;
            
            const type = typeSelect.value;
            valueSelect.innerHTML = '';
            
            let options = [];
            if (type === 'system') {
                options = systemOptions;
            } else if (type === 'stage') {
                options = stageOptions;
            } else if (type === 'custom_field') {
                options = customFieldOptions;
            }
            
            options.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.label;
                if (selectedValue && String(opt.value) === String(selectedValue)) {
                    option.selected = true;
                }
                valueSelect.appendChild(option);
            });
        }

        // Initialize Card Values dropdowns
        const initialConfig = @json($cardsConfig);
        for (let i = 0; i < 4; i++) {
            const typeSelect = document.getElementById(`card_${i + 1}_type`);
            if (typeSelect) {
                updateValueDropdown(i, initialConfig[i] ? initialConfig[i].value : null);
                typeSelect.addEventListener('change', function() {
                    updateValueDropdown(i);
                });
            }
        }

        // Save Stats Configuration AJAX submit
        const statsForm = document.getElementById('statsConfigForm');
        if (statsForm) {
            statsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const saveBtn = document.getElementById('saveConfigBtn');
                const originalText = saveBtn.innerHTML;
                
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>{{ __('Saving...') }}';

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastrs('Success', data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        toastrs('Error', data.message || 'Something went wrong', 'error');
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastrs('Error', 'An error occurred while saving configuration', 'error');
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalText;
                });
            });
        }
    </script>
@endpush