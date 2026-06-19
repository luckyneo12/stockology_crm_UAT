{{ Form::open(array('url' => 'crm/sheets/' . $sheet->id . '/share', 'method' => 'POST', 'class' => 'needs-validation', 'novalidate')) }}
<style>
    .sheet-premium-wrap .form-control {
        border-radius: 8px !important;
        border: 1px solid #cbd5e1 !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        font-size: 0.875rem !important;
        transition: all 0.2s !important;
    }
    .sheet-premium-wrap .form-control:focus {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
        outline: none !important;
    }
    .sheet-premium-wrap .btn-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        border: none !important;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.15) !important;
        font-weight: 600 !important;
        transition: all 0.2s !important;
    }
    .sheet-premium-wrap .btn-primary:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 6px 14px rgba(16, 185, 129, 0.25) !important;
    }
</style>
<div class="sheet-premium-wrap">
    <div class="modal-body py-3">
        <div class="row">
            <div class="form-group col-12 mb-3">
                {{ Form::label('user_id', __('Select Collaborator'), ['class' => 'form-label font-weight-bold text-dark']) }}
                {{ Form::select('user_id', $users, null, array('class' => 'form-control', 'required' => 'required', 'placeholder' => __('Select a team member...'))) }}
                <small class="form-text text-muted">{{ __('Invite a teammate to collaborate on this sheet in real-time. They must accept your invite to access it.') }}</small>
            </div>
        </div>
    </div>
    <div class="modal-footer border-top-0 pt-0">
        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px;">{{ __('Send Invite') }}</button>
    </div>
</div>
{{ Form::close() }}
