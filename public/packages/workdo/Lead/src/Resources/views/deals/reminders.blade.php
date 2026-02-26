
@if(!empty($reminder))
{{ Form::model($reminder, array('route' => array('deals.reminders.update', $deal->id, $reminder->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}
@else
{{ Form::open(array('route' => ['deals.reminders.store',$deal->id],'class'=>'needs-validation','novalidate')) }}
@endif
<div class="modal-body">
    <div class="row">
        <div class="col-12 form-group">
            {{ Form::label('title', __('Title'),['class'=>'col-form-label']) }}<x-required></x-required>
            {{ Form::text('title', null, array('class' => 'form-control','required'=>'required','placeholder' => __('Enter Title'))) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('remind_at', __('Remind At'),['class'=>'col-form-label']) }}<x-required></x-required>
            {{ Form::input('datetime-local', 'remind_at', isset($reminder) ? date('Y-m-d\TH:i', strtotime($reminder->remind_at)) : null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('user_id', __('Assigned User'),['class'=>'col-form-label']) }}<x-required></x-required>
            {{ Form::select('user_id', $users, null, array('class' => 'form-control', 'id'=>'user_id', 'required'=>'required')) }}
        </div>
        <div class="col-6 form-group">
            {{ Form::label('type', __('Type'),['class'=>'col-form-label']) }}<x-required></x-required>
            <select class="form-control" name="type" required id="type">
                @foreach($types as $key => $type)
                    <option value="{{$key}}" @if(isset($reminder) && $reminder->type == $key) selected @endif>{{__($type)}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 form-group">
            {{ Form::label('description', __('Description'),['class'=>'col-form-label']) }}
            {{ Form::textarea('description', null, array('class' => 'form-control', 'rows'=>3, 'placeholder' => __('Enter Description'))) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    @if(isset($reminder))
        <button type="submit" class="btn  btn-primary">{{__('Update')}}</button>
    @else
        <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
    @endif
</div>

{{ Form::close() }}
