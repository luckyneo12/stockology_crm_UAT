{{Form::model($user,array('route' => array('users.update', $user->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}
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
                        {{Form::label('email',__('Email'),['class'=>'form-label'])}}<x-required></x-required>
                        {{Form::email('email',null,array('class'=>'form-control','placeholder'=>__('Enter Customer Email'),'required'=>'required'))}}
                    </div>
                </div>
            @else
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
                        {{Form::label('role',__('Role'),['class'=>'form-label'])}}<x-required></x-required>
                        {{Form::select('role',$roles, !empty($user->roles->first()) ? $user->roles->first()->id : null, array('class'=>'form-control select','required'=>'required'))}}
                    </div>
                </div>
            @endif

            @if(!empty($departments) || !empty($teams))
                @php 
                    $deptId = $employee ? $employee->department_id : null; 
                @endphp
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                        {{ Form::select('department_id', ['' => __('Select Department')] + $departments->toArray(), $deptId, ['class' => 'form-control select', 'id' => 'department_id']) }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {{ Form::label('team_id', __('Team'), ['class' => 'form-label']) }}
                        {{ Form::select('team_id', ['' => __('Select Team')] + $teams->toArray(), $deptId, ['class' => 'form-control select', 'id' => 'team_id']) }}
                    </div>
                </div>
            @endif

            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('reporting_to', __('Reporting To'), ['class' => 'form-label']) }}
                    {{ Form::select('reporting_to', ['' => __('Select Manager')] + $reportingManagers->toArray(), $employee ? $employee->parent_id : null, ['class' => 'form-control select', 'id' => 'reporting_to']) }}
                </div>
            </div>

            <div class="col-md-6">
                <x-mobile value="{{ !empty($user->mobile_no) ? $user->mobile_no : null }}"></x-mobile>
            </div>

            <div class="col-md-12 mt-3 mb-3">
                 <div class="form-check form-switch custom-switch-v1 d-flex align-items-center justify-content-between border p-2 rounded">
                    <label class="form-check-label" for="is_active">{{ __('Active Status') }}</label>
                    <input type="checkbox" name="is_active" class="form-check-input input-primary pointer" value="on" id="is_active" {{ $user->is_disable == 0 ? 'checked' : '' }}>
                </div>
            </div>

            @if(Auth::user()->type != 'super admin')
                <div class="col-md-12">
                    <h6 class="text-muted mt-4 mb-3">{{ __('Functional Permissions') }}</h6>
                    <div class="row">
                        @if(module_is_active('Lead'))
                            <div class="col-md-6">
                                <div class="form-check form-switch custom-switch-v1 d-flex align-items-center justify-content-between mb-3">
                                    <label class="form-check-label" for="kyc_permission">{{ __('Post KYC Comments') }}</label>
                                    <input type="checkbox" name="kyc_permission" class="form-check-input" id="kyc_permission" {{ $user->isAbleTo('lead kyc comment') ? 'checked' : '' }}>
                                </div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <div class="form-check form-switch custom-switch-v1 d-flex align-items-center justify-content-between mb-3">
                                <label class="form-check-label" for="messenger_group_permission">{{ __('Create Messenger Groups') }}</label>
                                <input type="checkbox" name="messenger_group_permission" class="form-check-input" id="messenger_group_permission" {{ $user->isAbleTo('messenger group create') ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(Auth::user()->type != 'super admin' && module_is_active('Lead'))
                <div class="col-md-12">
                     <h6 class="text-muted mt-3 mb-3">{{ __('Data Visibility & Access') }}</h6>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {{Form::label('visibility_level',__('Data Visibility Level'),['class'=>'form-label'])}}
                        {{Form::select('visibility_level', ['self' => 'Self Only', 'team' => 'Team Leads', 'department' => 'Department Leads', 'all' => 'All CRM Data'], null, array('class'=>'form-control'))}}
                    </div>
                </div>

                <div class="col-md-6">
                     <div class="form-group">
                        {{Form::label('accessible_departments',__('Accessible Departments'),['class'=>'form-label'])}}
                        {{Form::select('accessible_departments[]',$departments,$user->accessible_departments,array('class'=>'form-control choices','id'=>'accessible_departments','multiple'=>'multiple'))}}
                    </div>
                </div>
                 <div class="col-md-6">
                    <div class="form-group">
                        {{Form::label('accessible_users',__('Accessible Users'),['class'=>'form-label'])}}
                        {{Form::select('accessible_users[]',$users,$user->accessible_users,array('class'=>'form-control choices','id'=>'accessible_users','multiple'=>'multiple'))}}
                    </div>
                </div>

                @if(!empty($pipelines))
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{__('Stage Name')}}</th>
                                        <th>{{__('Access Strategy')}}</th>
                                        <th>{{__('Can View')}}</th>
                                        <th>{{__('Can Move')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pipelines as $pipeline)
                                        <tr class="bg-light">
                                            <td colspan="4"><strong>{{ $pipeline->name }}</strong></td>
                                        </tr>
                                        @foreach($pipeline->leadStages as $stage)
                                            @php
                                                $hasOverride = $stagePermissions->has($stage->id);
                                                $perm = $hasOverride ? $stagePermissions->get($stage->id)->first() : clone ((object)['can_view' => 0, 'can_move' => 0]);
                                                
                                                // Check role defaults to show when inheriting
                                                $roleId = $user->roles->first()?->id;
                                                $rolePerm = (object)['can_view' => 0, 'can_move' => 0];
                                                if ($roleId) {
                                                    $rPerm = \Workdo\Lead\Entities\LeadStagePermission::where('stage_id', $stage->id)->where('role_id', $roleId)->first();
                                                    if ($rPerm) {
                                                        $rolePerm->can_view = $rPerm->can_view;
                                                        $rolePerm->can_move = $rPerm->can_move;
                                                    }
                                                }

                                                // If inheriting, show role's values
                                                if (!$hasOverride) {
                                                    $perm->can_view = $rolePerm->can_view;
                                                    $perm->can_move = $rolePerm->can_move;
                                                }
                                            @endphp
                                            <tr>
                                                <td>
                                                    {{ $stage->name }}
                                                    <input type="hidden" name="stage_ids[]" value="{{$stage->id}}">
                                                    <input type="hidden" id="role_view_{{$stage->id}}" value="{{ $rolePerm->can_view ? 1 : 0 }}">
                                                    <input type="hidden" id="role_move_{{$stage->id}}" value="{{ $rolePerm->can_move ? 1 : 0 }}">
                                                </td>
                                                <td>
                                                    <input type="hidden" name="stage_permissions[{{$stage->id}}][access_type]" id="access_type_{{$stage->id}}" value="{{ $hasOverride ? 'override' : 'inherit' }}">
                                                    <button type="button" class="btn btn-sm btn-{{ $hasOverride ? 'warning' : 'secondary' }} access-toggle-btn" data-stage-id="{{$stage->id}}" title="{{ $hasOverride ? __('Custom Override') : __('Inherit from Role') }}">
                                                        <i class="ti ti-{{ $hasOverride ? 'lock-open' : 'lock' }}" id="access_icon_{{$stage->id}}"></i> 
                                                        <span id="access_text_{{$stage->id}}">{{ $hasOverride ? __('Override') : __('Inherit') }}</span>
                                                    </button>
                                                </td>
                                                <td>
                                                    <div class="form-check form-switch custom-switch-v1">
                                                        <input type="checkbox" name="stage_permissions[{{$stage->id}}][can_view]" id="can_view_{{$stage->id}}" class="form-check-input" {{ $perm->can_view ? 'checked' : '' }} {{ !$hasOverride ? 'disabled' : '' }}>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-check form-switch custom-switch-v1">
                                                        <input type="checkbox" name="stage_permissions[{{$stage->id}}][can_move]" id="can_move_{{$stage->id}}" class="form-check-input" {{ $perm->can_move ? 'checked' : '' }} {{ !$hasOverride ? 'disabled' : '' }}>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                
                @if(!empty($webhookEndpoints) && count($webhookEndpoints) > 0)
                    <div class="col-md-12 mt-3">
                         <h6 class="text-muted mb-3">{{ __('Webhook Access') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{__('Webhook Endpoint')}}</th>
                                        <th>{{__('Can View Data')}}</th>
                                        <th>{{__('Can Edit Settings')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($webhookEndpoints as $endpoint)
                                        <tr>
                                            <td>{{ $endpoint->name }}</td>
                                            <td>
                                                <div class="form-check form-switch custom-switch-v1">
                                                    <input type="checkbox" name="webhook_permissions[{{$endpoint->id}}][can_view]" class="form-check-input" {{ in_array((string)$user->id, $endpoint->view_permissions ?? []) ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch custom-switch-v1">
                                                    <input type="checkbox" name="webhook_permissions[{{$endpoint->id}}][can_edit]" class="form-check-input" {{ in_array((string)$user->id, $endpoint->edit_permissions ?? []) ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        {{Form::submit(__('Update'),array('class'=>'btn  btn-primary'))}}
    </div>
{{Form::close()}}
<script>
    $(document).ready(function() {
        $('.access-toggle-btn').on('click', function() {
            var stageId = $(this).data('stage-id');
            var $typeInput = $('#access_type_' + stageId);
            var currentVal = $typeInput.val();
            var newVal = currentVal === 'inherit' ? 'override' : 'inherit';
            
            $typeInput.val(newVal);
            
            var $viewCb = $('#can_view_' + stageId);
            var $moveCb = $('#can_move_' + stageId);
            var $btn = $(this);
            var $icon = $('#access_icon_' + stageId);
            var $text = $('#access_text_' + stageId);
            
            if (newVal === 'inherit') {
                $btn.removeClass('btn-warning').addClass('btn-secondary');
                $btn.attr('title', '{{__("Inherit from Role")}}');
                $icon.removeClass('ti-lock-open').addClass('ti-lock');
                $text.text('{{__("Inherit")}}');
                
                $viewCb.prop('disabled', true);
                $moveCb.prop('disabled', true);
                
                // Show role's default value visually
                $viewCb.prop('checked', $('#role_view_' + stageId).val() == '1');
                $moveCb.prop('checked', $('#role_move_' + stageId).val() == '1');
            } else {
                $btn.removeClass('btn-secondary').addClass('btn-warning');
                $btn.attr('title', '{{__("Custom Override")}}');
                $icon.removeClass('ti-lock').addClass('ti-lock-open');
                $text.text('{{__("Override")}}');
                
                $viewCb.prop('disabled', false);
                $moveCb.prop('disabled', false);
            }
        });
    });
</script>
