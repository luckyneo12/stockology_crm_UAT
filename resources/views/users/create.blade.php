    {{Form::open(array('url'=>'users','method'=>'post','class'=>'needs-validation','novalidate'))}}
    <div class="modal-body">
        <div class="row">
            @if(Auth::user()->type == 'super admin')
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('name',__('Name'),['class'=>'form-label']) }}<x-required></x-required>
                        {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter Customer Name'),'required'=>'required'))}}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('workSpace_name',__('WorkSpace Name'),['class'=>'form-label']) }}<x-required></x-required>
                        {{Form::text('workSpace_name',null,array('class'=>'form-control','placeholder'=>__('Enter WorkSpace Name'),'required'=>'required'))}}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('email',__('Email'),['class'=>'form-label'])}}<x-required></x-required>
                        {{Form::email('email',null,array('class'=>'form-control','placeholder'=>__('Enter Customer Email'),'required'=>'required'))}}
                    </div>
                </div>
            @endif
            @if(Auth::user()->type != 'super admin')
                <div class="col-md-6">
                    <div class="form-group">
                        {{Form::label('name',__('Name'),['class'=>'form-label']) }}<x-required></x-required>
                        {{Form::text('name',null,array('class'=>'form-control','placeholder'=>__('Enter User Name'),'required'=>'required'))}}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {{Form::label('email',__('Email'),['class'=>'form-label'])}}<x-required></x-required>
                        {{Form::email('email',null,array('class'=>'form-control','placeholder'=>__('Enter User Email'),'required'=>'required'))}}
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        {{ Form::label('roles', __('Roles'),['class'=>'form-label']) }}<x-required></x-required>
                        {{ Form::select('roles',$roles, null, ['class' => 'form-control','placeholder'=>'Select Role', 'id' => 'user_id','required'=>'required']) }}
                        <div class=" text-xs mt-1">
                            <span class="text-danger text-xs">{{ __('Unable to modify this user`s role. Please ensure that the correct role has been assigned to this user.') }}</span><br>
                            {{ __('Create role here. ') }}
                            <a href="{{ route('roles.index') }}"><b>{{ __('Create role') }}</b></a>
                        </div>
                    </div>
                </div>
                
                @if(!empty($departments) || !empty($teams))
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                        {{ Form::select('department_id', ['' => __('Select Department')] + $departments->toArray(), null, ['class' => 'form-control select', 'id' => 'department_id']) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('team_id', __('Team'), ['class' => 'form-label']) }}
                        {{ Form::select('team_id', ['' => __('Select Team')] + $teams->toArray(), null, ['class' => 'form-control select', 'id' => 'team_id']) }}
                    </div>
                </div>
                @endif

                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('reporting_to', __('Reporting To'), ['class' => 'form-label']) }}
                        {{ Form::select('reporting_to', ['' => __('Select Manager')] + $reportingManagers->toArray(), null, ['class' => 'form-control select', 'id' => 'reporting_to']) }}
                    </div>
                </div>

                <div class="col-md-6">
                     <x-mobile></x-mobile>
                </div>
            @endif

            <div class="col-md-6 mt-4">
                <div class="form-check form-switch custom-switch-v1 d-flex align-items-center justify-content-between">
                     <label class="form-check-label" for="password_switch">{{ __('Login Enabled') }}</label>
                     <input type="checkbox" name="password_switch" class="form-check-input input-primary pointer" value="on" id="password_switch" {{ company_setting('password_switch')=='on'?' checked ':'' }}>
                </div>
            </div>
            <div class="col-md-6 mt-4">
                 <div class="form-check form-switch custom-switch-v1 d-flex align-items-center justify-content-between">
                     <label class="form-check-label" for="is_active">{{ __('Active Status') }}</label>
                     <input type="checkbox" name="is_active" class="form-check-input input-primary pointer" value="on" id="is_active" checked>
                </div>
            </div>
            <div class="col-md-12 ps_div d-none">
                <div class="form-group">
                    {{Form::label('password',__('Password'),['class'=>'form-label'])}}
                    {{Form::password('password',array('class'=>'form-control','placeholder'=>__('Enter User Password'),'minlength'=>"6"))}}
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal-body border-top">
        <div class="row">
            <div class="col-md-12 mb-3">
                <h6 class="text-muted">{{ __('Access Control') }}</h6>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{Form::label('accessible_departments',__('Accessible Departments'),['class'=>'form-label'])}}
                    {{Form::select('accessible_departments[]',$departments,null,array('class'=>'form-control choices','id'=>'accessible_departments','multiple'=>'multiple'))}}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{Form::label('accessible_users',__('Accessible Users'),['class'=>'form-label'])}}
                    {{Form::select('accessible_users[]',$users,null,array('class'=>'form-control choices','id'=>'accessible_users','multiple'=>'multiple'))}}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        {{Form::submit(__('Create'),array('class'=>'btn  btn-primary'))}}
    </div>
    {{Form::close()}}
