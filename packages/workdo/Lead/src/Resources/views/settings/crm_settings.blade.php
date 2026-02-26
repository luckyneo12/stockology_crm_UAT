@extends('layouts.main')

@section('page-title')
    {{ __('CRM Settings') }}
@endsection

@section('page-breadcrumb')
    {{ __('Setup') }},
    {{ __('CRM Settings') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-3 col-12">
            @include('lead::layouts.system_setup')
        </div>
        <div class="col-xl-9">
            <div class="card shadow-sm border-0 glass-effect">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-1 text-primary">{{ __('Duplicate Prevention Settings') }}</h5>
                            <p class="text-xs text-muted mb-0">{{ __('Select fields that should be checked for duplicates in real-time during Lead creation.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-body mt-4">
                    <form action="{{ route('crm.settings.save') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-4">
                                <div class="alert alert-info border-0 shadow-sm bg-light-info">
                                    <div class="d-flex align-items-center">
                                        <div class="alert-icon-me-2">
                                            <i class="ti ti-info-circle f-20 text-info"></i>
                                        </div>
                                        <div class="ms-2">
                                            {{ __('Enabling duplicate check for a field will alert the user instantly if they enter a value that already exists in your workspace.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <h6 class="mb-3">{{ __('Fields to Check') }}</h6>
                                <div class="row g-3">
                                    @foreach ($fields as $key => $label)
                                        <div class="col-md-4">
                                            <div class="form-check form-switch custom-switch-v1 mb-2">
                                                <input type="checkbox" class="form-check-input input-primary" 
                                                       name="duplicate_fields[]" value="{{ $key }}" 
                                                       id="field_{{ $key }}"
                                                       {{ in_array($key, $duplicateFields) ? 'checked' : '' }}>
                                                <label class="form-check-label f-w-500" for="field_{{ $key }}">{{ __($label) }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center shadow-sm">
                                <i class="ti ti-device-floppy me-2"></i>
                                {{ __('Save Configuration') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .glass-effect {
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .bg-light-info {
        background-color: rgba(63, 153, 222, 0.1) !important;
    }
    .custom-switch-v1 .form-check-input:checked {
        background-color: #584ed2;
        border-color: #584ed2;
    }
</style>
