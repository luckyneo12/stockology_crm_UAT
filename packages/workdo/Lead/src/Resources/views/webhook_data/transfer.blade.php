{{ Form::model($webhookData, array('route' => array('webhook-data.transfer', $webhookData->id), 'method' => 'POST')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-12">
            {{ Form::label('assigned_user_id', __('Transfer To User'),['class'=>'form-label']) }}
            {{ Form::select('assigned_user_id', $users, null, array('class' => 'form-control', 'required' => 'required', 'placeholder' => __('Select User'))) }}
        </div>
        <div class="col-12 text-muted">
            <small>{{ __('Transferring this data will notify the users who have permissions to view this webhook endpoint.') }}</small>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Transfer')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}
