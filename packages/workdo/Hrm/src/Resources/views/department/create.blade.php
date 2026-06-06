@php
    $company_settings = getCompanyAllSetting();
@endphp

{{ Form::open(['url' => 'department', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group mb-3">
                {{ Form::label('branch_id', !empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::select('branch_id', $branch, null, ['class' => 'form-control select2-modal', 'placeholder' => __('Select '.(!empty($company_settings['hrm_branch_name']) ? $company_settings['hrm_branch_name'] : __('Branch'))), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group mb-3">
                {{ Form::label('parent_id', __('Parent (Sub Department)'), ['class' => 'form-label']) }}
                {{ Form::select('parent_id', $parents, $parent_id ?? null, ['class' => 'form-control select2-modal', 'placeholder' => __('Select Parent')]) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group mb-3">
                {{ Form::label('manager_id', __('Department Head / Manager'), ['class' => 'form-label']) }}
                {{ Form::select('manager_id', $employees, null, ['class' => 'form-control select2-modal', 'placeholder' => __('Select Department Head')]) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group mb-3">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required></x-required>
                {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Department Name')]) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group mb-3">
                {{ Form::label('logo', __('Logo'), ['class' => 'form-label']) }}
                <div class="custom-file-upload">
                    <label for="logo-upload-create" class="file-upload-label">
                        <i class="ti ti-upload"></i>
                        <span id="upload-filename">{{ __('Choose custom logo...') }}</span>
                    </label>
                    {{ Form::file('logo', ['class' => 'file-upload-input', 'id' => 'logo-upload-create', 'accept' => 'image/*']) }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Create'), ['class' => 'btn  btn-primary rounded-pill px-4']) }}
</div>
{{ Form::close() }}

<style>
    .modal-body .form-control {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0 !important;
        padding: 10px 14px !important;
        font-size: 0.9rem !important;
        transition: all 0.2s ease;
        height: auto !important;
    }
    .modal-body .form-control:focus {
        border-color: #18bf6b !important;
        box-shadow: 0 0 0 3px rgba(24, 191, 107, 0.15) !important;
    }
    .custom-file-upload {
        position: relative;
        display: block;
        border: 2px dashed #cbd5e1;
        border-radius: 10px;
        padding: 24px 20px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .custom-file-upload:hover {
        border-color: #18bf6b;
        background: #f0fdf4;
    }
    .file-upload-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        color: #64748b;
        font-size: 0.88rem;
        cursor: pointer;
        margin-bottom: 0;
    }
    .file-upload-label i {
        font-size: 1.8rem;
        color: #18bf6b;
        margin-bottom: 8px;
    }
    .file-upload-input {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    /* Select2 Modal Styling overrides */
    .select2-container--default .select2-selection--single {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0 !important;
        height: 42px !important;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px !important;
        padding-left: 14px !important;
    }
</style>

<script>
    document.getElementById('logo-upload-create').addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : "{{ __('Choose custom logo...') }}";
        document.getElementById('upload-filename').textContent = fileName;
    });
</script>
