{{ Form::open(['url' => 'targets/templates', 'method' => 'post']) }}
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
            ], 'manual', ['class' => 'form-control select2', 'id' => 'template_target_type']) }}
        </div>

        <div class="col-md-6 form-group d-none" id="template_pipeline_field">
            {{ Form::label('pipeline_id', __('Select Pipeline'), ['class' => 'col-form-label']) }}
            {{ Form::select('pipeline_id', ['' => __('Select Pipeline')] + $pipelines, null, ['class' => 'form-control select2', 'id' => 'template_pipeline_id']) }}
        </div>

        <div class="col-md-6 form-group d-none" id="template_stage_field">
            {{ Form::label('stage_id', __('Select Stage'), ['class' => 'col-form-label']) }}
            <select name="stage_id" id="template_stage_id" class="form-control select2">
                <option value="">{{ __('Select Stage') }}</option>
            </select>
        </div>

        <div class="col-md-12 form-group d-none" id="template_custom_date_field_group">
            {{ Form::label('custom_date_field', __('Select Date Field for Scoping'), ['class' => 'col-form-label']) }}
            {{ Form::select('custom_date_field', ['created_at' => __('Lead Creation Date (created_at)')] + $customDateFields, 'created_at', ['class' => 'form-control select2', 'id' => 'template_custom_date_field']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Create Template') }}</button>
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        var val = $('#template_target_type').val();
        if (val === 'lead_stage') {
            $('#template_pipeline_field').removeClass('d-none');
            $('#template_stage_field').removeClass('d-none');
            $('#template_custom_date_field_group').removeClass('d-none');
        } else if (['account', 'ftd', 'revenue'].includes(val)) {
            $('#template_custom_date_field_group').removeClass('d-none');
        }
    });

    $(document).on('change', '#template_target_type', function() {
        var type = $(this).val();
        if (type === 'lead_stage') {
            $('#template_pipeline_field').removeClass('d-none');
            $('#template_stage_field').removeClass('d-none');
            $('#template_custom_date_field_group').removeClass('d-none');
            $('#template_pipeline_id').attr('required', 'required');
            $('#template_stage_id').attr('required', 'required');
            $('#template_custom_date_field').attr('required', 'required');
        } else if (['account', 'ftd', 'revenue'].includes(type)) {
            $('#template_pipeline_field').addClass('d-none');
            $('#template_stage_field').addClass('d-none');
            $('#template_custom_date_field_group').removeClass('d-none');
            $('#template_pipeline_id').removeAttr('required');
            $('#template_stage_id').removeAttr('required');
            $('#template_custom_date_field').attr('required', 'required');
        } else {
            $('#template_pipeline_field').addClass('d-none');
            $('#template_stage_field').addClass('d-none');
            $('#template_custom_date_field_group').addClass('d-none');
            $('#template_pipeline_id').removeAttr('required');
            $('#template_stage_id').removeAttr('required');
            $('#template_custom_date_field').removeAttr('required');
        }
    });

    $(document).on('change', '#template_pipeline_id', function() {
        var pipelineId = $(this).val();
        if (pipelineId) {
            $.ajax({
                url: "{{ route('targets.get.pipeline.stages') }}",
                type: 'GET',
                data: { pipeline_id: pipelineId },
                success: function(data) {
                    var select = $('#template_stage_id');
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
