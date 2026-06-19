{{ Form::open(array('url' => 'crm/sheets', 'method' => 'POST', 'files' => true, 'class' => 'needs-validation', 'novalidate')) }}
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
                {{ Form::label('name', __('Spreadsheet Name'), ['class' => 'form-label font-weight-bold text-dark']) }}
                {{ Form::text('name', '', array('class' => 'form-control', 'required' => 'required', 'placeholder' => __('e.g. Sales Pipeline Sync, Q2 Projections'))) }}
                <small class="form-text text-muted">{{ __('Provide a friendly name for this sheet. It will be used for sharing and tracking edits.') }}</small>
            </div>
            <div class="form-group col-12 mb-3">
                {{ Form::label('excel_file', __('Import Excel File (Optional)'), ['class' => 'form-label font-weight-bold text-dark']) }}
                {{ Form::file('excel_file', array('class' => 'form-control', 'accept' => '.xlsx, .xls, .csv')) }}
                <small class="form-text text-muted">{{ __('Import data from an existing spreadsheet (.xlsx, .xls, .csv). If uploaded, this data will initialize the sheet.') }}</small>
            </div>
        </div>
    </div>
    <div class="modal-footer border-top-0 pt-0">
        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 600;">{{ __('Cancel') }}</button>
        <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px;">{{ __('Create Sheet') }}</button>
    </div>
</div>
{{ Form::close() }}

