{{Form::model($role, array('route' => array('roles.update', $role->id), 'method' => 'PUT')) }}
    <div class="form-group">
        {{Form::label('name',__('Name'),array('class'=>'col-form-label'))}}
        {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Role Name')))}}
        @error('name')
        <span class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
        @enderror
    </div>
    <div class="form-group">
        {{Form::label('display_name',__('Display Name'),array('class'=>'col-form-label'))}}
        {{Form::text('display_name',null,array('class'=>'form-control','placeholder'=>__('Enter Display Name')))}}
        @error('display_name')
        <span class="invalid-display_name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
        @enderror
    </div>
    <div class="form-group">
        {{Form::label('module',__('Module'),array('class'=>'col-form-label'))}}
        <select class="form-control" data-trigger name="module" id="choices-single-default" placeholder="Select Module">
            @foreach ($modules as $module)
                <option value="{{ $module }}" {{ $role->module == $module ? 'selected' : '' }}>{{ $module }}</option>
            @endforeach
        </select>
        @error('module')
        <span class="invalid-module" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
        @enderror
    </div>

    <div class="text-end">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
    </div>
{{Form::close()}}
<script>
 var multipleCancelButton = new Choices(
        '#choices-single-default', {
            removeItemButton: true,
        }
        );

</script>
