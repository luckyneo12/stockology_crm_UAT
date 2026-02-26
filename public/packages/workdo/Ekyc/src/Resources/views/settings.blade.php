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
                                <a href="{{ route('ekyc.form.start') }}" target="_blank" class="btn btn-outline-primary">
                                    <i class="ti ti-external-link me-2"></i>{{ __('Preview Current KYC Form') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

