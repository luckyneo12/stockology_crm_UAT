@permission('lead manage')
<div class="card" id="lead-sidenav">
    {{ Form::open(['route' => 'lead.setting.store', 'id' => 'lead_setting_store']) }}
    <div class="card-header p-3">
        <h5 class="">{{ __('Lead & Click to Call Settings') }}</h5>
        <small class="text-muted">{{ __('Configure the API calling URLs for different departments.') }}</small>
    </div>
    <div class="card-body pb-0 p-3">

        <div class="row">
            <div class="col-md-12 mb-3">
                <h6 class="mb-2">{{ __('Calling API URL Configuration') }}</h6>
                <small class="text-muted d-block mb-3">
                    {{ __('URL format should be like: https://192.168.0.50/call.php') }}<br>
                    {{ __('The system will automatically append: ?ext=[USER_EXTENSION]&num=[LEAD_PHONE] if no placeholders are used.') }}<br>
                    {{ __('You can use placeholders for custom parameter names: {ext} for Extension and {num} for Phone Number. E.g., https://.../call.php?exten={ext}&number={num}') }}
                </small>
            </div>

            <div class="col-md-6 mb-3">
                <div class="form-group">
                    {{ Form::label('lead_default_calling_url', __('Default Calling API URL (Fallback)'), ['class' => 'form-label']) }}
                    {{ Form::text('lead_default_calling_url', !empty($settings['lead_default_calling_url']) ? $settings['lead_default_calling_url'] : '', ['class' => 'form-control', 'placeholder' => 'https://192.168.0.50/call.php']) }}
                </div>
            </div>
        </div>

        @if(module_is_active('Hrm') && count($departments) > 0)
            <hr>
            <div class="row mt-3">
                <div class="col-md-12 mb-3">
                    <h6 class="mb-2">{{ __('Department Access & URLs') }}</h6>
                    <small
                        class="text-muted d-block mb-3">{{ __('Enable Click to Call access for specific departments, and optionally override their base URL.') }}</small>
                </div>

                @foreach($departments as $dept)
                    <div class="col-md-6 mb-3 border-bottom pb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label mb-0">{{ $dept->name }} {{ __('Access') }}</label>
                            <div class="form-check form-switch custom-switch-v1 float-end">
                                <input type="checkbox" name="click_to_call_enabled_dept_{{ $dept->id }}"
                                    class="form-check-input input-primary" id="click_to_call_enabled_dept_{{ $dept->id }}" {{ (isset($settings['click_to_call_enabled_dept_' . $dept->id]) && $settings['click_to_call_enabled_dept_' . $dept->id] == 'on') ? 'checked' : '' }}>
                                <label class="form-check-label" for="click_to_call_enabled_dept_{{ $dept->id }}"></label>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            {{ Form::label('dept_calling_url_' . $dept->id, __('Override API URL'), ['class' => 'form-label text-muted', 'style' => 'font-size: 12px;']) }}
                            {{ Form::text('dept_calling_url_' . $dept->id, !empty($settings['dept_calling_url_' . $dept->id]) ? $settings['dept_calling_url_' . $dept->id] : '', ['class' => 'form-control form-control-sm', 'placeholder' => 'Leave blank to use default Base URL']) }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    <div class="card-footer text-end p-3">
        <input class="btn btn-primary lead_setting_btn" type="button" value="{{ __('Save Changes') }}">
    </div>
    {{ Form::close() }}
</div>

<script>
    $(".lead_setting_btn").click(function () {
        $("#lead_setting_store").submit();
    });
</script>
@endpermission