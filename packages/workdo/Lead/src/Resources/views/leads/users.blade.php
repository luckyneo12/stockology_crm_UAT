
{{ Form::model($lead, array('route' => array('leads.users.update', $lead->id), 'method' => 'PUT')) }}
    <div class="modal-body">
        <div class="row">
            <div class="col-12 form-group">
                {{ Form::label('users', __('Responsible Person'),['class'=>'col-form-label']) }}
                {{ Form::select('users', $users, $lead->user_id, array('class' => 'form-control choices','id'=>'choices-single','required'=>'required')) }}
                <p class="text-danger d-none" id="user_validation">{{__('Responsible Person field is required.')}}</p>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        <button type="submit" class="btn  btn-primary" id="submit">{{__('Save')}}</button>
    </div>
{{ Form::close() }}

<script>
    $(function(){
        $("#submit").click(function() {
            var user =  $("#choices-single").val();
            if(!user){
            $('#user_validation').removeClass('d-none')
                return false;
            }else{
            $('#user_validation').addClass('d-none')
            }
        });
    });
</script>

