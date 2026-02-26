@extends('layouts.main')

@section('page-title')
    {{__('eKYC Settings')}}
@endsection

@section('page-breadcrumb')
    {{__('eKYC')}},
    {{__('Settings')}}
@endsection

@section('content')
@php
    $admin_settings = getAdminAllSetting();
    $company_settings = getCompanyAllSetting();
@endphp

@push('css')
    <link href="{{  asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css')  }}" rel="stylesheet">
    <style>
        .note-editor.note-frame { border: 1px solid #ebf1f6; }
        .note-toolbar { background: #f8f9fa; }
    </style>
@endpush

<div class="row">
    <div class="col-xl-12">
        <!-- Tabbed Settings Interface -->
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="pills-api-tab" data-bs-toggle="pill" href="#pills-api" role="tab">
                    <i class="ti ti-api me-2"></i>{{ __('API Configuration') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pills-workflow-tab" data-bs-toggle="pill" href="#pills-workflow" role="tab">
                    <i class="ti ti-workflow me-2"></i>{{ __('Workflow Steps') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pills-otp-tab" data-bs-toggle="pill" href="#pills-otp" role="tab">
                    <i class="ti ti-message-circle me-2"></i>{{ __('OTP Settings') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pills-maintenance-tab" data-bs-toggle="pill" href="#pills-maintenance" role="tab">
                    <i class="ti ti-tool me-2"></i>{{ __('Maintenance Mode') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pills-ui-tab" data-bs-toggle="pill" href="#pills-ui" role="tab">
                    <i class="ti ti-palette me-2"></i>{{ __('UI Customization') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="pills-pdf-tab" data-bs-toggle="pill" href="#pills-pdf" role="tab">
                    <i class="ti ti-file-description me-2"></i>{{ __('PDF Management') }}
                </a>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            <!-- API Configuration Tab -->
            <div class="tab-pane fade show active" id="pills-api" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Digio API Configuration') }}</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('ekyc.settings.save') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="digio_client_id" class="form-label">{{ __('Digio Client ID') }}</label>
                                        <input type="text" name="digio_client_id" id="digio_client_id" class="form-control" placeholder="Enter Digio Client ID" value="{{ !empty($company_settings['digio_client_id']) ? $company_settings['digio_client_id'] : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="digio_client_secret" class="form-label">{{ __('Digio Client Secret') }}</label>
                                        <input type="password" name="digio_client_secret" id="digio_client_secret" class="form-control" placeholder="Enter Digio Client Secret" value="{{ !empty($company_settings['digio_client_secret']) ? $company_settings['digio_client_secret'] : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="digio_api_key" class="form-label">{{ __('Digio API Key') }}</label>
                                        <input type="text" name="digio_api_key" id="digio_api_key" class="form-control" placeholder="Enter Digio API Key" value="{{ !empty($company_settings['digio_api_key']) ? $company_settings['digio_api_key'] : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="digio_environment" class="form-label">{{ __('Environment') }}</label>
                                        <select name="digio_environment" id="digio_environment" class="form-control font-style">
                                            <option value="sandbox" {{ (!empty($company_settings['digio_environment']) && $company_settings['digio_environment'] == 'sandbox') ? 'selected' : '' }}>{{ __('Sandbox') }}</option>
                                            <option value="production" {{ (!empty($company_settings['digio_environment']) && $company_settings['digio_environment'] == 'production') ? 'selected' : '' }}>{{ __('Production') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Workflow Steps Tab -->
            <div class="tab-pane fade" id="pills-workflow" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('KYC Workflow Steps') }}</h5>
                        <small class="text-muted">{{ __('Enable or disable specific KYC verification steps') }}</small>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('ekyc.settings.save') }}">
                            @csrf
                            <div class="row">
                                @foreach ([
                                    'ekyc_verify_email' => 'Email Verification (Mandatory)',
                                    'ekyc_testing_mode' => 'Enable KYC Testing Mode (Demo Data)',
                                    'ekyc_pan' => 'PAN Verification', 
                                    'ekyc_aadhaar' => 'Aadhaar Verification', 
                                    'ekyc_selfie' => 'Selfie & Face Match', 
                                    'ekyc_bank' => 'Bank Account Verification', 
                                    'ekyc_segments' => 'Trading Segments Choice',
                                    'ekyc_personal_details' => 'Residency & Taxation (Personal Details)',
                                    'ekyc_nominee' => 'Nominee Optional Step',
                                    'ekyc_documents' => 'Document & Photo Upload',
                                    'ekyc_esign' => 'Aadhaar e-Sign Verification',
                                    'ekyc_video' => 'Video KYC (IPV)'
                                ] as $key => $label)
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="{{ $key }}" value="off">
                                            <input type="checkbox" class="form-check-input" name="{{ $key }}" id="{{ $key }}" {{ (!empty($company_settings[$key]) && $company_settings[$key] == 'on') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $key }}">{{ __($label) }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <hr>
                            <h6 class="mb-3">{{ __('Workflow Management') }}</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                     <h6 class="mb-3">{{ __('Manage Pipelines') }}</h6>
                                     <a href="{{ route('ekyc.pipelines.index') }}" class="btn btn-sm btn-primary">
                                         <i class="ti ti-settings me-1"></i> {{ __('Configure Pipelines') }}
                                     </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                     <h6 class="mb-3">{{ __('Manage Stages') }}</h6>
                                     <a href="{{ route('ekyc.stages.index') }}" class="btn btn-sm btn-primary">
                                         <i class="ti ti-settings me-1"></i> {{ __('Configure Stages') }}
                                     </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                     <h6 class="mb-3">{{ __('Manage Custom Fields') }}</h6>
                                     <a href="{{ route('ekyc.custom-fields.index') }}" class="btn btn-sm btn-primary">
                                         <i class="ti ti-settings me-1"></i> {{ __('Configure Custom Fields') }}
                                     </a>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- OTP Settings Tab -->
            <div class="tab-pane fade" id="pills-otp" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('OTP Configuration') }}</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('ekyc.settings.save') }}">
                            @csrf
                            
                            <!-- Testing Mode -->
                            <div class="alert alert-warning">
                                <i class="ti ti-alert-triangle me-2"></i>
                                <strong>{{ __('Testing Mode') }}</strong>
                                <p class="mb-0">{{ __('When testing mode is enabled, OTPs will not be sent via providers. Use the default OTP instead.') }}</p>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-3 mb-3">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="otp_testing_mode" value="off">
                                        <input type="checkbox" class="form-check-input" name="otp_testing_mode" id="otp_testing_mode" {{ (!empty($company_settings['otp_testing_mode']) && $company_settings['otp_testing_mode'] == 'on') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="otp_testing_mode">{{ __('SMS Testing Mode') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="otp_email_testing_mode" value="off">
                                        <input type="checkbox" class="form-check-input" name="otp_email_testing_mode" id="otp_email_testing_mode" {{ (!empty($company_settings['otp_email_testing_mode']) && $company_settings['otp_email_testing_mode'] == 'on') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="otp_email_testing_mode">{{ __('Email Testing Mode') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="otp_default_code" class="form-label">{{ __('Default OTP (Testing)') }}</label>
                                        <input type="text" name="otp_default_code" id="otp_default_code" class="form-control" placeholder="123456" value="{{ !empty($company_settings['otp_default_code']) ? $company_settings['otp_default_code'] : '123456' }}">
                                        <small class="text-muted">{{ __('This OTP will be used when testing mode is enabled') }}</small>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('SMS Provider Configuration') }}</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="otp_sms_provider" class="form-label">{{ __('SMS Provider') }}</label>
                                        <select name="otp_sms_provider" id="otp_sms_provider" class="form-control">
                                            <option value="twilio" {{ (!empty($company_settings['otp_sms_provider']) && $company_settings['otp_sms_provider'] == 'twilio') ? 'selected' : '' }}>Twilio</option>
                                            <option value="msg91" {{ (!empty($company_settings['otp_sms_provider']) && $company_settings['otp_sms_provider'] == 'msg91') ? 'selected' : '' }}>MSG91</option>
                                            <option value="fast2sms" {{ (!empty($company_settings['otp_sms_provider']) && $company_settings['otp_sms_provider'] == 'fast2sms') ? 'selected' : '' }}>Fast2SMS</option>
                                            <option value="custom" {{ (!empty($company_settings['otp_sms_provider']) && $company_settings['otp_sms_provider'] == 'custom') ? 'selected' : '' }}>Custom API</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="otp_sms_api_key" class="form-label">{{ __('SMS API Key') }}</label>
                                        <input type="text" name="otp_sms_api_key" id="otp_sms_api_key" class="form-control" value="{{ !empty($company_settings['otp_sms_api_key']) ? $company_settings['otp_sms_api_key'] : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="otp_sms_api_secret" class="form-label">{{ __('SMS API Secret') }}</label>
                                        <input type="password" name="otp_sms_api_secret" id="otp_sms_api_secret" class="form-control" value="{{ !empty($company_settings['otp_sms_api_secret']) ? $company_settings['otp_sms_api_secret'] : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="otp_sms_sender_id" class="form-label">{{ __('Sender ID') }}</label>
                                        <input type="text" name="otp_sms_sender_id" id="otp_sms_sender_id" class="form-control" value="{{ !empty($company_settings['otp_sms_sender_id']) ? $company_settings['otp_sms_sender_id'] : '' }}">
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('Email Provider Configuration') }}</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="otp_email_provider" class="form-label">{{ __('Email Provider') }}</label>
                                        <select name="otp_email_provider" id="otp_email_provider" class="form-control">
                                            <option value="smtp" {{ (!empty($company_settings['otp_email_provider']) && $company_settings['otp_email_provider'] == 'smtp') ? 'selected' : '' }}>SMTP</option>
                                            <option value="sendgrid" {{ (!empty($company_settings['otp_email_provider']) && $company_settings['otp_email_provider'] == 'sendgrid') ? 'selected' : '' }}>SendGrid</option>
                                            <option value="ses" {{ (!empty($company_settings['otp_email_provider']) && $company_settings['otp_email_provider'] == 'ses') ? 'selected' : '' }}>AWS SES</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="otp_email_from_email" class="form-label">{{ __('From Email') }}</label>
                                        <input type="email" name="otp_email_from_email" id="otp_email_from_email" class="form-control" value="{{ !empty($company_settings['otp_email_from_email']) ? $company_settings['otp_email_from_email'] : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="otp_email_from_name" class="form-label">{{ __('From Name') }}</label>
                                        <input type="text" name="otp_email_from_name" id="otp_email_from_name" class="form-control" value="{{ !empty($company_settings['otp_email_from_name']) ? $company_settings['otp_email_from_name'] : '' }}">
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('OTP Behavior') }}</h6>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="form-group">
                                        <label for="otp_length" class="form-label">{{ __('OTP Length') }}</label>
                                        <input type="number" name="otp_length" id="otp_length" class="form-control" min="4" max="8" value="{{ !empty($company_settings['otp_length']) ? $company_settings['otp_length'] : '6' }}">
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-group">
                                        <label for="otp_expiry_seconds" class="form-label">{{ __('Expiry (seconds)') }}</label>
                                        <input type="number" name="otp_expiry_seconds" id="otp_expiry_seconds" class="form-control" value="{{ !empty($company_settings['otp_expiry_seconds']) ? $company_settings['otp_expiry_seconds'] : '300' }}">
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-group">
                                        <label for="otp_max_attempts" class="form-label">{{ __('Max Attempts') }}</label>
                                        <input type="number" name="otp_max_attempts" id="otp_max_attempts" class="form-control" value="{{ !empty($company_settings['otp_max_attempts']) ? $company_settings['otp_max_attempts'] : '3' }}">
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="form-group">
                                        <label for="otp_resend_cooldown" class="form-label">{{ __('Resend Cooldown (s)') }}</label>
                                        <input type="number" name="otp_resend_cooldown" id="otp_resend_cooldown" class="form-control" value="{{ !empty($company_settings['otp_resend_cooldown']) ? $company_settings['otp_resend_cooldown'] : '60' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Maintenance Mode Tab -->
            <div class="tab-pane fade" id="pills-maintenance" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Maintenance Mode') }}</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('ekyc.settings.save') }}">
                            @csrf
                            
                            <div class="row mb-4">
                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="ekyc_maintenance_mode" value="off">
                                        <input type="checkbox" class="form-check-input" name="ekyc_maintenance_mode" id="ekyc_maintenance_mode" {{ (!empty($company_settings['ekyc_maintenance_mode']) && $company_settings['ekyc_maintenance_mode'] == 'on') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="ekyc_maintenance_mode">
                                            <strong>{{ __('Enable Maintenance Mode') }}</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">{{ __('When enabled, users will not be able to access the KYC form') }}</small>
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('Scheduled Maintenance') }}</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="ekyc_maintenance_start" class="form-label">{{ __('Start Date/Time') }}</label>
                                        <input type="datetime-local" name="ekyc_maintenance_start" id="ekyc_maintenance_start" class="form-control" value="{{ !empty($company_settings['ekyc_maintenance_start']) ? date('Y-m-d\TH:i', strtotime($company_settings['ekyc_maintenance_start'])) : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="ekyc_maintenance_end" class="form-label">{{ __('End Date/Time') }}</label>
                                        <input type="datetime-local" name="ekyc_maintenance_end" id="ekyc_maintenance_end" class="form-control" value="{{ !empty($company_settings['ekyc_maintenance_end']) ? date('Y-m-d\TH:i', strtotime($company_settings['ekyc_maintenance_end'])) : '' }}">
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('Maintenance Message') }}</h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="form-group">
                                        <label for="ekyc_maintenance_message" class="form-label">{{ __('Custom Message') }}</label>
                                        <textarea name="ekyc_maintenance_message" id="ekyc_maintenance_message" class="form-control" rows="3">{{ !empty($company_settings['ekyc_maintenance_message']) ? $company_settings['ekyc_maintenance_message'] : 'We are currently upgrading our KYC system. Please check back later.' }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('Whitelist') }}</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="ekyc_maintenance_whitelist_ips" class="form-label">{{ __('Whitelisted IPs') }}</label>
                                        <input type="text" name="ekyc_maintenance_whitelist_ips" id="ekyc_maintenance_whitelist_ips" class="form-control" placeholder="127.0.0.1, 192.168.1.1" value="{{ !empty($company_settings['ekyc_maintenance_whitelist_ips']) ? $company_settings['ekyc_maintenance_whitelist_ips'] : '' }}">
                                        <small class="text-muted">{{ __('Comma-separated IP addresses') }}</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="ekyc_maintenance_whitelist_users" class="form-label">{{ __('Whitelisted User IDs') }}</label>
                                        <input type="text" name="ekyc_maintenance_whitelist_users" id="ekyc_maintenance_whitelist_users" class="form-control" placeholder="1, 2, 3" value="{{ !empty($company_settings['ekyc_maintenance_whitelist_users']) ? $company_settings['ekyc_maintenance_whitelist_users'] : '' }}">
                                        <small class="text-muted">{{ __('Comma-separated user IDs') }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- UI Customization Tab -->
            <div class="tab-pane fade" id="pills-ui" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('UI/UX Customization') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            {{ __('Use the UI Builder to create custom KYC form designs without coding') }}
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <a href="{{ route('ekyc.admin.ui-builder') }}" class="btn btn-primary btn-lg">
                                    <i class="ti ti-palette me-2"></i>{{ __('Open UI Builder') }}
                                </a>
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-3">{{ __('Quick Preview') }}</h6>
                        <div class="row">
                            <div class="col-md-12">
                                <a href="{{ route('ekyc.form.start', ['c' => creatorId()]) }}" target="_blank" class="btn btn-outline-primary">
                                    <i class="ti ti-external-link me-2"></i>{{ __('Preview Current KYC Form') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- PDF Management Tab -->
            <div class="tab-pane fade" id="pills-pdf" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5>{{ __('PDF & Document Management') }}</h5>
                            <small class="text-muted">{{ __('Configure document templates for e-Sign and download') }}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" id="add-pdf-template">
                            <i class="ti ti-plus me-1"></i>{{ __('Add New Template') }}
                        </button>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('ekyc.settings.save') }}" id="pdf-templates-form" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="ekyc_pdf_templates" id="ekyc_pdf_templates_input">

                            <div class="card mb-4 border">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('Company Details for PDF Templates') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            {{Form::label('ekyc_company_name',__('Company Name'),['class'=>'form-label'])}}
                                            {{Form::text('ekyc_company_name',!empty($company_settings['ekyc_company_name']) ? $company_settings['ekyc_company_name'] : '',['class'=>'form-control','placeholder'=>__('Enter Company Name')])}}
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            {{Form::label('ekyc_company_address',__('Company Address'),['class'=>'form-label'])}}
                                            {{Form::textarea('ekyc_company_address',!empty($company_settings['ekyc_company_address']) ? $company_settings['ekyc_company_address'] : '',['class'=>'form-control','placeholder'=>__('Enter Company Address'), 'rows' => 1])}}
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            {{Form::label('ekyc_company_logo',__('Company Logo'),['class'=>'form-label'])}}
                                            <input type="file" class="form-control" name="ekyc_company_logo" id="ekyc_company_logo">
                                            @if(!empty($company_settings['ekyc_company_logo']))
                                                <div class="mt-2">
                                                    <img src="{{ get_file($company_settings['ekyc_company_logo']) }}" width="80px" class="border rounded bg-light">
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            {{Form::label('ekyc_company_auth_sign',__('Authorized Signature'),['class'=>'form-label'])}}
                                            <input type="file" class="form-control" name="ekyc_company_auth_sign" id="ekyc_company_auth_sign">
                                            @if(!empty($company_settings['ekyc_company_auth_sign']))
                                                <div class="mt-2">
                                                    <img src="{{ get_file($company_settings['ekyc_company_auth_sign']) }}" width="80px" class="border rounded bg-light">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-sm btn-primary">{{ __('Save Company Details') }}</button>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">{{ __('Document Templates') }}</h6>
                                <div>
                                    <a href="{{ route('ekyc.pdf-editor') }}" class="btn btn-sm btn-primary">
                                        <i class="ti ti-edit me-1"></i>{{ __('Open Dedicated PDF Editor') }}
                                    </a>
                                </div>
                            </div>
                            <p class="text-muted small mb-3">{{ __('Standard Templates: Master E-Sign, DDPI, AOF are available in the editor.') }}</p>
                            
                            <div id="pdf-templates-container">
                                @php
                                    $templates = !empty($company_settings['ekyc_pdf_templates']) ? json_decode($company_settings['ekyc_pdf_templates'], true) : [];
                                    if(empty($templates)) {
                                        $templates = [
                                            [
                                                'id' => uniqid(),
                                                'name' => 'Master E-Sign Registration Form',
                                                'is_enabled' => 'on',
                                                'require_esign' => 'on',
                                                'content' => '<style>
    .master-esign { font-family: sans-serif; color: #333; line-height: 1.4; font-size: 11px; }
    .page-box { padding: 20px; border: 1px solid #eee; margin-bottom: 20px; }
    .title-header { background: #f8f9fa; border: 1px solid #ddd; padding: 15px; text-align: center; }
    .title-header h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
    .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    .info-table th, .info-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    .info-table th { background: #f2f2f2; width: 25%; }
    .section-hl { background: #10b981; color: white; padding: 5px 10px; font-weight: bold; margin: 15px 0 10px 0; }
</style>
<div class="master-esign">
    <div class="page-box">
        <div style="text-align: center;">
            {company_logo}
            <h2>{company_name}</h2>
            <p>Member : National Stock Exchange of India Ltd.</p>
        </div>
        <div class="title-header">
            <h1 style="color: #333;">Client Registration Form</h1>
            <p>Individual / Sole Proprietor</p>
        </div>
        <table class="info-table">
            <tr><th>Form No.:</th><td>{application_no}</td><th>Date:</th><td>{current_date}</td></tr>
            <tr><th>Client Name:</th><td colspan="3"><b>{full_name}</b></td></tr>
            <tr><th>Client Code:</th><td>{client_code}</td><th>BOID:</th><td>{boid}</td></tr>
        </table>
    </div>
    <div class="page-break"></div>
    <div class="section-hl">KYC INDIVIDUAL APPLICATION FORM</div>
    <table class="info-table">
        <tr>
            <th>Name:</th><td>{full_name}</td>
            <td rowspan="4" style="text-align:center; width:120px; border: 1px solid #ddd;">{selfie}<br><small>Photo</small></td>
        </tr>
        <tr><th>Father\'s Name:</th><td>{father_name}</td></tr>
        <tr><th>Mother\'s Name:</th><td>{mother_name}</td></tr>
        <tr><th>DOB:</th><td>{dob}</td></tr>
        <tr><th>Gender:</th><td>{gender}</td><th>Marital Status:</th><td>{marital_status}</td></tr>
    </table>
    <div class="section-hl">IDENTITY & ADDRESS</div>
    <table class="info-table">
        <tr><th>Aadhaar:</th><td>{aadhaar_number}</td><th>PAN:</th><td>{pan_number}</td></tr>
        <tr><th>Address:</th><td colspan="3">{user_address}</td></tr>
        <tr><th>City/State:</th><td>{city} / {state}</td><th>PIN:</th><td>{pin_code}</td></tr>
    </table>
    <div class="section-hl">BANK & FINANCIAL DETAILS</div>
    <table class="info-table">
        <tr><th>A/C No.:</th><td>{bank_account_number}</td><th>IFSC:</th><td>{bank_ifsc}</td></tr>
        <tr><th>Bank Name:</th><td colspan="3">{bank_name}</td></tr>
        <tr><th>Income:</th><td>{annual_income}</td><th>Networth:</th><td>{networth}</td></tr>
    </table>
    <div style="margin-top: 20px; border: 1px solid #ddd; padding: 15px;">
        <p><b>DECLARATION:</b> I hereby declare that the details furnished above are true and correct to the best of my knowledge and belief and I undertake to inform you of any changes therein, immediately.</p>
        <div style="text-align: right; margin-top: 30px;">
            {signature}<br><b>(Applicant Signature)</b>
        </div>
    </div>
</div>'
                                            ],
                                            [
                                                'id' => uniqid(),
                                                'name' => 'Demat Debit and Pledge Instruction (DDPI)',
                                                'is_enabled' => 'on',
                                                'require_esign' => 'on',
                                                'content' => '<div style="text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px;">
    <div style="float: left;">{company_logo}</div>
    <div style="display: inline-block;">
        <h2 style="margin: 0;">DDPI FOR DEMAT ACCOUNT settlement</h2>
        <p style="margin: 5px 0;">{company_name}</p>
        <p style="font-size: 10px; margin: 0;">{company_address}</p>
    </div>
    <div style="clear: both;"></div>
</div>
<p>To,<br><b>{company_name}</b></p>
<p>I/We hold a beneficiary account no. (BO ID) with you. I/We hereby give this Demat Debit and Pledge Instruction (DDPI) to you for the following purposes:</p>
<table border="1" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
    <thead>
        <tr style="background: #f2f2f2;">
            <th style="padding: 10px; width: 50px;">Sr.</th>
            <th style="padding: 10px;">Purpose</th>
            <th style="padding: 10px; width: 150px;">Signature</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="padding: 10px; text-align: center;">1</td>
            <td style="padding: 10px;">Transfer of securities held in beneficial owner accounts of the client towards Stock Exchange related deliveries / settlement obligations arising out of trades executed by clients on the Stock Exchange through the same stock broker</td>
            <td style="padding: 10px; text-align: center;">{signature}</td>
        </tr>
        <tr>
            <td style="padding: 10px; text-align: center;">2</td>
            <td style="padding: 10px;">Pledging / re-pledging of securities in favor of the Trading Member (TM) / Clearing Member (CM) for the purpose of meeting margin requirements of the clients.</td>
            <td style="padding: 10px; text-align: center;">{signature}</td>
        </tr>
    </tbody>
</table>
<div style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; margin-bottom: 20px;">
    <h4 style="margin-top: 0;">Client Details:</h4>
    <p><b>Name:</b> {full_name}</p>
    <p><b>PAN:</b> {pan_number}</p>
    <p><b>Date:</b> {current_date}</p>
</div>
<div style="margin-top: 50px; text-align: right; border-top: 1px solid #eee; padding-top: 10px;">
    <div style="display: inline-block; text-align: center;">
        {signature}<br>
        <p style="border-top: 1px solid #333; width: 200px; margin-top: 5px;">(Client Signature)</p>
    </div>
</div>'
                                            ],
                                            [
                                                'id' => uniqid(),
                                                'name' => 'Account Opening Form (AOF)',
                                                'is_enabled' => 'on',
                                                'require_esign' => 'on',
                                                'content' => '<div style="text-align: center; border-bottom: 2px solid #10b981; padding-bottom: 10px; margin-bottom: 20px;">
    {company_logo}
    <h1 style="color: #10b981; margin: 10px 0;">KYC & ACCOUNT OPENING FORM</h1>
    <p style="margin: 0;">{company_name}</p>
</div>

<div style="margin-bottom: 30px;">
    <h3 style="background: #10b981; color: white; padding: 5px 10px;">1. PERSONAL DETAILS</h3>
    <table style="width: 100%; border: 1px solid #ddd; border-collapse: collapse;">
        <tr><td style="padding: 8px; border: 1px solid #ddd; width: 30%;"><b>Full Name</b></td><td style="padding: 8px; border: 1px solid #ddd;">{full_name}</td></tr>
        <tr><td style="padding: 8px; border: 1px solid #ddd;"><b>Father\'s Name</b></td><td style="padding: 8px; border: 1px solid #ddd;">{father_name}</td></tr>
        <tr><td style="padding: 8px; border: 1px solid #ddd;"><b>Mother\'s Name</b></td><td style="padding: 8px; border: 1px solid #ddd;">{mother_name}</td></tr>
        <tr><td style="padding: 8px; border: 1px solid #ddd;"><b>Date of Birth</b></td><td style="padding: 8px; border: 1px solid #ddd;">{dob}</td></tr>
        <tr><td style="padding: 8px; border: 1px solid #ddd;"><b>PAN</b></td><td style="padding: 8px; border: 1px solid #ddd;">{pan_number}</td></tr>
        <tr><td style="padding: 8px; border: 1px solid #ddd;"><b>Aadhaar</b></td><td style="padding: 8px; border: 1px solid #ddd;">{aadhaar_number}</td></tr>
        <tr><td style="padding: 8px; border: 1px solid #ddd;"><b>Contact</b></td><td style="padding: 8px; border: 1px solid #ddd;">Mob: {mobile_number} | Email: {email}</td></tr>
    </table>
</div>

<div style="margin-bottom: 30px;">
    <h3 style="background: #10b981; color: white; padding: 5px 10px;">2. BANK ACCOUNT DETAILS</h3>
    <table style="width: 100%; border: 1px solid #ddd; border-collapse: collapse;">
        <tr><td style="padding: 8px; border: 1px solid #ddd; width: 30%;"><b>Account Number</b></td><td style="padding: 8px; border: 1px solid #ddd;">{bank_account_number}</td></tr>
        <tr><td style="padding: 8px; border: 1px solid #ddd;"><b>IFSC Code</b></td><td style="padding: 8px; border: 1px solid #ddd;">{bank_ifsc}</td></tr>
    </table>
</div>

<div style="margin-top: 30px; border: 1px solid #ddd; padding: 15px;">
    <p><b>DECLARATION:</b> I hereby declare that the details furnished above are true and correct to the best of my knowledge and belief and I undertake to inform you of any changes therein, immediately.</p>
    
    <div style="margin-top: 40px; display: table; width: 100%;">
        <div style="display: table-cell; vertical-align: bottom;">
            <div style="border: 1px dashed #ccc; padding: 5px; display: inline-block;">{selfie}</div>
            <p style="margin-top: 5px;">(Applicant Photograph)</p>
        </div>
        <div style="display: table-cell; text-align: right; vertical-align: bottom;">
            {signature}<br>
            <p style="border-top: 1px solid #333; display: inline-block; width: 200px; padding-top: 5px;">(Applicant Signature)</p>
            <p><b>Date:</b> {current_date}</p>
        </div>
    </div>
</div>

<div style="margin-top: 30px; background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px;">
    <p style="margin-top: 0;"><b>FOR OFFICE USE ONLY:</b></p>
    {auth_sign}<br>
    <p style="margin-bottom: 0;"><b>Authorized Signatory:</b> {company_name}</p>
</div>'
                                            ]
                                        ];
                                    }
                                @endphp

                                @foreach($templates as $index => $template)
                                    <div class="pdf-template-block card mb-4 border" data-id="{{ $template['id'] ?? uniqid() }}">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                            <h6 class="mb-0 text-primary"><i class="ti ti-file-text me-2"></i>{{ $template['name'] }}</h6>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-link text-danger remove-template" title="Delete"><i class="ti ti-trash"></i></button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-9 mb-3">
                                                    <label class="form-label">{{ __('Template Name') }}</label>
                                                    <input type="text" class="form-control template-name" value="{{ $template['name'] ?? '' }}" placeholder="e.g. Account Opening Form">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">{{ __('Insert Placeholder') }}</label>
                                                    <select class="form-control insert-placeholder">
                                                        <option value="">{{ __('Select...') }}</option>
                                                        <optgroup label="User Data">
                                                            <option value="{full_name}">Full Name</option>
                                                            <option value="{pan_number}">PAN Number</option>
                                                            <option value="{aadhaar_number}">Aadhaar Number</option>
                                                            <option value="{dob}">DoB</option>
                                                            <option value="{signature}">User Signature</option>
                                                            <option value="{selfie}">User Selfie</option>
                                                        </optgroup>
                                                        <optgroup label="Company Data">
                                                            <option value="{company_name}">Company Name</option>
                                                            <option value="{company_address}">Company Address</option>
                                                            <option value="{company_logo}">Company Logo</option>
                                                            <option value="{auth_sign}">Auth Signature</option>
                                                        </optgroup>
                                                        <optgroup label="Other">
                                                            <option value="{current_date}">Current Date</option>
                                                            <option value="<div class='page-break'></div>">Page Break</option>
                                                        </optgroup>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-check form-switch mt-2">
                                                        <input type="checkbox" class="form-check-input template-enabled" {{ ($template['is_enabled'] ?? 'on') == 'on' ? 'checked' : '' }}>
                                                        <label class="form-check-label">{{ __('Active') }}</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-check form-switch mt-2">
                                                        <input type="checkbox" class="form-check-input template-esign" {{ ($template['require_esign'] ?? 'on') == 'on' ? 'checked' : '' }}>
                                                        <label class="form-check-label">{{ __('Require e-Sign') }}</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">{{ __('Document Content') }}</label>
                                                    <textarea class="form-control summernote template-content" rows="10">{{ $template['content'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary px-5">{{ __('Save PDF Configuration') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
<script>
    $(document).ready(function() {
        initSummernote();

        $('#add-pdf-template').on('click', function() {
            const id = 'tmp_' + Date.now();
            const html = `
                <div class="pdf-template-block card mb-4 border" data-id="${id}">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <h6 class="mb-0 text-primary"><i class="ti ti-file-text me-2"></i>New Template</h6>
                        <div>
                            <button type="button" class="btn btn-sm btn-link text-danger remove-template" title="Delete"><i class="ti ti-trash"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <label class="form-label">{{ __('Template Name') }}</label>
                                <input type="text" class="form-control template-name" placeholder="e.g. KYC Form">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">{{ __('Insert Placeholder') }}</label>
                                <select class="form-control insert-placeholder">
                                    <option value="">{{ __('Select...') }}</option>
                                    <optgroup label="User Data">
                                        <option value="{full_name}">Full Name</option>
                                        <option value="{pan_number}">PAN Number</option>
                                        <option value="{aadhaar_number}">Aadhaar Number</option>
                                        <option value="{dob}">DoB</option>
                                        <option value="{signature}">User Signature</option>
                                        <option value="{selfie}">User Selfie</option>
                                    </optgroup>
                                    <optgroup label="Company Data">
                                        <option value="{company_name}">Company Name</option>
                                        <option value="{company_address}">Company Address</option>
                                        <option value="{company_logo}">Company Logo</option>
                                        <option value="{auth_sign}">Auth Signature</option>
                                    </optgroup>
                                    <optgroup label="Other">
                                        <option value="{current_date}">Current Date</option>
                                        <option value="<div class='page-break'></div>">Page Break</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" class="form-check-input template-enabled" checked>
                                    <label class="form-check-label">{{ __('Active') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" class="form-check-input template-esign" checked>
                                    <label class="form-check-label">{{ __('Require e-Sign') }}</label>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">{{ __('Document Content') }}</label>
                                <textarea class="form-control summernote template-content" rows="10"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#pdf-templates-container').prepend(html);
            initSummernote();
        });

        $(document).on('click', '.remove-template', function() {
            if(confirm('Are you sure you want to delete this template?')) {
                $(this).closest('.pdf-template-block').fadeOut(function() { $(this).remove(); });
            }
        });

        $(document).on('change', '.insert-placeholder', function() {
            const val = $(this).val();
            if (val) {
                const textarea = $(this).closest('.pdf-template-block').find('.summernote');
                textarea.summernote('editor.insertText', val);
                $(this).val('');
            }
        });

        $('#pdf-templates-form').on('submit', function(e) {
            const templates = [];
            $('.pdf-template-block').each(function() {
                templates.push({
                    id: $(this).data('id'),
                    name: $(this).find('.template-name').val(),
                    is_enabled: $(this).find('.template-enabled').is(':checked') ? 'on' : 'off',
                    require_esign: $(this).find('.template-esign').is(':checked') ? 'on' : 'off',
                    content: $(this).find('.template-content').summernote('code')
                });
            });
            $('#ekyc_pdf_templates_input').val(JSON.stringify(templates));
        });

        function initSummernote() {
            $('.summernote').summernote({
                height: 300,
                dialogsInBody: true,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        }
    });
</script>
@endpush

