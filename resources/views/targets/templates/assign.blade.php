@extends('layouts.main')

@section('page-title')
    {{ __('Assign Target') }}
@endsection

@section('page-breadcrumb')
    {{ __('Targets') }}
@endsection

@section('page-action')
    <a href="{{ route('targets.index') }}" class="btn btn-sm btn-light border">
        <i class="ti ti-arrow-left"></i> {{ __('Back to Dashboard') }}
    </a>
@endsection

@push('css')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        .template-assign-container {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        .template-detail-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px -5px rgba(50, 50, 93, 0.05), 0 5px 15px -5px rgba(0, 0, 0, 0.03);
            background: #fff;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .template-header-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 28px 24px;
        }
        .template-header-gradient-manual {
            background: linear-gradient(135deg, #2dce89 0%, #2dcecc 100%);
            color: #fff;
            padding: 28px 24px;
        }
        
        /* Interactive Choice Cards */
        .choice-card {
            border: 2px solid #e9ecef;
            border-radius: 16px;
            padding: 24px 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            background: #fff;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.02);
        }
        .choice-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(50, 50, 93, 0.06), 0 4px 6px rgba(0, 0, 0, 0.02);
        }
        .choice-card.active {
            border-color: #5e72e4;
            background: linear-gradient(145deg, #ffffff 0%, rgba(94, 114, 228, 0.02) 100%);
            box-shadow: 0 10px 25px rgba(94, 114, 228, 0.1), 0 3px 6px rgba(94, 114, 228, 0.03);
        }
        .choice-card .check-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: linear-gradient(135deg, #5e72e4 0%, #825ee4 100%);
            color: #fff;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            box-shadow: 0 4px 8px rgba(94, 114, 228, 0.3);
            border: 2px solid #fff;
        }
        .choice-card.active .check-badge {
            display: flex;
        }
        .choice-card i {
            font-size: 32px;
            margin-bottom: 12px;
            display: block;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #8898aa 0%, #adb5bd 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .choice-card.active i {
            background: linear-gradient(135deg, #5e72e4 0%, #825ee4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transform: scale(1.15);
        }
        .choice-card span {
            font-size: 0.95rem;
            font-weight: 700;
            color: #4a5568;
            display: block;
            transition: color 0.3s ease;
        }
        .choice-card.active span {
            color: #5e72e4;
        }

        /* Form Labels and Inputs styling */
        .template-assign-container .form-label {
            font-size: 0.9rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .template-assign-container .form-control {
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            min-height: 48px !important;
            font-size: 0.95rem !important;
            color: #4a5568 !important;
            transition: all 0.25s ease-in-out !important;
            box-shadow: 0 2px 4px rgba(50, 50, 93, 0.01) !important;
            background-color: #fff !important;
        }

        .template-assign-container .form-control:focus {
            border-color: #5e72e4 !important;
            box-shadow: 0 0 0 4px rgba(94, 114, 228, 0.12) !important;
            outline: none !important;
        }

        /* Select2 Overrides */
        .template-assign-container .select2-container--default .select2-selection--multiple,
        .template-assign-container .select2-container--default .select2-selection--single {
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            padding: 6px 12px !important;
            min-height: 48px !important;
            background-color: #fff !important;
            transition: all 0.25s ease-in-out !important;
            box-shadow: 0 2px 4px rgba(50, 50, 93, 0.01) !important;
            display: flex;
            align-items: center;
        }
        
        .template-assign-container .select2-container--default.select2-container--focus .select2-selection--multiple,
        .template-assign-container .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #5e72e4 !important;
            box-shadow: 0 0 0 4px rgba(94, 114, 228, 0.12) !important;
            outline: none !important;
        }

        .template-assign-container .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: linear-gradient(135deg, rgba(94, 114, 228, 0.08) 0%, rgba(130, 94, 228, 0.08) 100%) !important;
            border: 1px solid rgba(94, 114, 228, 0.15) !important;
            color: #5e72e4 !important;
            border-radius: 8px !important;
            padding: 4px 12px !important;
            font-weight: 600 !important;
            font-size: 0.85rem !important;
            margin-top: 4px !important;
            margin-bottom: 4px !important;
            display: inline-flex;
            align-items: center;
        }

        .template-assign-container .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #5e72e4 !important;
            margin-right: 8px !important;
            font-weight: bold !important;
        }
        
        .template-assign-container .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #f5365c !important;
            background: transparent !important;
        }

        .template-assign-container .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #4a5568 !important;
            font-size: 0.95rem !important;
            padding-left: 0 !important;
        }

        .template-assign-container .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
        }

        /* Buttons */
        .template-assign-container .btn-primary {
            background: linear-gradient(135deg, #5e72e4 0%, #825ee4 100%) !important;
            border: none !important;
            padding: 12px 28px !important;
            border-radius: 12px !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 15px rgba(94, 114, 228, 0.25) !important;
            transition: all 0.25s ease-in-out !important;
            color: #fff !important;
        }
        .template-assign-container .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(94, 114, 228, 0.35) !important;
        }

        .template-assign-container .btn-light {
            background-color: #fff !important;
            border: 1px solid #e2e8f0 !important;
            color: #4a5568 !important;
            padding: 12px 28px !important;
            border-radius: 12px !important;
            font-weight: 600 !important;
            transition: all 0.25s ease-in-out !important;
        }
        .template-assign-container .btn-light:hover {
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
            transform: translateY(-1px);
        }

        /* Custom Checkbox */
        .template-assign-container .form-check-input {
            border: 2px solid #cbd5e1 !important;
            border-radius: 6px !important;
            width: 20px !important;
            height: 20px !important;
            margin-top: 2px !important;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .template-assign-container .form-check-input:checked {
            background-color: #5e72e4 !important;
            border-color: #5e72e4 !important;
            box-shadow: 0 0 0 4px rgba(94, 114, 228, 0.12) !important;
        }
        .template-assign-container .form-check-label {
            cursor: pointer;
            padding-left: 6px;
            font-weight: 600;
            color: #4a5568;
        }

        /* Avatar styling */
        .avatar-initials {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #5e72e4;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            margin-right: 12px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.05);
        }

        /* Table separate styling for floating rows look */
        #dynamic_quantities_section {
            margin-top: 24px;
            transition: all 0.3s ease;
        }
        #dynamic_quantities_section .table {
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        #dynamic_quantities_section tr {
            background: #f8fafc;
            border-radius: 12px;
            transition: all 0.25s ease;
        }
        #dynamic_quantities_section tr:hover {
            background: #f1f5f9;
            transform: scale(1.002);
        }
        #dynamic_quantities_section td, #dynamic_quantities_section th {
            border: none !important;
            padding: 14px 20px !important;
            vertical-align: middle;
        }
        #dynamic_quantities_section td:first-child {
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }
        #dynamic_quantities_section td:last-child {
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        #dynamic_quantities_section thead tr {
            background: transparent !important;
        }
        #dynamic_quantities_section thead th {
            font-weight: 700 !important;
            color: #718096 !important;
            text-transform: uppercase;
            font-size: 0.75rem !important;
            letter-spacing: 0.05em;
        }
    </style>
