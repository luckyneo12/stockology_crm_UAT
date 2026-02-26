
{{ Form::open(array('route' => 'leads.bulk.task.reminder.store', 'class'=>'needs-validation', 'novalidate')) }}
<input type="hidden" name="ids" value="{{ $ids }}">
<div class="modal-body">
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="pills-task-tab" data-bs-toggle="pill" href="#tab-task" role="tab" aria-controls="pills-task" aria-selected="true">{{__('Task')}}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="pills-reminder-tab" data-bs-toggle="pill" href="#tab-reminder" role="tab" aria-controls="pills-reminder" aria-selected="false">{{__('Reminder')}}</a>
        </li>
    </ul>

    <div class="tab-content tab-bordered">
        <!-- Task Tab -->
        <div class="tab-pane fade show active" id="tab-task" role="tabpanel">
            <input type="hidden" name="type" value="task" id="action_type">
            <div class="row">
                <div class="col-12 form-group">
                    {{ Form::label('task_subject', __('Subject'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::text('task_subject', null, array('class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Task Subject'))) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('task_date', __('Date'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::date('task_date', date('Y-m-d'), array('class' => 'form-control', 'required' => 'required')) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('task_priority', __('Priority'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::select('task_priority', ['low'=>__('Low'), 'medium'=>__('Medium'), 'high'=>__('High')], null, array('class' => 'form-control choices', 'required'=>'required')) }}
                </div>
                <div class="col-12 form-group">
                    {{ Form::label('task_user_id', __('Assign To'),['class'=>'form-label']) }}
                    <select name="task_user_id" class="form-control choices">
                        <option value="lead_owner" selected>{{ __('Lead Responsible Person (Owner)') }}</option>
                        @foreach($users as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Reminder Tab -->
        <div class="tab-pane fade" id="tab-reminder" role="tabpanel">
            <div class="row">
                <div class="col-6 form-group">
                    {{ Form::label('reminder_date', __('Date'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::date('reminder_date', date('Y-m-d'), array('class' => 'form-control')) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label('reminder_time', __('Time'),['class'=>'form-label']) }}
                    {{ Form::time('reminder_time', null, array('class' => 'form-control')) }}
                </div>
                 <div class="col-12 form-group">
                    {{ Form::label('reminder_description', __('Description'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                    {{ Form::textarea('reminder_description', null, array('class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Reminder Description'))) }}
                </div>
                <div class="col-12 form-group">
                    {{ Form::label('reminder_user_id', __('Assign To'),['class'=>'form-label']) }}
                    <select name="reminder_user_id" class="form-control choices">
                        <option value="lead_owner" selected>{{ __('Lead Responsible Person (Owner)') }}</option>
                        @foreach($users as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">{{ __('This reminder will be created for the selected user.') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    <button type="submit" class="btn btn-primary">{{__('Create')}}</button>
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
         if (typeof Choices !== 'undefined') {
            var selectElements = document.querySelectorAll('.choices');
            selectElements.forEach(function(element) {
                 if (!element.choicesInstance) {
                    new Choices(element, {
                        removeItemButton: true,
                        searchEnabled: true,
                         shouldSort: false
                    });
                 }
            });
        }
        
        $('a[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href") // activated tab
            if(target == '#tab-task') {
                $('#action_type').val('task');
                $('input[name="task_subject"]').attr('required', 'required');
                $('textarea[name="reminder_description"]').removeAttr('required');
            } else {
                $('#action_type').val('reminder');
                $('input[name="task_subject"]').removeAttr('required');
                $('textarea[name="reminder_description"]').attr('required', 'required');
            }
        });
    });
</script>
