{{ Form::model($document, array('route' => array('lead-documents.update', $document->id), 'method' => 'PUT','enctype'=>'multipart/form-data')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12 form-group">
            {{ Form::label('name', __('Name'),['class'=>'form-label']) }}
            {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-12 form-group">
            {{ Form::label('stage_id', __('Visible From Stage'),['class'=>'form-label']) }}
            {{ Form::select('stage_id', $stages, null, array('class' => 'form-control select2')) }}
        </div>
        <div class="col-12 form-group">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" name="is_required" id="is_required" {{ $document->is_required ? 'checked' : '' }}>
                <label class="form-check-label" for="is_required">{{ __('Is Required') }}</label>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Update')}}</button>
</div>
{{ Form::close() }}
