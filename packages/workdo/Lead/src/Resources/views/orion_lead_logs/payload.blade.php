<div class="modal-body" style="font-family: 'Outfit', sans-serif;">
    <div class="card bg-dark text-white border-0 shadow-none mb-0" style="border-radius: 16px; overflow: hidden;">
        <div class="card-body p-4">
            <!-- Header Section -->
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-secondary pb-3">
                <div>
                    <h5 class="text-white mb-1 fw-bold d-flex align-items-center">
                        <i class="ti ti-api text-warning me-2"></i> {{ __('Orion API Payload') }}
                    </h5>
                    <span class="text-muted text-xs">
                        {{ __('Client Code') }}: <strong class="text-white">{{ $log->client_code ?? 'N/A' }}</strong> &nbsp;|&nbsp; 
                        {{ __('API Type') }}: <span class="badge bg-secondary text-xs">{{ ucwords(str_replace('_', ' ', $log->api_type)) }}</span>
                    </span>
                </div>
                <div>
                    <span class="badge {{ $log->status == 'success' ? 'bg-success' : ($log->status == 'pending' ? 'bg-warning' : 'bg-danger') }} px-3 py-2 text-xs rounded-pill">
                        {{ ucfirst($log->status) }}
                    </span>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <ul class="nav nav-pills nav-fill bg-secondary bg-opacity-10 p-1 rounded-pill mb-3" id="payloadTab" role="tablist" style="border: 1px solid rgba(255,255,255,0.05);">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill text-white text-xs fw-semibold py-2" id="request-tab" data-bs-toggle="tab" data-bs-target="#requestPayload" type="button" role="tab" aria-controls="requestPayload" aria-selected="true">
                        <i class="ti ti-arrow-up-right me-1 text-warning"></i> {{ __('Request Payload') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill text-white text-xs fw-semibold py-2" id="response-tab" data-bs-toggle="tab" data-bs-target="#responsePayload" type="button" role="tab" aria-controls="responsePayload" aria-selected="false">
                        <i class="ti ti-arrow-down-left me-1 text-success"></i> {{ __('Response Payload') }}
                    </button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content mt-2" id="payloadTabContent">
                <!-- Request Tab -->
                <div class="tab-pane fade show active" id="requestPayload" role="tabpanel" aria-labelledby="request-tab">
                    <div class="bg-secondary bg-opacity-25 p-3 rounded-3 mt-1 position-relative" style="max-height: 350px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.05);">
                        <button type="button" class="btn position-absolute top-0 end-0 m-2" onclick="copyToClipboard(this)" style="z-index: 10; font-size: 10px; border-radius: 6px; padding: 4px 10px; border: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.4); color: #ffffff; transition: all 0.2s;" title="{{ __('Copy JSON') }}">
                            <i class="ti ti-copy me-1"></i>{{ __('Copy') }}
                        </button>
                        <pre class="text-white mb-0" style="white-space: pre-wrap; font-family: 'Fira Code', 'Courier New', Courier, monospace; font-size: 13px;"><code>{{ json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                    </div>
                </div>
                
                <!-- Response Tab -->
                <div class="tab-pane fade" id="responsePayload" role="tabpanel" aria-labelledby="response-tab">
                    <div class="bg-secondary bg-opacity-25 p-3 rounded-3 mt-1 position-relative" style="max-height: 350px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.05);">
                        <button type="button" class="btn position-absolute top-0 end-0 m-2" onclick="copyToClipboard(this)" style="z-index: 10; font-size: 10px; border-radius: 6px; padding: 4px 10px; border: 1px solid rgba(255,255,255,0.15); background: rgba(0,0,0,0.4); color: #ffffff; transition: all 0.2s;" title="{{ __('Copy JSON') }}">
                            <i class="ti ti-copy me-1"></i>{{ __('Copy') }}
                        </button>
                        <pre class="text-white mb-0" style="white-space: pre-wrap; font-family: 'Fira Code', 'Courier New', Courier, monospace; font-size: 13px;"><code>{{ json_encode($log->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                    </div>
                </div>
            </div>

            <!-- Error Details -->
            @if($log->error_reason)
                <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger mb-0 p-3 rounded-3 mt-4" style="border-left: 4px solid #dc3545 !important;">
                    <div class="d-flex">
                        <i class="ti ti-alert-triangle fs-4 me-2 mt-0.5"></i>
                        <div>
                            <span class="fw-bold d-block mb-1">{{ __('Error Details') }}</span>
                            <span class="text-xs">{{ $log->error_reason }}</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
<div class="modal-footer border-top-0 d-flex justify-content-end pb-4 px-4 pt-0">
    <button type="button" class="btn btn-sm btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ __('Close') }}</button>
</div>

<script>
    function copyToClipboard(btn) {
        const pre = btn.nextElementSibling;
        if (!pre) return;
        const code = pre.querySelector('code');
        if (!code) return;
        
        const text = code.innerText;
        navigator.clipboard.writeText(text).then(() => {
            const origHTML = btn.innerHTML;
            btn.innerHTML = '<i class="ti ti-check text-success me-1"></i>{{ __("Copied!") }}';
            btn.style.borderColor = '#198754';
            setTimeout(() => {
                btn.innerHTML = origHTML;
                btn.style.borderColor = 'rgba(255,255,255,0.15)';
            }, 2000);
        }).catch(err => {
            console.error('Clipboard copy failed', err);
        });
    }
</script>
