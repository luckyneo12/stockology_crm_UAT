{{ Form::model($config, array('route' => array('whatsapp-config.update', $config->id), 'method' => 'PUT', 'class' => 'needs-validation', 'novalidate')) }}
<style>
    .wa-setup-card {
        background: #ffffff;
        border: 1px solid #e3e9ef;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: all 0.25s ease;
    }
    .wa-setup-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
    .wa-card-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
        border-bottom: 1px dashed #e2e8f0;
        padding-bottom: 0.75rem;
    }
    .wa-card-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: rgba(37, 211, 102, 0.1);
        color: #25d366;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .wa-card-icon.meta-icon {
        background: rgba(6, 182, 212, 0.1);
        color: #06b6d4;
    }
    .wa-card-icon.routing-icon {
        background: rgba(99, 102, 241, 0.1);
        color: #6366f1;
    }
    .wa-card-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .wa-card-description {
        font-size: 0.825rem;
        color: #64748b;
        margin-top: 0.1rem;
    }
    .wa-form-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 0.4rem;
    }
    .wa-form-desc {
        font-size: 0.775rem;
        color: #64748b;
        margin-top: 0.3rem;
        line-height: 1.35;
    }
    .wa-copy-btn {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-left: none;
        color: #475569;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .wa-copy-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
</style>

<div class="modal-body py-3">
    <div class="row">
        <!-- Section 1: General Details -->
        <div class="col-12">
            <div class="wa-setup-card">
                <div class="wa-card-header">
                    <div class="wa-card-icon">
                        <i class="ti ti-brand-whatsapp"></i>
                    </div>
                    <div>
                        <h6 class="wa-card-title">{{ __('1. General Account Details') }}</h6>
                        <span class="wa-card-description">{{ __('Set up your profile name and contact number.') }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6 mb-3">
                        {{ Form::label('name', __('Configuration Name'), ['class' => 'wa-form-label']) }}
                        {{ Form::text('name', null, array('class' => 'form-control', 'required' => 'required', 'placeholder' => __('e.g. Primary WhatsApp Support'))) }}
                        <div class="wa-form-desc">{{ __('A friendly label to distinguish this account within the CRM settings.') }}</div>
                    </div>
                    <div class="form-group col-md-6 mb-3">
                        {{ Form::label('phone_number', __('WhatsApp Phone Number'), ['class' => 'wa-form-label']) }}
                        {{ Form::text('phone_number', null, array('class' => 'form-control', 'required' => 'required', 'placeholder' => __('e.g. +1234567890'))) }}
                        <div class="wa-form-desc">{{ __('The verified WhatsApp telephone number associated with your account.') }}</div>
                    </div>
                    <div class="form-group col-md-12 mb-3">
                        {{ Form::label('connection_type', __('Connection Type'), ['class' => 'wa-form-label']) }}
                        {{ Form::select('connection_type', ['meta_cloud' => __('Meta Cloud API (Official)'), 'qr_session' => __('QR Code Session (whatsapp-web.js)')], null, array('class' => 'form-control', 'required' => 'required', 'id' => 'wa_connection_type')) }}
                        <div class="wa-form-desc">{{ __('Select how you want to connect this number to the CRM.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Meta API Credentials -->
        <div class="col-12" id="meta_credentials_section">
            <div class="wa-setup-card">
                <div class="wa-card-header">
                    <div class="wa-card-icon meta-icon">
                        <i class="ti ti-key"></i>
                    </div>
                    <div>
                        <h6 class="wa-card-title">{{ __('2. Meta API Credentials') }}</h6>
                        <span class="wa-card-description">{{ __('Provide authorization details from the Meta Developer Dashboard.') }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6 mb-3">
                        {{ Form::label('phone_number_id', __('Phone Number ID'), ['class' => 'wa-form-label']) }}
                        {{ Form::text('phone_number_id', null, array('class' => 'form-control', 'required' => 'required', 'placeholder' => __('e.g. 109283746562543'))) }}
                        <div class="wa-form-desc">{{ __('Found in Meta App Settings > WhatsApp > API Setup.') }}</div>
                    </div>
                    <div class="form-group col-md-6 mb-3">
                        {{ Form::label('business_account_id', __('WhatsApp Business Account ID'), ['class' => 'wa-form-label']) }}
                        {{ Form::text('business_account_id', null, array('class' => 'form-control', 'required' => 'required', 'placeholder' => __('e.g. 987654321012345'))) }}
                        <div class="wa-form-desc">{{ __('Found in Meta App Settings > WhatsApp > API Setup.') }}</div>
                    </div>
                    <div class="form-group col-12 mb-0">
                        {{ Form::label('access_token', __('Permanent Access Token'), ['class' => 'wa-form-label']) }}
                        {{ Form::textarea('access_token', null, array('class' => 'form-control', 'required' => 'required', 'rows' => 3, 'placeholder' => __('EAAG...'))) }}
                        <div class="wa-form-desc">{{ __('Your permanent Meta System User token. Ensure it has the whatsapp_business_messaging permission granted.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Routing & Lead Automation -->
        <div class="col-12">
            <div class="wa-setup-card">
                <div class="wa-card-header">
                    <div class="wa-card-icon routing-icon">
                        <i class="ti ti-git-branch"></i>
                    </div>
                    <div>
                        <h6 class="wa-card-title">{{ __('3. Lead & Routing Automation') }}</h6>
                        <span class="wa-card-description">{{ __('Configure webhook tokens and rules for new lead creation.') }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6 mb-3" id="verify_token_container">
                        {{ Form::label('verify_token', __('Webhook Verify Token'), ['class' => 'wa-form-label']) }}
                        <div class="input-group">
                            {{ Form::text('verify_token', null, array('class' => 'form-control', 'required' => 'required', 'id' => 'wa_verify_token_input')) }}
                            <button class="btn wa-copy-btn px-3" type="button" id="wa_copy_token_btn" data-bs-toggle="tooltip" title="{{ __('Copy Verify Token') }}">
                                <i class="ti ti-copy"></i>
                            </button>
                        </div>
                        <div class="wa-form-desc">{{ __('Enter this exact value in the "Verify Token" field inside the Facebook Webhooks panel.') }}</div>
                    </div>
                    <div class="form-group col-md-6 mb-3">
                        {{ Form::label('department_id', __('Assign to Department (Lead routing)'), ['class' => 'wa-form-label']) }}
                        {{ Form::select('department_id', [null => __('General / Unassigned')] + $departments, null, array('class' => 'form-control')) }}
                        <div class="wa-form-desc">{{ __('New incoming chats will instantly notify and assign the Head of this Department.') }}</div>
                    </div>
                    <div class="form-group col-md-6 mb-0">
                        {{ Form::label('pipeline_id', __('Default Lead Pipeline'), ['class' => 'wa-form-label']) }}
                        {{ Form::select('pipeline_id', $pipelines, null, array('class' => 'form-control', 'id' => 'pipeline_id', 'required' => 'required', 'placeholder' => __('Select Pipeline'))) }}
                        <div class="wa-form-desc">{{ __('The target workflow pipeline where auto-created leads are placed.') }}</div>
                    </div>
                    <div class="form-group col-md-6 mb-0">
                        {{ Form::label('stage_id', __('Default Lead Stage'), ['class' => 'wa-form-label']) }}
                        {{ Form::select('stage_id', $stages, null, array('class' => 'form-control', 'id' => 'stage_id', 'required' => 'required', 'placeholder' => __('Select Stage'))) }}
                        <div class="wa-form-desc">{{ __('The initial pipeline stage/column where new leads are spawned.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer border-top-0 pt-0">
    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 8px;">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px; background: #25d366; border-color: #25d366;">{{ __('Save Changes') }}</button>
</div>
{{ Form::close() }}

<script>
    // Copy webhook verify token logic
    $(document).off('click', '#wa_copy_token_btn').on('click', '#wa_copy_token_btn', function(e) {
        e.preventDefault();
        var copyText = document.getElementById("wa_verify_token_input");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        
        try {
            navigator.clipboard.writeText(copyText.value).then(function() {
                showSuccessFeedback();
            }).catch(function() {
                fallbackCopy(copyText);
            });
        } catch (err) {
            fallbackCopy(copyText);
        }
    });

    function fallbackCopy(element) {
        document.execCommand("copy");
        showSuccessFeedback();
    }

    function showSuccessFeedback() {
        var btn = $('#wa_copy_token_btn');
        var originalHtml = btn.html();
        btn.html('<i class="ti ti-check text-success"></i>').addClass('bg-success-light');
        
        // Use CRM's built-in show_toastr if it exists, otherwise silent fallback
        if (typeof show_toastr === 'function') {
            show_toastr('{{ __("Success") }}', '{{ __("Verify Token copied to clipboard!") }}', 'success');
        }
        
        setTimeout(function() {
            btn.html(originalHtml).removeClass('bg-success-light');
        }, 2000);
    }

    // Dynamic stage dropdown resolver
    $(document).off('change', '#pipeline_id').on('change', '#pipeline_id', function() {
        var pipeline_id = $(this).val();
        if (pipeline_id) {
            $.ajax({
                url: '{{ route('whatsapp-config.stages') }}',
                data: {
                    pipeline_id: pipeline_id,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                success: function(data) {
                    var stage_select = $('#stage_id');
                    stage_select.empty();
                    stage_select.append('<option value="">{{ __('Select Stage') }}</option>');
                    $.each(data, function(key, val) {
                        stage_select.append('<option value="' + key + '">' + val + '</option>');
                    });
                }
            });
        } else {
            $('#stage_id').empty().append('<option value="">{{ __('Select Stage') }}</option>');
        }
    });

    // Connection type dynamic fields toggler
    $(document).off('change', '#wa_connection_type').on('change', '#wa_connection_type', function() {
        var type = $(this).val();
        if (type === 'qr_session') {
            $('#meta_credentials_section').slideUp();
            $('#meta_credentials_section').find('input, textarea').removeAttr('required');
            $('#verify_token_container').slideUp();
            $('#verify_token_container').find('input').removeAttr('required');
        } else {
            $('#meta_credentials_section').slideDown();
            $('#meta_credentials_section').find('input, textarea').attr('required', 'required');
            $('#verify_token_container').slideDown();
            $('#verify_token_container').find('input').attr('required', 'required');
        }
    });
    // Trigger on load
    setTimeout(function() {
        $('#wa_connection_type').trigger('change');
    }, 100);
</script>
