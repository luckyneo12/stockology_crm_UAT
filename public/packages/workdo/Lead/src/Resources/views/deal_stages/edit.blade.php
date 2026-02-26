
{{ Form::model($dealStage, array('route' => array('deal-stages.update', $dealStage->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-12">
            {{ Form::label('name', __('Deal Stage Name'),['class'=>'col-form-label']) }} <x-required></x-required>
            {{ Form::text('name', null, array('class' => 'form-control','required'=>'required','placeholder' => __('Enter Deal Stage Name'),'maxlength' => '30')) }}
        </div>
        <div class="form-group col-12">
            {{ Form::label('pipeline_id', __('Pipeline'),['class'=>'col-form-label']) }} <x-required></x-required>
            {{ Form::select('pipeline_id', $pipelines,null, array('class' => 'form-control select2','required'=>'required')) }}
        </div>
        
        <div class="col-12 mt-4">
            <h6 class="text-sm font-weight-bold">{{ __('Automation Rule') }}</h6>
            <hr class="my-2">
            <div class="form-group">
                {{ Form::label('target_department_id', __('Target Department (Auto-Transfer)'), ['class' => 'col-form-label']) }}
                <select name="target_department_id" class="form-control select2">
                    <option value="">{{ __('Select Target Department') }}</option>
                    @if(isset($departments) && count($departments) > 0)
                        @foreach($departments as $id => $name)
                            <option value="{{ $id }}" {{ (isset($automation->target_department_id) && $automation->target_department_id == $id) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    @endif
                </select>
                <small class="text-muted">{{ __('When a deal moves to this stage, it will be automatically assigned to the Head of this Department.') }}</small>
            </div>

            <div class="form-group mt-3">
                <h6 class="text-sm font-weight-bold">{{ __('Auto-Trigger Task') }}</h6>
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
                        {{ Form::select('auto_task_priority', \Workdo\Lead\Entities\DealTask::$priorities, $automation->auto_task_priority ?? null, array('class' => 'form-control select2')) }}
                    </div>
                    <div class="form-group col-md-3">
                        {{ Form::label('auto_task_duration', __('Duration (Days)'),['class'=>'col-form-label']) }}
                        {{ Form::number('auto_task_duration', $automation->auto_task_duration ?? 1, array('class' => 'form-control','min' => 0)) }}
                        <small class="text-xs">{{ __('Due after X days') }}</small>
                    </div>
                </div>
            </div>

            <div class="form-group mt-3">
                <h6 class="text-sm font-weight-bold">{{ __('Auto-Trigger Reminder') }}</h6>
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
        </div>

        <div class="col-12 mt-4">
            <h6 class="text-sm font-weight-bold">{{ __('Field Visibility') }}</h6>
            <hr class="my-2">
            @if(isset($customFields) && count($customFields) > 0)
                <div class="form-group">
                    <label class="form-label">{{ __('Select Visible Custom Fields') }}</label>
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
            @else
                <p class="text-muted text-sm">{{ __('No Custom Fields found for Deals.') }}</p>
            @endif
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Update')}}</button>
</div>
{{ Form::close() }}

