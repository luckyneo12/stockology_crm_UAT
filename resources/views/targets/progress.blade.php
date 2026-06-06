{{ Form::open(['route' => ['targets.progress.update', $target->id], 'method' => 'POST']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="card bg-light-primary text-primary border-0">
                <div class="card-body p-3">
                    <h6 class="mb-1">{{ __('Target Objective') }}: {{ $target->target_name }}</h6>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span><strong>{{ __('Quota') }}:</strong> {{ $target->target_value }}</span>
                        <span><strong>{{ __('Previously Reported') }}:</strong> {{ $target->achieved_value }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 form-group">
            {{ Form::label('achieved_value', __('Enter Current Accomplished Quantity'), ['class' => 'col-form-label']) }}
            {{ Form::number('achieved_value', $target->achieved_value, ['class' => 'form-control', 'required' => 'required', 'min' => '0']) }}
            <small class="text-muted">{{ __('Updating this will recalculate your performance percentage.') }}</small>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    <button type="submit" class="btn  btn-primary">{{ __('Update My Progress') }}</button>
</div>
{{ Form::close() }}
