<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <h5 class="mb-3">{{ __('Raw Payload') }}</h5>
            <div class="card bg-dark">
                <div class="card-body p-3">
                    <pre class="text-white mb-0" style="white-space: pre-wrap; font-size: 13px;"><code>{{ json_encode($webhookData->payload, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Close')}}" class="btn btn-light" data-bs-dismiss="modal">
</div>
