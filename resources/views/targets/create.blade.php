{{ Form::open(['url' => 'targets', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        @if(isset($parentTarget) && $parentTarget)
            <div class="col-md-12 mb-2">
                @php
                    $parentAssignedTo = '';
                    if ($parentTarget->department) $parentAssignedTo = $parentTarget->department->name . ' (' . __('Dept') . ')';
                    elseif ($parentTarget->team) $parentAssignedTo = $parentTarget->team->name . ' (' . __('Team') . ')';
                    elseif ($parentTarget->assignedToUser) $parentAssignedTo = $parentTarget->assignedToUser->name;
                @endphp

                {{-- Context-aware guidance banner --}}
                @if(isset($assignmentContext) && $assignmentContext == 'teams_only')
                    <div class="alert border-0 mb-3" style="background: linear-gradient(135deg, rgba(13,202,240,0.08) 0%, rgba(13,202,240,0.04) 100%); border-left: 4px solid #0dcaf0 !important; border-radius: 10px;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="ti ti-sitemap text-info fs-5 mt-1"></i>
                            <div>
                                <strong class="text-dark d-block" style="font-size: 0.85rem;">{{ __('Department Target — Divide into Teams') }}</strong>
                                <span class="text-muted" style="font-size: 0.8rem;">
                                    {{ __('Parent target') }}: <strong>{{ $parentTarget->target_name }}</strong> ({{ __('Value') }}: <strong>{{ $parentTarget->target_value }}</strong>)<br>
                                    {{ __('Assign this sub-target to a team under') }} <strong>{{ $contextLabel ?? $parentAssignedTo }}</strong>.
                                </span>
                            </div>
                        </div>
                    </div>
                @elseif(isset($assignmentContext) && $assignmentContext == 'members_only')
                    <div class="alert border-0 mb-3" style="background: linear-gradient(135deg, rgba(255,159,67,0.08) 0%, rgba(255,159,67,0.04) 100%); border-left: 4px solid #ff9f43 !important; border-radius: 10px;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="ti ti-user-plus text-warning fs-5 mt-1"></i>
                            <div>
                                <strong class="text-dark d-block" style="font-size: 0.85rem;">{{ __('Team Target — Assign to Members') }}</strong>
                                <span class="text-muted" style="font-size: 0.8rem;">
                                    {{ __('Parent target') }}: <strong>{{ $parentTarget->target_name }}</strong> ({{ __('Value') }}: <strong>{{ $parentTarget->target_value }}</strong>)<br>
                                    {{ __('Assign this sub-target to individual members of') }} <strong>{{ $contextLabel ?? $parentAssignedTo }}</strong>.
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info border-0 mb-3" style="border-radius: 10px;">
                        <strong>{{ __('Master Target') }}:</strong> {{ $parentTarget->target_name }}<br>
                        <strong>{{ __('Value') }}:</strong> {{ $parentTarget->target_value }}
                    </div>
                @endif
                <input type="hidden" name="parent_id" value="{{ $parentTarget->id }}">
            </div>
        @endif

        @if(isset($parentTarget) && $parentTarget)
            <input type="hidden" name="target_name" value="{{ $parentTarget->target_name }}">
        @else
            <div class="col-md-12 form-group">
                {{ Form::label('target_name', __('Target Objective / Name'), ['class' => 'col-form-label']) }}
                {{ Form::text('target_name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('E.g. Convert 50 Leads')]) }}
            </div>
        @endif

        {{-- ── Assignment Type Section ─────────────────────────────── --}}
        @php
            $ctx = $assignmentContext ?? 'free';
        @endphp

        @if($ctx == 'teams_only')
            {{-- Department → Teams: hide individual & dept options, show only teams --}}
            <input type="hidden" name="assignment_type" value="team">
            <div class="col-md-12 form-group">
                <label class="col-form-label fw-bold">{{ __('Divide Target among Teams') }}</label>
                <div class="d-flex align-items-center gap-1 mb-2">
                    <i class="ti ti-users text-warning" style="font-size:14px;"></i>
                    <small class="text-muted">{{ __('Teams under') }} <strong>{{ $contextLabel }}</strong></small>
                </div>
                
                {{-- Live Allocations Tracker --}}
                @php
                    $assignedSum = \App\Models\Target::where('parent_id', $parentTarget->id)->sum('target_value');
                    $remainingVal = max(0, $parentTarget->target_value - $assignedSum);
                @endphp
                <div class="p-3 rounded-4 mb-3" style="background: rgba(var(--primary-theme-color-rgb), 0.03); border: 1px solid rgba(var(--primary-theme-color-rgb), 0.1);">
                    <div class="row text-center g-2" style="font-size: 0.82rem;">
                        <div class="col-4 border-end">
                            <span class="text-muted d-block text-xxs uppercase">{{ __('Parent Target') }}</span>
                            <span class="fw-bold text-dark fs-6">{{ $parentTarget->target_value }}</span>
                        </div>
                        <div class="col-4 border-end">
                            <span class="text-muted d-block text-xxs uppercase">{{ __('Already Divided') }}</span>
                            <span class="fw-bold text-primary fs-6">{{ $assignedSum }}</span>
                        </div>
                        <div class="col-4">
                            <span class="text-muted d-block text-xxs uppercase">{{ __('Remaining Limit') }}</span>
                            <span class="fw-bold text-success fs-6" id="remaining-limit-val" data-limit="{{ $remainingVal }}">{{ $remainingVal }}</span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive border rounded-3 overflow-hidden bg-white shadow-xs">
                    <table class="table table-hover align-items-center mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th style="padding: 10px 16px;">{{ __('Team Name') }}</th>
                                <th style="width: 140px; padding: 10px 16px;" class="text-end">{{ __('Target Value') }}</th>
                                <th style="width: 140px; padding: 10px 16px;" class="text-end">{{ __('Target Incentive') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($restrictedTeams ?? $teams as $teamId => $teamName)
                                <tr>
                                    <td class="align-middle fw-semibold text-dark" style="padding: 10px 16px;">{{ $teamName }}</td>
                                    <td style="padding: 6px 16px;">
                                        <input type="number" name="team_targets[{{ $teamId }}]" class="form-control form-control-sm text-end allocation-input" min="0" placeholder="0" style="border-radius: 6px; font-weight: 700;">
                                    </td>
                                    <td style="padding: 6px 16px;">
                                        <input type="number" name="team_incentives[{{ $teamId }}]" class="form-control form-control-sm text-end" min="0" placeholder="0.00" step="0.01" style="border-radius: 6px; font-weight: 700;">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-3 text-muted">{{ __('No child teams found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Live error message --}}
                <div class="text-danger fw-bold text-xs mt-2 d-none" id="allocation-warning">
                    <i class="ti ti-alert-triangle me-1"></i> {{ __('Total allocated sum exceeds the remaining limit!') }}
                </div>
            </div>

        @elseif($ctx == 'members_only')
            {{-- Team → Members: show only member users --}}
            <input type="hidden" name="assignment_type" value="individual">
            <div class="col-md-12 form-group">
                <label class="col-form-label fw-bold">{{ __('Divide Target among Members') }}</label>
                <div class="d-flex align-items-center gap-1 mb-2">
                    <i class="ti ti-user-check text-primary" style="font-size:14px;"></i>
                    <small class="text-muted">{{ __('Members of') }} <strong>{{ $contextLabel }}</strong></small>
                </div>
                
                {{-- Live Allocations Tracker --}}
                @php
                    $assignedSum = \App\Models\Target::where('parent_id', $parentTarget->id)->sum('target_value');
                    $remainingVal = max(0, $parentTarget->target_value - $assignedSum);
                @endphp
                <div class="p-3 rounded-4 mb-3" style="background: rgba(var(--primary-theme-color-rgb), 0.03); border: 1px solid rgba(var(--primary-theme-color-rgb), 0.1);">
                    <div class="row text-center g-2" style="font-size: 0.82rem;">
                        <div class="col-4 border-end">
                            <span class="text-muted d-block text-xxs uppercase">{{ __('Parent Target') }}</span>
                            <span class="fw-bold text-dark fs-6">{{ $parentTarget->target_value }}</span>
                        </div>
                        <div class="col-4 border-end">
                            <span class="text-muted d-block text-xxs uppercase">{{ __('Already Divided') }}</span>
                            <span class="fw-bold text-primary fs-6">{{ $assignedSum }}</span>
                        </div>
                        <div class="col-4">
                            <span class="text-muted d-block text-xxs uppercase">{{ __('Remaining Limit') }}</span>
                            <span class="fw-bold text-success fs-6" id="remaining-limit-val" data-limit="{{ $remainingVal }}">{{ $remainingVal }}</span>
                        </div>
                    </div>
                </div>

                @if(!empty($restrictedUsers))
                    <div class="table-responsive border rounded-3 overflow-hidden bg-white shadow-xs">
                        <table class="table table-hover align-items-center mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="padding: 10px 16px;">{{ __('Member Name') }}</th>
                                    <th style="width: 140px; padding: 10px 16px;" class="text-end">{{ __('Target Value') }}</th>
                                    <th style="width: 140px; padding: 10px 16px;" class="text-end">{{ __('Target Incentive') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($restrictedUsers as $userId => $userName)
                                    <tr>
                                        <td class="align-middle fw-semibold text-dark" style="padding: 10px 16px;">{{ $userName }}</td>
                                        <td style="padding: 6px 16px;">
                                            <input type="number" name="individual_targets[{{ $userId }}]" class="form-control form-control-sm text-end allocation-input" min="0" placeholder="0" style="border-radius: 6px; font-weight: 700;">
                                        </td>
                                        <td style="padding: 6px 16px;">
                                            <input type="number" name="individual_incentives[{{ $userId }}]" class="form-control form-control-sm text-end" min="0" placeholder="0.00" step="0.01" style="border-radius: 6px; font-weight: 700;">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Live error message --}}
                    <div class="text-danger fw-bold text-xs mt-2 d-none" id="allocation-warning">
                        <i class="ti ti-alert-triangle me-1"></i> {{ __('Total allocated sum exceeds the remaining limit!') }}
                    </div>
                @else
                    <div class="alert alert-warning py-2">{{ __('No members found in this team. Please add employees first.') }}</div>
                @endif
            </div>

        @else
            {{-- Free context: show all options --}}
            <div class="col-md-12 form-group">
                {{ Form::label('assignment_type', __('Assign Target To'), ['class' => 'col-form-label']) }}
                <div class="d-flex gap-3">
                    @if(Auth::user()->type != 'company' && Auth::user()->type != 'super admin')
                        <div class="form-check">
                            {{ Form::radio('assignment_type', 'individual', true, ['class' => 'form-check-input', 'id' => 'assign_individual', 'onchange' => 'toggleAssignmentFields()']) }}
                            {{ Form::label('assign_individual', __('Individual'), ['class' => 'form-check-label']) }}
                        </div>
                    @endif
                    <div class="form-check">
                        {{ Form::radio('assignment_type', 'department', (Auth::user()->type == 'company' || Auth::user()->type == 'super admin'), ['class' => 'form-check-input', 'id' => 'assign_department', 'onchange' => 'toggleAssignmentFields()']) }}
                        {{ Form::label('assign_department', __('Department'), ['class' => 'form-check-label']) }}
                    </div>
                    <div class="form-check">
                        {{ Form::radio('assignment_type', 'team', false, ['class' => 'form-check-input', 'id' => 'assign_team', 'onchange' => 'toggleAssignmentFields()']) }}
                        {{ Form::label('assign_team', __('Team'), ['class' => 'form-check-label']) }}
                    </div>
                </div>
            </div>

            <div class="col-md-12 form-group {{ (Auth::user()->type == 'company' || Auth::user()->type == 'super admin') ? 'd-none' : '' }}" id="individual_field">
                {{ Form::label('assigned_to', __('Select Employee(s)'), ['class' => 'col-form-label']) }}
                {{ Form::select('assigned_to[]', $users, null, ['class' => 'form-control select2', 'multiple' => 'multiple', 'id' => 'assigned_to']) }}
            </div>

            <div class="col-md-12 form-group {{ (Auth::user()->type == 'company' || Auth::user()->type == 'super admin') ? '' : 'd-none' }}" id="department_field">
                {{ Form::label('department_id', __('Select Department(s)'), ['class' => 'col-form-label']) }}
                {{ Form::select('department_id[]', $departments, null, ['class' => 'form-control select2', 'multiple' => 'multiple', 'id' => 'department_id']) }}
            </div>

            <div class="col-md-12 form-group d-none" id="team_field">
                {{ Form::label('team_id', __('Select Team(s)'), ['class' => 'col-form-label']) }}
                {{ Form::select('team_id[]', $teams, null, ['class' => 'form-control select2', 'multiple' => 'multiple', 'id' => 'team_id']) }}
            </div>
        @endif
        {{-- ────────────────────────────────────────────────────────── --}}

        @if(isset($parentTarget) && $parentTarget)
            <input type="hidden" name="start_date" value="{{ $parentTarget->start_date }}">
            <input type="hidden" name="end_date" value="{{ $parentTarget->end_date }}">
            <input type="hidden" name="target_type" value="{{ $parentTarget->target_type }}">
            @if($parentTarget->target_type == 'lead_stage')
                <input type="hidden" name="pipeline_id" value="{{ $parentTarget->pipeline_id }}">
                <input type="hidden" name="stage_id" value="{{ $parentTarget->stage_id }}">
                <input type="hidden" name="custom_date_field" value="{{ $parentTarget->custom_date_field }}">
            @endif
        @else
            <div class="col-md-6 form-group">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}
                {{ Form::date('start_date', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
            <div class="col-md-6 form-group">
                {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}
                {{ Form::date('end_date', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
            <div class="col-md-6 form-group">
                {{ Form::label('target_value', __('Target Quantity'), ['class' => 'col-form-label']) }}
                {{ Form::number('target_value', 1, ['class' => 'form-control', 'required' => 'required', 'min' => '1']) }}
            </div>
            <div class="col-md-6 form-group">
                {{ Form::label('incentive', __('Target Incentive'), ['class' => 'col-form-label']) }}
                {{ Form::number('incentive', 0.00, ['class' => 'form-control', 'step' => '0.01', 'min' => '0']) }}
            </div>

            <div class="col-md-12 form-group">
                {{ Form::label('target_type', __('Target Tracking Type'), ['class' => 'col-form-label']) }}
                {{ Form::select('target_type', ['manual' => __('Manual (Self Reported)'), 'lead_stage' => __('Lead Stage Transition (Automated)')], 'manual', ['class' => 'form-control select2', 'id' => 'target_type']) }}
            </div>

            <div class="col-md-6 form-group d-none" id="pipeline_field">
                {{ Form::label('pipeline_id', __('Select Pipeline'), ['class' => 'col-form-label']) }}
                {{ Form::select('pipeline_id', ['' => __('Select Pipeline')] + $pipelines, null, ['class' => 'form-control select2', 'id' => 'pipeline_id']) }}
            </div>

            <div class="col-md-6 form-group d-none" id="stage_field">
                {{ Form::label('stage_id', __('Select Stage'), ['class' => 'col-form-label']) }}
                <select name="stage_id" id="stage_id" class="form-control select2">
                    <option value="">{{ __('Select Stage') }}</option>
                </select>
            </div>

            <div class="col-md-12 form-group d-none" id="custom_date_field_group">
                {{ Form::label('custom_date_field', __('Select Date Field for Scoping'), ['class' => 'col-form-label']) }}
                {{ Form::select('custom_date_field', ['created_at' => __('Lead Creation Date (created_at)')] + $customDateFields, 'created_at', ['class' => 'form-control select2', 'id' => 'custom_date_field']) }}
            </div>
        @endif
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <button type="submit" class="btn  btn-primary">{{ __('Create') }}</button>
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        if (typeof $().select2 !== 'undefined') {
            $('.select2').select2({
                dropdownParent: $('.modal-body').parent()
            });
        }

        // Live allocation limit checks
        $(document).on('input', '.allocation-input', function() {
            var limit = parseFloat($('#remaining-limit-val').attr('data-limit')) || 0;
            var sum = 0;
            $('.allocation-input').each(function() {
                var val = parseFloat($(this).val()) || 0;
                sum += val;
            });

            var remaining = limit - sum;
            $('#remaining-limit-val').text(remaining >= 0 ? remaining : 0);

            if (remaining < 0) {
                $('#allocation-warning').removeClass('d-none');
                $('#remaining-limit-val').removeClass('text-success').addClass('text-danger');
                $('button[type="submit"]').prop('disabled', true);
            } else {
                $('#allocation-warning').addClass('d-none');
                $('#remaining-limit-val').removeClass('text-danger').addClass('text-success');
                $('button[type="submit"]').prop('disabled', false);
            }
        });
    });

    function toggleAssignmentFields() {
        const type = $('input[name="assignment_type"]:checked').val();
        $('#individual_field').addClass('d-none');
        $('#department_field').addClass('d-none');
        $('#team_field').addClass('d-none');

        if (type === 'individual') $('#individual_field').removeClass('d-none');
        else if (type === 'department') $('#department_field').removeClass('d-none');
        else if (type === 'team') $('#team_field').removeClass('d-none');
    }

    $(document).on('change', '#target_type', function() {
        var type = $(this).val();
        if (type === 'lead_stage') {
            $('#pipeline_field').removeClass('d-none');
            $('#stage_field').removeClass('d-none');
            $('#custom_date_field_group').removeClass('d-none');
            $('#pipeline_id').attr('required', 'required');
            $('#stage_id').attr('required', 'required');
            $('#custom_date_field').attr('required', 'required');
        } else {
            $('#pipeline_field').addClass('d-none');
            $('#stage_field').addClass('d-none');
            $('#custom_date_field_group').addClass('d-none');
            $('#pipeline_id').removeAttr('required');
            $('#stage_id').removeAttr('required');
            $('#custom_date_field').removeAttr('required');
        }
    });

    $(document).on('change', '#pipeline_id', function() {
        var pipelineId = $(this).val();
        if (pipelineId) {
            $.ajax({
                url: "{{ route('targets.get.pipeline.stages') }}",
                type: 'GET',
                data: { pipeline_id: pipelineId },
                success: function(data) {
                    var select = $('#stage_id');
                    select.empty();
                    select.append('<option value="">' + "{{ __('Select Stage') }}" + '</option>');
                    $.each(data, function(key, value) {
                        select.append('<option value="' + key + '">' + value + '</option>');
                    });
                    select.trigger('change');
                }
            });
        }
    });
</script>
