{{ Form::model($template, ['route' => ['targets.templates.update', $template->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12 form-group">
            {{ Form::label('name', __('Template Name / Target Objective'), ['class' => 'col-form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('E.g. Convert 50 Leads or Account Opening')]) }}
        </div>

        <div class="col-md-12 form-group">
            {{ Form::label('target_type', __('Target Tracking Type'), ['class' => 'col-form-label']) }}
            {{ Form::select('target_type', [
                'manual' => __('Manual (Self Reported)'),
                'lead_stage' => __('Lead Stage Transition (Automated)'),
                'account' => __('Account Opening (Automated)'),
                'ftd' => __('FTD Count (Automated)'),
                'revenue' => __('Revenue Sum (Automated)'),
            ], null, ['class' => 'form-control select2', 'id' => 'edit_template_target_type']) }}
        </div>

        <div class="col-md-6 form-group {{ $template->target_type == 'lead_stage' ? '' : 'd-none' }}" id="edit_template_pipeline_field">
            {{ Form::label('pipeline_id', __('Select Pipeline'), ['class' => 'col-form-label']) }}
            {{ Form::select('pipeline_id', ['' => __('Select Pipeline')] + $pipelines, null, ['class' => 'form-control select2', 'id' => 'edit_template_pipeline_id']) }}
        </div>

        <div class="col-md-6 form-group {{ $template->target_type == 'lead_stage' ? '' : 'd-none' }}" id="edit_template_stage_field">
            {{ Form::label('stage_id', __('Select Stage'), ['class' => 'col-form-label']) }}
            {{ Form::select('stage_id', ['' => __('Select Stage')] + $stages, null, ['class' => 'form-control select2', 'id' => 'edit_template_stage_id']) }}
        </div>

        <div class="col-md-12 form-group {{ in_array($template->target_type, ['lead_stage', 'account', 'ftd', 'revenue']) ? '' : 'd-none' }}" id="edit_template_custom_date_field_group">
            {{ Form::label('custom_date_field', __('Select Date Field for Scoping'), ['class' => 'col-form-label']) }}
            {{ Form::select('custom_date_field', ['created_at' => __('Lead Creation Date (created_at)')] + $customDateFields, null, ['class' => 'form-control select2', 'id' => 'edit_template_custom_date_field']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Update Template') }}</button>
</div>
{{ Form::close() }}

<script>
    $(document).on('change', '#edit_template_target_type', function() {
        var type = $(this).val();
        if (type === 'lead_stage') {
            $('#edit_template_pipeline_field').removeClass('d-none');
            $('#edit_template_stage_field').removeClass('d-none');
            $('#edit_template_custom_date_field_group').removeClass('d-none');
            $('#edit_template_pipeline_id').attr('required', 'required');
            $('#edit_template_stage_id').attr('required', 'required');
            $('#edit_template_custom_date_field').attr('required', 'required');
        } else if (['account', 'ftd', 'revenue'].includes(type)) {
            $('#edit_template_pipeline_field').addClass('d-none');
            $('#edit_template_stage_field').addClass('d-none');
            $('#edit_template_custom_date_field_group').removeClass('d-none');
            $('#edit_template_pipeline_id').removeAttr('required');
            $('#edit_template_stage_id').removeAttr('required');
            $('#edit_template_custom_date_field').attr('required', 'required');
        } else {
            $('#edit_template_pipeline_field').addClass('d-none');
            $('#edit_template_stage_field').addClass('d-none');
            $('#edit_template_custom_date_field_group').addClass('d-none');
            $('#edit_template_pipeline_id').removeAttr('required');
            $('#edit_template_stage_id').removeAttr('required');
            $('#edit_template_custom_date_field').removeAttr('required');
        }
    });

    $(document).on('change', '#edit_template_pipeline_id', function() {
        var pipelineId = $(this).val();
        if (pipelineId) {
            $.ajax({
                url: "{{ route('targets.get.pipeline.stages') }}",
                type: 'GET',
                data: { pipeline_id: pipelineId },
                success: function(data) {
                    var select = $('#edit_template_stage_id');
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
