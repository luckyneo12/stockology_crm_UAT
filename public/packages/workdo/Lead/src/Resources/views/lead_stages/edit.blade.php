
{{ Form::model($leadStage, array('route' => array('lead-stages.update', $leadStage->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group col-12">
                {{ Form::label('name', __('Lead Stage Name'),['class'=>'col-form-label']) }}<x-required></x-required>
                {{ Form::text('name', null, array('class' => 'form-control','required'=>'required','placeholder' => __('Enter Lead Stage Name'),'maxlength' => '30')) }}
            </div>
            <div class="form-group col-12">
                {{ Form::label('pipeline_id', __('Pipeline'),['class'=>'col-form-label']) }}<x-required></x-required>
                {{ Form::select('pipeline_id', $pipelines,null, array('class' => 'form-control select2','required'=>'required')) }}
            </div>
        </div>

        <hr>
        <div class="row">
            @if(!empty($departments))
                <div class="form-group col-12">
                    <h6 class="text-muted">{{ __('Automation Rule') }}</h6>
                    {{ Form::label('target_department_id', __('Move to Department on Entry'),['class'=>'col-form-label']) }}
                    {{ Form::select('target_department_id', $departments, $automation->target_department_id ?? null, array('class' => 'form-control select2','placeholder' => __('Select Target Department'))) }}
                    <small class="text-xs">{{ __('Lead will be automatically transferred to this department when moved to this stage.') }}</small>
                </div>
            @endif

            <div class="form-group col-12">
                <h6 class="text-muted">{{ __('Auto-Trigger Task') }}</h6>
                <div class="form-check form-switch custom-control-inline">
                    <input type="checkbox" name="is_auto_task" value="1" class="form-check-input" id="is_auto_task" {{ (isset($automation->is_auto_task) && $automation->is_auto_task == 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_auto_task">{{ __('Enable Auto Task Creation') }}</label>
                </div>
                <div class="row mt-2 auto_task_div {{ (isset($automation->is_auto_task) && $automation->is_auto_task == 1) ? '' : 'd-none' }}">
                    <div class="form-group col-md-6">
                        {{ Form::label('auto_task_name', __('Task Name'),['class'=>'col-form-label']) }}
                        {{ Form::text('auto_task_name', $automation->auto_task_name ?? null, array('class' => 'form-control','placeholder' => __('Enter Task Name'))) }}
                    </div>
                    <div class="form-group col-md-3">
                        {{ Form::label('auto_task_priority', __('Priority'),['class'=>'col-form-label']) }}
                        {{ Form::select('auto_task_priority', \Workdo\Lead\Entities\LeadTask::$priorities, $automation->auto_task_priority ?? null, array('class' => 'form-control select2')) }}
                    </div>
                    <div class="form-group col-md-3">
                        {{ Form::label('auto_task_duration', __('Duration (Days)'),['class'=>'col-form-label']) }}
                        {{ Form::number('auto_task_duration', $automation->auto_task_duration ?? 1, array('class' => 'form-control','min' => 0)) }}
                        <small class="text-xs">{{ __('Due after X days') }}</small>
                    </div>
                </div>
            </div>

            <div class="form-group col-12">
                <h6 class="text-muted">{{ __('Auto-Trigger Reminder') }}</h6>
                <div class="form-check form-switch custom-control-inline">
                    <input type="checkbox" name="is_auto_reminder" value="1" class="form-check-input" id="is_auto_reminder" {{ (isset($automation->is_auto_reminder) && $automation->is_auto_reminder == 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_auto_reminder">{{ __('Enable Auto Reminder Creation') }}</label>
                </div>
                <div class="row mt-2 auto_reminder_div {{ (isset($automation->is_auto_reminder) && $automation->is_auto_reminder == 1) ? '' : 'd-none' }}">
                    <div class="form-group col-md-8">
                        {{ Form::label('auto_reminder_title', __('Reminder Title'),['class'=>'col-form-label']) }}
                        {{ Form::text('auto_reminder_title', $automation->auto_reminder_title ?? null, array('class' => 'form-control','placeholder' => __('Enter Reminder Title'))) }}
                    </div>
                    <div class="form-group col-md-4">
                        {{ Form::label('auto_reminder_duration', __('Duration (Days)'),['class'=>'col-form-label']) }}
                        {{ Form::number('auto_reminder_duration', $automation->auto_reminder_duration ?? 1, array('class' => 'form-control','min' => 0)) }}
                        <small class="text-xs">{{ __('Remind after X days') }}</small>
                    </div>
                </div>
            </div>

            <script>
                $(document).on('change', '#is_auto_task', function() {
                    if ($(this).is(':checked')) {
                        $('.auto_task_div').removeClass('d-none');
                    } else {
                        $('.auto_task_div').addClass('d-none');
                    }
                });
                $(document).on('change', '#is_auto_reminder', function() {
                    if ($(this).is(':checked')) {
                        $('.auto_reminder_div').removeClass('d-none');
                    } else {
                        $('.auto_reminder_div').addClass('d-none');
                    }
                });
            </script>

            @if(!empty($customFields) && count($customFields) > 0)
                <div class="form-group col-12">
                    <h6 class="text-muted">{{ __('Field Visibility') }}</h6>
                    <small class="text-xs">{{ __('Select custom fields visible in this stage.') }}</small>
                    <div class="row mt-2">
                        @foreach($customFields as $cf)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" name="custom_fields[]" value="{{ $cf->id }}" class="form-check-input" id="cf_{{ $cf->id }}" {{ array_key_exists($cf->id, $stageCustomFields) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cf_{{ $cf->id }}">{{ $cf->name }}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="custom_fields_required[{{ $cf->id }}]" value="1" class="form-check-input" id="cfr_{{ $cf->id }}" {{ (array_key_exists($cf->id, $stageCustomFields) && $stageCustomFields[$cf->id] == 1) ? 'checked' : '' }}>
                                    <label class="form-check-label text-xs" for="cfr_{{ $cf->id }}">{{ __('Required') }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        <button type="submit" class="btn  btn-primary">{{__('Update')}}</button>
    </div>

{{ Form::close() }}