@endpush

@section('content')
<div class="row template-assign-container">
    <!-- Left Column: Template Summary -->
    <div class="col-xl-4 col-lg-5 mb-4">
        <div class="card template-detail-card">
            <div class="{{ in_array($template->target_type, ['lead_stage', 'account', 'ftd', 'revenue']) ? 'template-header-gradient' : 'template-header-gradient-manual' }}">
                <span class="badge bg-white-off text-xxs px-2 py-1 mb-2">{{ in_array($template->target_type, ['lead_stage', 'account', 'ftd', 'revenue']) ? __('Automated Tracking') : __('Manual Tracking') }}</span>
                <h4 class="text-white mb-0 font-weight-bold">{{ $template->name }}</h4>
            </div>
            <div class="card-body p-4">
                <h6 class="font-weight-bold text-dark mb-3">{{ __('Tracking Settings') }}</h6>
                
                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted text-sm">{{ __('Type') }}</span>
                        <span class="text-dark font-weight-bold text-sm">
                            @if($template->target_type == 'lead_stage')
                                {{ __('Lead Stage Transition') }}
                            @elseif($template->target_type == 'account')
                                {{ __('Account Opening') }}
                            @elseif($template->target_type == 'ftd')
                                {{ __('FTD Count') }}
                            @elseif($template->target_type == 'revenue')
                                {{ __('Revenue Sum') }}
                            @else
                                {{ __('Self Reported') }}
                            @endif
                        </span>
                    </div>

                    @if($template->target_type == 'lead_stage')
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted text-sm">{{ __('Pipeline') }}</span>
                            <span class="text-dark font-weight-bold text-sm">{{ $template->pipeline ? $template->pipeline->name : __('N/A') }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted text-sm">{{ __('Lead Stage') }}</span>
                            <span class="text-primary font-weight-bold text-sm">{{ $template->stage ? $template->stage->name : __('N/A') }}</span>
                        </div>
                    @elseif(in_array($template->target_type, ['account', 'ftd', 'revenue']))
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted text-sm">{{ __('Date Scoping Field') }}</span>
                            <span class="text-dark font-weight-bold text-sm">
                                {{ $template->custom_date_field === 'created_at' ? __('Lead Creation Date') : ($template->custom_date_field ?? __('Lead Creation Date')) }}
                            </span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted text-sm">{{ __('Workspace') }}</span>
                        <span class="text-dark font-weight-bold text-sm">#{{ getActiveWorkSpace() }}</span>
                    </div>
                </div>

                <div class="alert alert-warning border-0 mb-0 rounded-4">
                    <div class="d-flex align-items-start gap-2">
                        <i class="ti ti-info-circle fs-4 text-warning"></i>
                        <div class="text-xs">
                            <strong>{{ __('Quota Assignment Note:') }}</strong>
                            <p class="mb-0 mt-1 text-muted">{{ __('Assignments created from this template will track performance independently based on the target quantity and date ranges you define.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Assignment Form -->
    <div class="col-xl-8 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header border-0 bg-transparent pt-4 pb-0">
                <h5 class="mb-0 font-weight-bold text-dark"><i class="ti ti-user-plus me-1 text-primary"></i>{{ __('Quota Assignment Form') }}</h5>
            </div>
            
            {{ Form::open(['route' => ['targets.templates.assign.store', $template->id], 'method' => 'post', 'class' => 'p-4']) }}
            <div class="row">
                
                <!-- Styled Choice Cards for Assignment Type -->
                <div class="col-md-12 form-group mb-4">
                    {{ Form::label('assignment_type', __('Assign Target To'), ['class' => 'form-label fw-bold']) }}
                    <div class="row g-3 mt-1">
                        @php
                            $showCompany = (auth()->user()->type == 'company' || auth()->user()->type == 'super admin');
                            $colClass = 'col-md-4';
                        @endphp
                        
                        @if($showCompany)
                            <div class="{{ $colClass }}">
                                <div class="choice-card" onclick="selectChoice('company')">
                                    <span class="check-badge"><i class="ti ti-check"></i></span>
                                    <i class="ti ti-building-community"></i>
                                    <span>{{ __('Company / Workspace') }}</span>
                                    <input type="radio" name="assignment_type" value="company" class="d-none" id="type_company">
                                </div>
                            </div>
                            <div class="{{ $colClass }}">
                                <div class="choice-card active" onclick="selectChoice('department')">
                                    <span class="check-badge"><i class="ti ti-check"></i></span>
                                    <i class="ti ti-building"></i>
                                    <span>{{ __('Department') }}</span>
                                    <input type="radio" name="assignment_type" value="department" checked class="d-none" id="type_department">
                                </div>
                            </div>
                            <div class="{{ $colClass }}">
                                <div class="choice-card" onclick="selectChoice('team')">
                                    <span class="check-badge"><i class="ti ti-check"></i></span>
                                    <i class="ti ti-users"></i>
                                    <span>{{ __('Team') }}</span>
                                    <input type="radio" name="assignment_type" value="team" class="d-none" id="type_team">
                                </div>
                            </div>
                        @else
                            <div class="{{ $colClass }}">
                                <div class="choice-card active" onclick="selectChoice('individual')">
                                    <span class="check-badge"><i class="ti ti-check"></i></span>
                                    <i class="ti ti-user"></i>
                                    <span>{{ __('Individual') }}</span>
                                    <input type="radio" name="assignment_type" value="individual" checked class="d-none" id="type_individual">
                                </div>
                            </div>
                            <div class="{{ $colClass }}">
                                <div class="choice-card" onclick="selectChoice('department')">
                                    <span class="check-badge"><i class="ti ti-check"></i></span>
                                    <i class="ti ti-building"></i>
                                    <span>{{ __('Department') }}</span>
                                    <input type="radio" name="assignment_type" value="department" class="d-none" id="type_department">
                                </div>
                            </div>
                            <div class="{{ $colClass }}">
                                <div class="choice-card" onclick="selectChoice('team')">
                                    <span class="check-badge"><i class="ti ti-check"></i></span>
                                    <i class="ti ti-users"></i>
                                    <span>{{ __('Team') }}</span>
                                    <input type="radio" name="assignment_type" value="team" class="d-none" id="type_team">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Dropdown selectors -->
                @if($showCompany)
                    <div class="col-md-12 form-group d-none" id="assign_template_company_field">
                        {{ Form::label('workspace_id', __('Select Company(s) / Workspace(s)'), ['class' => 'form-label fw-bold']) }}
                        {{ Form::select('workspace_id[]', $workspaces, null, ['class' => 'form-control choices', 'multiple' => 'multiple', 'id' => 'assign_template_workspace_id', 'searchEnabled' => 'true', 'data-placeholder' => __('Search and select companies/workspaces')]) }}
                    </div>
                @endif

                <div class="col-md-12 form-group {{ $showCompany ? 'd-none' : '' }}" id="assign_template_individual_field">
                    {{ Form::label('assigned_to', __('Select Employee(s)'), ['class' => 'form-label fw-bold']) }}
                    {{ Form::select('assigned_to[]', $users, null, ['class' => 'form-control choices', 'multiple' => 'multiple', 'id' => 'assign_template_assigned_to', 'searchEnabled' => 'true', 'data-placeholder' => __('Search and select employees')]) }}
                </div>

                <div class="col-md-12 form-group {{ $showCompany ? '' : 'd-none' }}" id="assign_template_department_field">
                    {{ Form::label('department_id', __('Select Department(s)'), ['class' => 'form-label fw-bold']) }}
                    {{ Form::select('department_id[]', $departments, null, ['class' => 'form-control choices', 'multiple' => 'multiple', 'id' => 'assign_template_department_id', 'searchEnabled' => 'true', 'data-placeholder' => __('Search and select departments')]) }}
                </div>

                <div class="col-md-12 form-group d-none" id="assign_template_team_field">
                    {{ Form::label('team_id', __('Select Team(s)'), ['class' => 'form-label fw-bold']) }}
                    {{ Form::select('team_id[]', $teams, null, ['class' => 'form-control choices', 'multiple' => 'multiple', 'id' => 'assign_template_team_id', 'searchEnabled' => 'true', 'data-placeholder' => __('Search and select teams')]) }}
                </div>



                <!-- General Quota -->
                <div class="col-md-12 form-group">
                    {{ Form::label('target_value', __('Default Target Quantity'), ['class' => 'form-label fw-bold']) }}
                    {{ Form::number('target_value', 1, ['class' => 'form-control', 'required' => 'required', 'min' => '1', 'id' => 'assign_template_default_val']) }}
                    <small class="text-muted">{{ __('This quota applies by default. Configure individual custom quotas below if needed.') }}</small>
                </div>

                <!-- General Incentive -->
                <div class="col-md-12 form-group mt-3">
                    {{ Form::label('incentive', __('Default Target Incentive'), ['class' => 'form-label fw-bold']) }}
                    {{ Form::number('incentive', 0.00, ['class' => 'form-control', 'required' => 'required', 'min' => '0', 'step' => '0.01', 'id' => 'assign_template_default_incentive']) }}
                    <small class="text-muted">{{ __('This incentive applies by default. Configure individual custom incentives below if needed.') }}</small>
                </div>

                <!-- Dynamic separate quantities config table -->
                <div class="col-md-12 form-group mt-3" id="dynamic_quantities_section" style="display: none;">
                    {{ Form::label('quantities_table', __('Configure Separate Quantities per Assignee'), ['class' => 'form-label fw-bold text-primary']) }}
                    <div class="table-responsive border rounded-4 overflow-hidden">
                        <table class="table align-items-center mb-0">
                            <thead class="bg-light">
                                <tr class="text-xs text-muted uppercase">
                                    <th>{{ __('Assignee') }}</th>
                                    <th width="200px" class="text-end">{{ __('Custom Target Quota') }}</th>
                                    <th width="200px" class="text-end">{{ __('Custom Target Incentive') }}</th>
                                </tr>
                            </thead>
                            <tbody id="dynamic_quantities_list_table">
                                <!-- Loaded dynamically via JS -->
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted mt-2 d-block"><i class="ti ti-info-circle me-1"></i>{{ __('Leave custom input blank to fallback to the default target quantity.') }}</small>
                </div>



                <div class="col-md-12 form-group">
                    <div class="form-check">
                        {{ Form::checkbox('can_edit', 1, false, ['class' => 'form-check-input', 'id' => 'assign_template_can_edit']) }}
                        {{ Form::label('assign_template_can_edit', __('Allow Responsible Person to Edit / Update this target'), ['class' => 'form-check-label']) }}
                    </div>
                </div>

                <div class="col-md-12 mt-4 text-end">
                    <a href="{{ route('targets.index') }}" class="btn btn-light border me-2">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary shadow-sm">{{ __('Assign Quota Targets') }}</button>
                </div>

            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function selectChoice(type) {
        // Toggle active visual class on choices
        $('.choice-card').removeClass('active');
        if (type === 'company') {
            $('.choice-card:has(#type_company)').addClass('active');
            $('#type_company').prop('checked', true);
            
            $('#assign_template_company_field').removeClass('d-none');
            $('#assign_template_individual_field').addClass('d-none');
            $('#assign_template_department_field').addClass('d-none');
            $('#assign_template_team_field').addClass('d-none');
        } else if (type === 'individual') {
            $('.choice-card:has(#type_individual)').addClass('active');
            $('#type_individual').prop('checked', true);
            
            $('#assign_template_company_field').addClass('d-none');
            $('#assign_template_individual_field').removeClass('d-none');
            $('#assign_template_department_field').addClass('d-none');
            $('#assign_template_team_field').addClass('d-none');
        } else if (type === 'department') {
            $('.choice-card:has(#type_department)').addClass('active');
            $('#type_department').prop('checked', true);
            
            $('#assign_template_company_field').addClass('d-none');
            $('#assign_template_individual_field').addClass('d-none');
            $('#assign_template_department_field').removeClass('d-none');
            $('#assign_template_team_field').addClass('d-none');
        } else if (type === 'team') {
            $('.choice-card:has(#type_team)').addClass('active');
            $('#type_team').prop('checked', true);
            
            $('#assign_template_company_field').addClass('d-none');
            $('#assign_template_individual_field').addClass('d-none');
            $('#assign_template_department_field').addClass('d-none');
            $('#assign_template_team_field').removeClass('d-none');
        }

        updateDynamicQuantitiesList();
    }

    function getInitials(name) {
        return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    }

    function getRandomColorClass(id) {
        const bgClasses = ['bg-primary', 'bg-success', 'bg-warning', 'bg-info', 'bg-danger', 'bg-dark'];
        return bgClasses[id % bgClasses.length];
    }

    function updateDynamicQuantitiesList() {
        const type = $('input[name="assignment_type"]:checked').val();
        let selectElement = null;
        let iconMarkup = '<i class="ti ti-user text-muted"></i>';
        
        if (type === 'individual') selectElement = $('#assign_template_assigned_to');
        else if (type === 'department') {
            selectElement = $('#assign_template_department_id');
            iconMarkup = '<i class="ti ti-building text-info"></i>';
        }
        else if (type === 'team') {
            selectElement = $('#assign_template_team_id');
            iconMarkup = '<i class="ti ti-users text-warning"></i>';
        }
        else if (type === 'company') {
            selectElement = $('#assign_template_workspace_id');
            iconMarkup = '<i class="ti ti-building-community text-success"></i>';
        }

        const container = $('#dynamic_quantities_list_table');
        container.empty();

        if (!selectElement) return;

        const selectedOptions = selectElement.find('option:selected');
        if (selectedOptions.length > 0) {
            $('#dynamic_quantities_section').show();
            const defaultVal = $('#assign_template_default_val').val() || 1;
            const defaultIncentive = $('#assign_template_default_incentive').val() || "0.00";
            selectedOptions.each(function() {
                const id = $(this).val();
                const name = $(this).text();
                
                // Initials circle for individuals, icon for department/teams/companies
                let avatarHtml = `<div class="avatar-initials ${getRandomColorClass(id)}">${getInitials(name)}</div>`;
                if (type !== 'individual') {
                    avatarHtml = `<div class="avatar-initials bg-light border me-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 50%;">${iconMarkup}</div>`;
                }

                const row = `
                    <tr class="align-middle">
                        <td>
                            <div class="d-flex align-items-center">
                                ${avatarHtml}
                                <span class="text-dark font-weight-bold text-sm">${name}</span>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-block" style="width: 150px;">
                                <input type="number" name="target_values[${id}]" class="form-control form-control-sm text-end" placeholder="${defaultVal}" min="1">
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-block" style="width: 150px;">
                                <input type="number" name="incentives[${id}]" class="form-control form-control-sm text-end" placeholder="${defaultIncentive}" min="0" step="0.01">
                            </div>
                        </td>
                    </tr>
                `;
                container.append(row);
            });
        } else {
            $('#dynamic_quantities_section').hide();
        }
    }

    $(document).on('change', '#assign_template_assigned_to, #assign_template_department_id, #assign_template_team_id, #assign_template_workspace_id', function() {
        updateDynamicQuantitiesList();
    });

    $(document).on('input change', '#assign_template_default_val', function() {
        const val = $(this).val() || 1;
        $('#dynamic_quantities_list_table input[name^="target_values"]').each(function() {
            $(this).attr('placeholder', val);
        });
    });

    $(document).on('input change', '#assign_template_default_incentive', function() {
        const val = $(this).val() || "0.00";
        $('#dynamic_quantities_list_table input[name^="incentives"]').each(function() {
            $(this).attr('placeholder', val);
        });
    });


</script>
@endpush
