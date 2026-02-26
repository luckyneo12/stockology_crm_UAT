{{ Form::open(['route' => ['department.store_employee', $department->id], 'method' => 'POST']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('employee_id', __('Select Employee'), ['class' => 'form-label']) }}
                {{ Form::select('employee_id', $employees, null, ['class' => 'form-control choices', 'required' => 'required', 'placeholder' => __('Select Employee')]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Add'), ['class' => 'btn btn-primary']) }}
</div>
{{ Form::close() }}
