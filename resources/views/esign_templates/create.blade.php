@extends('layouts.main')

@section('page-title')
    {{ __('Upload E-Sign Template') }}
@endsection

@section('page-breadcrumb')
    {{ __('Sales') }},{{ __('E-Sign Templates') }},{{ __('Upload Template') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Upload Source PDF') }}</h5>
                    <small class="text-muted">{{ __('Ensure your PDF matches your standard documentation format. Once uploaded, you can start mapping target text fields and signature placements.') }}</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('esign-templates.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="form-group mb-4">
                            <label for="name" class="form-label fw-bold">{{ __('Template Name') }}</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Account Opening Agreement" required>
                            <small class="text-muted">{{ __('Use a clear, descriptive name to easily identify this form in integrations.') }}</small>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="pdf_file" class="form-label fw-bold">{{ __('Source PDF Document') }}</label>
                            <div class="p-4 rounded-3 text-center border-dashed" style="border: 2px dashed #0F62FE; background-color: rgba(15, 98, 254, 0.02);">
                                <i class="ti ti-cloud-upload text-primary fs-1 mb-2 d-block"></i>
                                <span class="d-block mb-3 text-muted">Click or drag your PDF here (Max Size: 10MB)</span>
                                <input type="file" name="pdf_file" id="pdf_file" class="form-control form-control-sm mx-auto" style="max-width: 300px;" accept="application/pdf" required>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-5">
                            <a href="{{ route('esign-templates.index') }}" class="btn btn-light">
                                <i class="ti ti-arrow-left me-1"></i> {{ __('Back to Templates') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-upload me-1"></i> {{ __('Upload & Configure') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
