<div class="modal-body py-4 px-4" style="font-family: 'Outfit', sans-serif;">
    @php
        $badgeClass = 'bg-danger';
        $borderClass = 'border-danger-subtle';
        if ($log->status === 'converted') {
            $badgeClass = 'bg-light-success text-success border border-success-subtle';
            $borderClass = 'border-success-subtle';
        } elseif ($log->status === 'pending') {
            $badgeClass = 'bg-light-warning text-warning border border-warning-subtle';
            $borderClass = 'border-warning-subtle';
        } elseif ($log->status === 'skipped') {
            $badgeClass = 'bg-light-secondary text-secondary border border-secondary-subtle';
            $borderClass = 'border-secondary-subtle';
        } else {
            $badgeClass = 'bg-light-danger text-danger border border-danger-subtle';
            $borderClass = 'border-danger-subtle';
        }
    @endphp

    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom border-secondary border-opacity-10">
        <div>
            <h5 class="fw-bold text-dark mb-1">{{ __('Facebook Lead Details') }}</h5>
            <span class="text-muted text-xs">{{ __('Lead ID') }}: <code class="text-primary fw-semibold">{{ $log->leadgen_id }}</code></span>
        </div>
        <span class="badge {{ $badgeClass }} px-3 py-1.5 rounded-pill fw-bold text-xs" style="font-size: 0.75rem;">
            {{ ucfirst($log->status) }}
        </span>
    </div>
    
    <div class="mb-4">
        <label class="form-label fw-bold text-dark text-xs mb-2">{{ __('Extracted Form Payload') }}</label>
        <div class="bg-dark p-3 rounded-3" style="max-height: 350px; overflow-y: auto; box-shadow: inset 0 2px 8px rgba(0,0,0,0.15);">
            <pre class="text-light-green mb-0" style="white-space: pre-wrap; font-family: 'Fira Code', Consolas, Monaco, monospace; font-size: 0.78rem; color: #a5d6ff;"><code class="language-json">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
        </div>
    </div>

    @if($log->error_reason)
        @php
            $alertClass = $log->status === 'skipped' ? 'alert-info bg-light-info border border-info-subtle text-info' : 'alert-danger bg-light-danger border border-danger-subtle text-danger';
            $alertIcon = $log->status === 'skipped' ? 'ti ti-info-circle fs-5 mt-0.5' : 'ti ti-alert-triangle fs-5 mt-0.5 animate-pulse';
            $alertTitle = $log->status === 'skipped' ? __('Ingestion Info Details') : __('Ingestion Error Details');
        @endphp
        <div class="alert {{ $alertClass }} mb-0 p-3 rounded-3 d-flex gap-2">
            <i class="{{ $alertIcon }}"></i>
            <div>
                <span class="fw-bold d-block text-sm mb-0.5">{{ $alertTitle }}</span>
                <span class="text-xs fw-semibold">{{ $log->error_reason }}</span>
            </div>
        </div>
    @endif
</div>

<div class="modal-footer border-top-0 d-flex justify-content-end pb-4 px-4 pt-0">
    <button type="button" class="btn btn-sm btn-light-secondary border" data-bs-dismiss="modal" style="border-radius: 8px;">{{ __('Close') }}</button>
</div>

<style>
    .text-light-green {
        color: #8ade8a !important;
    }
    .bg-light-success {
        background-color: rgba(25, 135, 84, 0.08) !important;
    }
    .bg-light-warning {
        background-color: rgba(245, 158, 11, 0.08) !important;
    }
    .bg-light-secondary {
        background-color: rgba(108, 117, 125, 0.08) !important;
    }
    .bg-light-danger {
        background-color: rgba(220, 53, 69, 0.08) !important;
    }
    .bg-light-info {
        background-color: rgba(13, 202, 240, 0.08) !important;
    }
    .border-info-subtle {
        border-color: rgba(13, 202, 240, 0.15) !important;
    }
    .border-success-subtle {
        border-color: rgba(25, 135, 84, 0.15) !important;
    }
    .border-warning-subtle {
        border-color: rgba(245, 158, 11, 0.15) !important;
    }
    .border-danger-subtle {
        border-color: rgba(220, 53, 69, 0.15) !important;
    }
    .border-secondary-subtle {
        border-color: rgba(108, 117, 125, 0.15) !important;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    .animate-pulse {
        animation: pulse 2s infinite;
    }
</style>
