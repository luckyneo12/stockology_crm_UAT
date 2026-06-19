@extends('layouts.main')

@section('page-title')
    {{ __('WhatsApp Settings') }}
@endsection

@section('page-action')
    <div class="float-end">
        <a href="#" data-size="lg" data-url="{{ route('whatsapp-config.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Create WhatsApp Configuration') }}" data-title="{{ __('Create WhatsApp Configuration') }}" class="btn btn-sm btn-primary btn-icon-only" style="background: #25d366; border-color: #25d366; border-radius: 8px;">
            <i class="ti ti-plus text-white"></i>
        </a>
    </div>
@endsection

@section('content')
    <style>
        .wa-guide-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .wa-guide-title {
            color: #0f172a;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1rem;
        }
        .wa-guide-title i {
            color: #25d366;
            font-size: 1.4rem;
        }
        .wa-step-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }
        .wa-step-item {
            position: relative;
            padding-left: 2.5rem;
            margin-bottom: 0.75rem;
        }
        .wa-step-item:last-child {
            margin-bottom: 0;
        }
        .wa-step-number {
            position: absolute;
            left: 0;
            top: 2px;
            width: 22px;
            height: 22px;
            background: #25d366;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .wa-badge-code {
            background: #e2e8f0;
            color: #0f172a;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.85rem;
            border: 1px solid #cbd5e1;
        }
        .copy-url-btn {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
            color: #64748b;
            transition: all 0.2s;
            margin-left: 0.3rem;
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
        }
        .copy-url-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }
        .table-avatar-badge {
            background: rgba(37, 211, 102, 0.1);
            color: #25d366;
            font-weight: bold;
            padding: 0.4rem 0.75rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .table-badge-gray {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-family: monospace;
        }
    </style>

    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0">
                <div class="card-body">
                    @php
                        $hasMetaConfigs = $configs->contains(fn($c) => !$c->isQrBased());
                        $hasQrConfigs = $configs->contains(fn($c) => $c->isQrBased());
                    @endphp

                    <!-- Webhook Setup Guide for Meta Cloud API -->
                    @if($hasMetaConfigs)
                        <div class="wa-guide-card">
                            <h5 class="wa-guide-title">
                                <i class="ti ti-brand-whatsapp" style="color: #06b6d4;"></i>
                                {{ __('Meta Webhook Setup Instructions') }}
                            </h5>
                            <p class="text-muted mb-3">
                                {{ __('To enable real-time messaging, subscribe to the WhatsApp message webhook in your Facebook Developer Portal with these parameters:') }}
                            </p>
                            <ul class="wa-step-list">
                                <li class="wa-step-item">
                                    <span class="wa-step-number" style="background: #06b6d4;">1</span>
                                    <strong>{{ __('Callback URL') }}:</strong>
                                    <span class="wa-badge-code" id="webhook_callback_url">{{ env('WHATSAPP_NODE_PUBLIC_URL', 'http://your-node-domain.com') }}/webhook/whatsapp</span>
                                    <button type="button" class="copy-url-btn" id="btn_copy_callback" data-bs-toggle="tooltip" title="{{ __('Copy URL') }}">
                                        <i class="ti ti-copy"></i> {{ __('Copy') }}
                                    </button>
                                </li>
                                <li class="wa-step-item">
                                    <span class="wa-step-number" style="background: #06b6d4;">2</span>
                                    <strong>{{ __('Verify Token') }}:</strong>
                                    {{ __('Use the specific verify token shown inside your active configurations table below.') }}
                                </li>
                                <li class="wa-step-item">
                                    <span class="wa-step-number" style="background: #06b6d4;">3</span>
                                    <strong>{{ __('Webhook Subscriptions') }}:</strong>
                                    {{ __('Under WhatsApp > Configuration, subscribe to the') }} <span class="wa-badge-code">messages</span> {{ __('field.') }}
                                </li>
                            </ul>
                        </div>
                    @endif

                    <!-- QR Session Connection Instructions -->
                    @if($hasQrConfigs || $configs->isEmpty())
                        <div class="wa-guide-card" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-color: #bbf7d0;">
                            <h5 class="wa-guide-title" style="color: #14532d;">
                                <i class="ti ti-brand-whatsapp" style="color: #22c55e;"></i>
                                {{ __('QR Code Connection Instructions') }}
                            </h5>
                            <p class="text-muted mb-3" style="color: #166534 !important;">
                                {{ __('To link your WhatsApp account via QR Code scan:') }}
                            </p>
                            <ul class="wa-step-list" style="color: #166534;">
                                <li class="wa-step-item">
                                    <span class="wa-step-number" style="background: #22c55e;">1</span>
                                    {{ __('Click the') }} <strong>📱 QR</strong> {{ __('button next to the configuration in the table below.') }}
                                </li>
                                <li class="wa-step-item">
                                    <span class="wa-step-number" style="background: #22c55e;">2</span>
                                    {{ __('Wait for the QR code to load, then open WhatsApp on your phone, tap Linked Devices, and scan the QR code.') }}
                                </li>
                                <li class="wa-step-item">
                                    <span class="wa-step-number" style="background: #22c55e;">3</span>
                                    {{ __('Keep the modal open. Once connected, status will update to "Connected" and your team can start chatting immediately.') }}
                                </li>
                            </ul>
                        </div>
                    @endif

                    <!-- Configs Table -->
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle" id="whatsapp-configs" style="border-collapse: separate; border-spacing: 0 8px;">
                            <thead>
                                <tr class="text-muted" style="border-bottom: 2px solid #f1f5f9;">
                                    <th>{{ __('Account Name') }}</th>
                                    <th>{{ __('WhatsApp Phone') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Assigned Department') }}</th>
                                    <th>{{ __('Default Pipeline / Stage') }}</th>
                                    <th width="160px" class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($configs as $config)
                                    <tr style="background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.02); border-radius: 8px;">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="table-avatar-badge">
                                                    <i class="ti ti-brand-whatsapp"></i>
                                                </div>
                                                <div>
                                                    <span class="font-weight-bold d-block text-dark">{{ $config->name }}</span>
                                                    <small class="text-muted" style="font-size: 0.75rem;">ID: {{ $config->id }} ({{ $config->connection_type === 'qr_session' ? 'QR' : 'Meta' }})</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-secondary font-weight-bold">{{ $config->phone_number }}</span>
                                        </td>
                                        {{-- Session Status --}}
                                        <td>
                                            <span class="badge bg-{{ $config->session_status_color ?? 'secondary' }} text-white" 
                                                  id="status-badge-{{ $config->id }}"
                                                  style="border-radius: 6px; font-size: 0.75rem; padding: 0.35rem 0.7rem;">
                                                <span id="status-dot-{{ $config->id }}">
                                                    @if($config->session_status === 'connected') 🟢
                                                    @elseif($config->session_status === 'qr_pending') 🟡
                                                    @elseif($config->session_status === 'connecting') 🔵
                                                    @elseif($config->session_status === 'blocked') 🔴
                                                    @else ⚪
                                                    @endif
                                                </span>
                                                <span id="status-label-{{ $config->id }}">{{ $config->session_status_label ?? ucfirst($config->session_status ?? 'disconnected') }}</span>
                                            </span>
                                        </td>
                                        <td>
                                            @if($config->department)
                                                <span class="badge bg-primary text-white" style="border-radius: 6px; font-weight: 550; padding: 0.35rem 0.6rem;">
                                                    <i class="ti ti-users"></i> {{ $config->department->name }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary text-white" style="border-radius: 6px; font-weight: 550; padding: 0.35rem 0.6rem;">
                                                    {{ __('General / Unassigned') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="font-size: 0.85rem;">
                                                <span class="text-dark font-weight-bold">{{ $config->pipeline ? $config->pipeline->name : '-' }}</span>
                                                <i class="ti ti-chevron-right text-muted mx-1" style="font-size: 0.75rem;"></i>
                                                <span class="text-muted">{{ $config->stage ? $config->stage->name : '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1 align-items-center">
                                                {{-- QR Connect Button --}}
                                                @if($config->isQrBased())
                                                    <button type="button"
                                                        class="btn btn-sm btn-icon qr-connect-btn"
                                                        onclick="openQrModal({{ $config->id }}, '{{ addslashes($config->name) }}')"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Connect via QR Code') }}"
                                                        id="qr-btn-{{ $config->id }}"
                                                        style="width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; background: #e7f8f0; border: 1px solid #b2ebd0;">
                                                        <i class="ti ti-qrcode text-success"></i>
                                                    </button>
                                                @endif

                                                {{-- Edit button --}}
                                                <a href="#" class="btn btn-sm btn-icon btn-light"
                                                   data-url="{{ route('whatsapp-config.edit', $config->id) }}"
                                                   data-ajax-popup="true" data-size="lg"
                                                   data-bs-toggle="tooltip" title="{{ __('Edit Settings') }}"
                                                   data-title="{{ __('Edit WhatsApp Configuration') }}"
                                                   style="width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; background: #f1f5f9;">
                                                    <i class="ti ti-pencil text-secondary"></i>
                                                </a>

                                                {{-- Delete button --}}
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['whatsapp-config.destroy', $config->id], 'id' => 'delete-form-' . $config->id, 'class' => 'd-inline']) !!}
                                                    <a href="#" class="btn btn-sm btn-icon btn-light bs-pass-para show_confirm"
                                                       data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                       style="width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; background: #fff1f2;">
                                                        <i class="ti ti-trash text-danger"></i>
                                                    </a>
                                                {!! Form::close() !!}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="ti ti-brand-whatsapp text-light mb-3" style="font-size: 3.5rem;"></i>
                                                <h5>{{ __('No Accounts Configured') }}</h5>
                                                <p>{{ __('Get started by creating your first WhatsApp integration configuration.') }}</p>
                                                <a href="#" data-size="lg" data-url="{{ route('whatsapp-config.create') }}" data-ajax-popup="true" data-title="{{ __('Create WhatsApp Configuration') }}" class="btn btn-primary btn-sm mt-2" style="background: #25d366; border-color: #25d366; border-radius: 8px;">
                                                    <i class="ti ti-plus"></i> {{ __('Add WhatsApp Account') }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Copy Callback URL
        $(document).off('click', '#btn_copy_callback').on('click', '#btn_copy_callback', function(e) {
            e.preventDefault();
            var text = $('#webhook_callback_url').text();
            
            navigator.clipboard.writeText(text).then(function() {
                var btn = $('#btn_copy_callback');
                var orig = btn.html();
                btn.html('<i class="ti ti-check text-success"></i> Copied!').addClass('bg-success-light');
                if (typeof show_toastr === 'function') {
                    show_toastr('{{ __("Success") }}', '{{ __("Callback URL copied to clipboard!") }}', 'success');
                }
                setTimeout(function() {
                    btn.html(orig).removeClass('bg-success-light');
                }, 2000);
            }).catch(function() {
                // Fallback copy
                var temp = $('<input>');
                $('body').append(temp);
                temp.val(text).select();
                document.execCommand('copy');
                temp.remove();
                if (typeof show_toastr === 'function') {
                    show_toastr('{{ __("Success") }}', '{{ __("Callback URL copied to clipboard!") }}', 'success');
                }
            });
        });

        // Copy Table Tokens
        function copyTokenText(elementId, btn) {
            var text = $('#' + elementId).text();
            navigator.clipboard.writeText(text).then(function() {
                var $btn = $(btn);
                var orig = $btn.html();
                $btn.html('<i class="ti ti-check text-success" style="font-size: 0.75rem;"></i>');
                if (typeof show_toastr === 'function') {
                    show_toastr('{{ __("Success") }}', '{{ __("Verify Token copied to clipboard!") }}', 'success');
                }
                setTimeout(function() {
                    $btn.html(orig);
                }, 2000);
            }).catch(function() {
                var temp = $('<input>');
                $('body').append(temp);
                temp.val(text).select();
                document.execCommand('copy');
                temp.remove();
                if (typeof show_toastr === 'function') {
                    show_toastr('{{ __("Success") }}', '{{ __("Verify Token copied to clipboard!") }}', 'success');
                }
            });
        }
    </script>

{{-- ════════════ QR CONNECT MODAL ════════════ --}}
<div class="modal fade" id="qrConnectModal" tabindex="-1" aria-labelledby="qrConnectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 540px;">
        <div class="modal-content" style="border-radius: 18px; overflow: hidden; border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.18);">

            {{-- Modal Header --}}
            <div class="modal-header border-0 pb-0" style="background: linear-gradient(135deg, #128c7e, #25d366); padding: 20px 24px 16px;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;background:rgba(255,255,255,0.2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                        <i class="ti ti-brand-whatsapp text-white" style="font-size:1.5rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-white fw-bold mb-0" id="qrConnectModalLabel">Connect WhatsApp</h5>
                        <small class="text-white" style="opacity:0.85;" id="qr-config-name-label">Scan QR to link your account</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-0">

                {{-- Loading State --}}
                <div id="qr-state-loading" class="text-center py-5 px-4" style="display:none;">
                    <div class="mb-3">
                        <div class="spinner-border text-success" role="status" style="width:3rem;height:3rem;border-width:3px;"></div>
                    </div>
                    <h6 class="fw-bold text-dark">Starting WhatsApp Session...</h6>
                    <p class="text-muted small mb-0">This may take 10–30 seconds. Please wait.</p>
                    <small class="text-muted" style="font-size:0.72rem;">First launch may take longer while downloading Chromium</small>
                </div>

                {{-- QR State --}}
                <div id="qr-state-qr" style="display:none;">
                    <div class="d-flex align-items-start gap-0 p-3">
                        {{-- QR Image --}}
                        <div class="flex-shrink-0" style="background:#f8f9fa;border-radius:14px;padding:12px;border:2px solid #e9edef;">
                            <img id="qr-code-img" src="" alt="WhatsApp QR Code"
                                 style="width:220px;height:220px;display:block;border-radius:8px;" />
                            <p class="text-center text-muted mb-0 mt-2" style="font-size:0.7rem;">
                                🔄 QR refreshes automatically
                            </p>
                        </div>

                        {{-- Instructions --}}
                        <div class="flex-grow-1 ps-3 py-1">
                            <h6 class="fw-bold text-dark mb-3">How to connect:</h6>
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex align-items-start gap-2">
                                    <span style="width:24px;height:24px;background:#25d366;border-radius:50%;color:white;font-size:0.75rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">1</span>
                                    <span style="font-size:0.85rem;">Open <strong>WhatsApp</strong> on your phone</span>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <span style="width:24px;height:24px;background:#25d366;border-radius:50%;color:white;font-size:0.75rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">2</span>
                                    <span style="font-size:0.85rem;">Tap <strong>Settings</strong> → <strong>Linked Devices</strong></span>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <span style="width:24px;height:24px;background:#25d366;border-radius:50%;color:white;font-size:0.75rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">3</span>
                                    <span style="font-size:0.85rem;">Tap <strong>Link a Device</strong></span>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <span style="width:24px;height:24px;background:#25d366;border-radius:50%;color:white;font-size:0.75rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">4</span>
                                    <span style="font-size:0.85rem;">Point your camera at the QR code on the left</span>
                                </div>
                            </div>
                            <div class="mt-3 p-2 rounded" style="background:#fff8e1;border:1px solid #ffc107;">
                                <small class="text-warning-emphasis">
                                    ⚠️ Keep this window open while scanning. QR code expires in ~60 seconds.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Connected State --}}
                <div id="qr-state-connected" class="text-center py-5 px-4" style="display:none;">
                    <div class="mb-3" style="font-size:4rem;">✅</div>
                    <h5 class="fw-bold text-success">WhatsApp Connected!</h5>
                    <p class="text-muted">Your WhatsApp account is now linked to CRM.<br>Team members can now use this number.</p>
                    <a href="{{ route('whatsapp.chat.index') }}" class="btn btn-success mt-2" style="border-radius:10px;background:#25d366;border:none;">
                        <i class="ti ti-messages me-1"></i> Open Chat Inbox
                    </a>
                </div>

                {{-- Error State --}}
                <div id="qr-state-error" class="text-center py-5 px-4" style="display:none;">
                    <div class="mb-3" style="font-size:3rem;">⚠️</div>
                    <h5 class="fw-bold text-danger">Connection Failed</h5>
                    <p class="text-muted" id="qr-error-message">Could not reach WhatsApp service.</p>
                    <div class="alert alert-warning text-start" style="font-size:0.82rem;">
                        <strong>Make sure the Node.js service is running:</strong><br>
                        <code>cd whatsapp-node-service</code><br>
                        <code>node index.js</code>
                    </div>
                    <button class="btn btn-outline-success" onclick="startQrLoad(currentQrConfigId)" style="border-radius:10px;">
                        🔄 Try Again
                    </button>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer border-0 pt-0 px-4 pb-3" id="qr-footer-disconnect" style="display:none;">
                <button type="button"
                    class="btn btn-outline-danger btn-sm"
                    onclick="disconnectSession(currentQrConfigId)"
                    style="border-radius:8px;">
                    <i class="ti ti-plug-x me-1"></i> Disconnect Session
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="border-radius:8px;">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

<script>
// ─── QR Modal Logic ──────────────────────────────────────────────────────────
let currentQrConfigId = null;
let qrSocket = null;
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;
const NODE_PUBLIC_URL = '{{ env("WHATSAPP_NODE_PUBLIC_URL", "http://localhost:3001") }}';

function showQrState(state) {
    ['loading', 'qr', 'connected', 'error'].forEach(s => {
        document.getElementById('qr-state-' + s).style.display = 'none';
    });
    document.getElementById('qr-state-' + state).style.display = 'block';
    document.getElementById('qr-footer-disconnect').style.display =
        (state === 'connected') ? 'flex' : 'none';
}

function openQrModal(configId, configName) {
    currentQrConfigId = configId;
    document.getElementById('qr-config-name-label').innerText = configName;

    // Check if already connected (from status badge)
    const statusLabel = document.getElementById('status-label-' + configId)?.innerText?.trim();
    if (statusLabel === 'Connected') {
        showQrState('connected');
        new bootstrap.Modal(document.getElementById('qrConnectModal')).show();
        return;
    }

    showQrState('loading');
    new bootstrap.Modal(document.getElementById('qrConnectModal')).show();

    // Connect to Socket.io for real-time QR updates
    connectQrSocket(configId);

    // Fetch QR from Laravel (which calls Node.js)
    startQrLoad(configId);
}

function connectQrSocket(configId) {
    if (qrSocket) {
        try { qrSocket.disconnect(); } catch(e) {}
        qrSocket = null;
    }

    // Load socket.io client dynamically if not already loaded
    if (typeof io === 'undefined') {
        const script = document.createElement('script');
        script.src = NODE_PUBLIC_URL + '/socket.io/socket.io.js';
        script.onload = () => initSocket(configId);
        document.head.appendChild(script);
    } else {
        initSocket(configId);
    }
}

function initSocket(configId) {
    qrSocket = io(NODE_PUBLIC_URL, {
        transports: ['websocket', 'polling'],
        reconnectionAttempts: 3,
    });

    qrSocket.on('connect', function() {
        qrSocket.emit('join_config', { config_id: configId });
    });

    // Real-time QR update
    qrSocket.on('whatsapp_qr', function(data) {
        if (String(data.config_id) === String(configId) && data.qr_data_url) {
            document.getElementById('qr-code-img').src = data.qr_data_url;
            showQrState('qr');
        }
    });

    // Session connected!
    qrSocket.on('whatsapp_session_ready', function(data) {
        if (String(data.config_id) === String(configId)) {
            showQrState('connected');
            updateStatusBadge(configId, 'connected', 'Connected');
            if (typeof show_toastr === 'function') {
                show_toastr('Success', 'WhatsApp connected successfully! 🎉', 'success');
            }
        }
    });

    qrSocket.on('whatsapp_session_disconnected', function(data) {
        if (String(data.config_id) === String(configId)) {
            updateStatusBadge(configId, 'disconnected', 'Disconnected');
        }
    });
}

function startQrLoad(configId) {
    showQrState('loading');

    fetch('/whatsapp-config/' + configId + '/qr', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'connected') {
            showQrState('connected');
            updateStatusBadge(configId, 'connected', 'Connected');
        } else if (data.status === 'qr_pending' && data.qr_data_url) {
            document.getElementById('qr-code-img').src = data.qr_data_url;
            showQrState('qr');
            updateStatusBadge(configId, 'qr_pending', 'Scan QR Code');
        } else {
            document.getElementById('qr-error-message').innerText =
                data.message || 'Failed to generate QR code.';
            showQrState('error');
        }
    })
    .catch(err => {
        document.getElementById('qr-error-message').innerText =
            'Cannot reach WhatsApp service. Make sure Node.js is running on port 3001.';
        showQrState('error');
    });
}

function disconnectSession(configId) {
    if (!confirm('Are you sure you want to disconnect this WhatsApp session?')) return;

    fetch('/whatsapp-config/' + configId + '/disconnect', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        }
    })
    .then(res => res.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('qrConnectModal')).hide();
        updateStatusBadge(configId, 'disconnected', 'Disconnected');
        if (typeof show_toastr === 'function') {
            show_toastr('Success', 'WhatsApp session disconnected.', 'success');
        }
    });
}

function updateStatusBadge(configId, status, label) {
    const badge = document.getElementById('status-badge-' + configId);
    const labelEl = document.getElementById('status-label-' + configId);
    const dotEl = document.getElementById('status-dot-' + configId);
    if (!badge) return;

    const colorMap = {
        'connected': 'success', 'disconnected': 'secondary',
        'qr_pending': 'warning', 'connecting': 'info', 'blocked': 'danger'
    };
    const dotMap = {
        'connected': '🟢', 'disconnected': '⚪',
        'qr_pending': '🟡', 'connecting': '🔵', 'blocked': '🔴'
    };

    badge.className = 'badge bg-' + (colorMap[status] || 'secondary') + ' text-white';
    if (labelEl) labelEl.innerText = label;
    if (dotEl) dotEl.innerText = dotMap[status] || '⚪';
}

// Cleanup socket when modal closes
document.getElementById('qrConnectModal')?.addEventListener('hidden.bs.modal', function() {
    if (qrSocket) {
        try { qrSocket.disconnect(); } catch(e) {}
        qrSocket = null;
    }
});
</script>

@endsection

