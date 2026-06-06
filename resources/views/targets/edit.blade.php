{{ Form::model($target, ['route' => ['targets.update', $target->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        @if($target->parentTarget)
            <div class="col-md-12">
                <div class="alert alert-info">
                    <strong>{{ __('Part of Master Target') }}:</strong> {{ $target->parentTarget->target_name }}
                    <input type="hidden" name="parent_id" value="{{ $target->parent_id }}">
                </div>
            </div>
        @endif

        <div class="col-md-12 form-group">
            {{ Form::label('target_name', __('Target Objective / Name'), ['class' => 'col-form-label']) }}
            {{ Form::text('target_name', null, ['class' => 'form-control', 'required' => 'required', (!$canChangeResponsible ? 'readonly' : '')]) }}
        </div>

        <div class="col-md-12 form-group">
            {{ Form::label('assignment_type', __('Assign Target To'), ['class' => 'col-form-label']) }}
            <div class="d-flex gap-3">
                @if(Auth::user()->type != 'company' && Auth::user()->type != 'super admin' || $target->parent_id)
                <div class="form-check">
                    {{ Form::radio('assignment_type', 'individual', ($target->assigned_to ? true : false), ['class' => 'form-check-input', 'id' => 'edit_assign_individual', 'onchange' => 'toggleEditAssignmentFields()', (!$canChangeResponsible ? 'disabled' : '')]) }}
                    {{ Form::label('edit_assign_individual', __('Individual'), ['class' => 'form-check-label']) }}
                </div>
                @endif
                <div class="form-check">
                    {{ Form::radio('assignment_type', 'department', ($target->department_id ? true : false), ['class' => 'form-check-input', 'id' => 'edit_assign_department', 'onchange' => 'toggleEditAssignmentFields()', (!$canChangeResponsible ? 'disabled' : '')]) }}
                    {{ Form::label('edit_assign_department', __('Department'), ['class' => 'form-check-label']) }}
                </div>
                <div class="form-check">
                    {{ Form::radio('assignment_type', 'team', ($target->team_id ? true : false), ['class' => 'form-check-input', 'id' => 'edit_assign_team', 'onchange' => 'toggleEditAssignmentFields()', (!$canChangeResponsible ? 'disabled' : '')]) }}
                    {{ Form::label('edit_assign_team', __('Team'), ['class' => 'form-check-label']) }}
                </div>
            </div>
        </div>

        <div class="col-md-12 form-group {{ $target->assigned_to ? '' : 'd-none' }}" id="edit_individual_field">
            {{ Form::label('assigned_to', __('Select Employee'), ['class' => 'col-form-label']) }}
            {{ Form::select('assigned_to', ['' => __('Select Employee')] + $users, null, ['class' => 'form-control select2', (!$canChangeResponsible ? 'disabled' : '')]) }}
            @if(!$canChangeResponsible) {{ Form::hidden('assigned_to', $target->assigned_to) }} @endif
        </div>

        <div class="col-md-12 form-group {{ $target->department_id ? '' : 'd-none' }}" id="edit_department_field">
            {{ Form::label('department_id', __('Select Department'), ['class' => 'col-form-label']) }}
            {{ Form::select('department_id', ['' => __('Select Department')] + $departments, null, ['class' => 'form-control select2', 'id' => 'edit_department_id', (!$canChangeResponsible ? 'disabled' : '')]) }}
            @if(!$canChangeResponsible) {{ Form::hidden('department_id', $target->department_id) }} @endif
        </div>

        <div class="col-md-12 form-group {{ $target->team_id ? '' : 'd-none' }}" id="edit_team_field">
            {{ Form::label('team_id', __('Select Team'), ['class' => 'col-form-label']) }}
            {{ Form::select('team_id', ['' => __('Select Team')] + $teams, null, ['class' => 'form-control select2', 'id' => 'edit_team_id', (!$canChangeResponsible ? 'disabled' : '')]) }}
            @if(!$canChangeResponsible) {{ Form::hidden('team_id', $target->team_id) }} @endif
        </div>

        <div class="col-md-6 form-group">
            {{ Form::label('start_date', __('Start Date'), ['class' => 'col-form-label']) }}
            {{ Form::date('start_date', null, ['class' => 'form-control', (!$canChangeResponsible ? 'readonly' : '')]) }}
        </div>
        <div class="col-md-6 form-group">
            {{ Form::label('end_date', __('End Date'), ['class' => 'col-form-label']) }}
            {{ Form::date('end_date', null, ['class' => 'form-control', (!$canChangeResponsible ? 'readonly' : '')]) }}
        </div>
        <div class="col-md-6 form-group">
            {{ Form::label('target_value', __('Target Quantity'), ['class' => 'col-form-label']) }}
            {{ Form::number('target_value', null, ['class' => 'form-control', 'required' => 'required', 'min' => '1', (!$canChangeResponsible ? 'readonly' : '')]) }}
        </div>
        <div class="col-md-6 form-group">
            {{ Form::label('incentive', __('Target Incentive'), ['class' => 'col-form-label']) }}
            {{ Form::number('incentive', null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0', (!$canChangeResponsible ? 'readonly' : '')]) }}
        </div>
        <div class="col-md-6 form-group" id="edit_achieved_field">
            {{ Form::label('achieved_value', __('Achieved Quantity'), ['class' => 'col-form-label']) }}
            {{ Form::number('achieved_value', null, ['class' => 'form-control', 'required' => 'required', 'min' => '0', ($target->target_type == 'lead_stage' ? 'readonly' : '')]) }}
            @if($target->target_type == 'lead_stage')
                <small class="text-info" id="edit_achieved_note">{{ __('Automatically calculated based on lead movements.') }}</small>
            @endif
        </div>
        
        <div class="col-md-12 form-group">
            {{ Form::label('target_type', __('Target Tracking Type'), ['class' => 'col-form-label']) }}
            {{ Form::select('target_type', ['manual' => __('Manual (Self Reported)'), 'lead_stage' => __('Lead Stage Transition (Automated)')], null, ['class' => 'form-control select2', 'id' => 'edit_target_type', (!$canChangeResponsible ? 'disabled' : '')]) }}
            @if(!$canChangeResponsible) {{ Form::hidden('target_type', $target->target_type) }} @endif
        </div>

        <div class="col-md-6 form-group {{ $target->target_type == 'lead_stage' ? '' : 'd-none' }}" id="edit_pipeline_field">
            {{ Form::label('pipeline_id', __('Select Pipeline'), ['class' => 'col-form-label']) }}
            {{ Form::select('pipeline_id', ['' => __('Select Pipeline')] + $pipelines, null, ['class' => 'form-control select2', 'id' => 'edit_pipeline_id', (!$canChangeResponsible ? 'disabled' : '')]) }}
            @if(!$canChangeResponsible) {{ Form::hidden('pipeline_id', $target->pipeline_id) }} @endif
        </div>

        <div class="col-md-6 form-group {{ $target->target_type == 'lead_stage' ? '' : 'd-none' }}" id="edit_stage_field">
            {{ Form::label('stage_id', __('Select Stage'), ['class' => 'col-form-label']) }}
            {{ Form::select('stage_id', ['' => __('Select Stage')] + $stages, null, ['class' => 'form-control select2', 'id' => 'edit_stage_id', (!$canChangeResponsible ? 'disabled' : '')]) }}
            @if(!$canChangeResponsible) {{ Form::hidden('stage_id', $target->stage_id) }} @endif
        </div>

        <div class="col-md-12 form-group {{ $target->target_type == 'lead_stage' ? '' : 'd-none' }}" id="edit_custom_date_field_group">
            {{ Form::label('custom_date_field', __('Select Date Field for Scoping'), ['class' => 'col-form-label']) }}
            {{ Form::select('custom_date_field', ['created_at' => __('Lead Creation Date (created_at)')] + $customDateFields, null, ['class' => 'form-control select2', 'id' => 'edit_custom_date_field', (!$canChangeResponsible ? 'disabled' : '')]) }}
            @if(!$canChangeResponsible) {{ Form::hidden('custom_date_field', $target->custom_date_field) }} @endif
        </div>

        @if($canChangeResponsible)
        <div class="col-md-12 form-group">
            <div class="form-check">
                {{ Form::checkbox('can_edit', 1, null, ['class' => 'form-check-input', 'id' => 'can_edit']) }}
                {{ Form::label('can_edit', __('Allow Responsible Person to Edit / Update this target'), ['class' => 'form-check-label']) }}
            </div>
        </div>
        @endif
        <div class="col-md-12 form-group">
            {{ Form::label('status', __('Override Status'), ['class' => 'col-form-label']) }}
            {{ Form::select('status', ['Pending' => __('Pending'), 'Completed' => __('Completed'), 'Missed' => __('Missed')], null, ['class' => 'form-control select2']) }}
            <small class="text-muted">{{ __('Note: Status automatically completes if achieved reaches target.') }}</small>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <button type="submit" class="btn  btn-primary">{{ __('Update') }}</button>
</div>
{{ Form::close() }}

<script>
    function toggleEditAssignmentFields() {
        const type = $('input[name="assignment_type"]:checked').val();
        $('#edit_individual_field').addClass('d-none');
        $('#edit_department_field').addClass('d-none');
        $('#edit_team_field').addClass('d-none');

        if (type === 'individual') $('#edit_individual_field').removeClass('d-none');
        else if (type === 'department') $('#edit_department_field').removeClass('d-none');
        else if (type === 'team') $('#edit_team_field').removeClass('d-none');
    }

    $(document).on('change', '#edit_target_type', function() {
        var type = $(this).val();
        if (type === 'lead_stage') {
            $('#edit_pipeline_field').removeClass('d-none');
            $('#edit_stage_field').removeClass('d-none');
            $('#edit_custom_date_field_group').removeClass('d-none');
            $('#edit_pipeline_id').attr('required', 'required');
            $('#edit_stage_id').attr('required', 'required');
            $('#edit_custom_date_field').attr('required', 'required');
            
            // Make achieved value read-only
            $('#edit_achieved_field input').attr('readonly', 'readonly');
            if ($('#edit_achieved_note').length == 0) {
                $('#edit_achieved_field').append('<small class="text-info" id="edit_achieved_note">' + "{{ __('Automatically calculated based on lead movements.') }}" + '</small>');
            }
        } else {
            $('#edit_pipeline_field').addClass('d-none');
            $('#edit_stage_field').addClass('d-none');
            $('#edit_custom_date_field_group').addClass('d-none');
            $('#edit_pipeline_id').removeAttr('required');
            $('#edit_stage_id').removeAttr('required');
            $('#edit_custom_date_field').removeAttr('required');
            
            // Make achieved value editable
            $('#edit_achieved_field input').removeAttr('readonly');
            $('#edit_achieved_note').remove();
        }
    });

    $(document).on('change', '#edit_pipeline_id', function() {
        var pipelineId = $(this).val();
        if (pipelineId) {
            $.ajax({
                url: "{{ route('targets.get.pipeline.stages') }}",
                type: 'GET',
                data: { pipeline_id: pipelineId },
                success: function(data) {
                    var select = $('#edit_stage_id');
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
