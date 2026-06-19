@extends('layouts.main')

@section('page-title')
    {{ __('Manage E-Sign Templates') }}
@endsection

@section('page-action')
    <a href="{{ route('esign-templates.create') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Create Template') }}">
        <i class="ti ti-plus"></i> {{ __('Add Template') }}
    </a>
@endsection

@section('page-breadcrumb')
    {{ __('Sales') }},{{ __('E-Sign Templates') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Available PDF Form Templates') }}</h5>
                    <small class="text-muted">{{ __('Upload standard PDFs, define coordinate placements for your text details, and set up your Digio signature areas.') }}</small>
                </div>
                <div class="card-body table-border-style">
                    @if(session('success'))
                        <div class="alert alert-success border-0 rounded-3 mb-4">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger border-0 rounded-3 mb-4">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <div class="table-responsive">
                        <table class="table align-items-center">
                            <thead>
                                <tr>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Template Name') }}</th>
                                    <th>{{ __('Source PDF URL') }}</th>
                                    <th>{{ __('Total Mapped Variables') }}</th>
                                    <th>{{ __('API Config Endpoint') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    <th class="text-end" style="width: 150px;">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $template)
                                    <tr>
                                        <td><span class="badge bg-light-secondary text-dark">#{{ $template->id }}</span></td>
                                        <td class="font-style-semibold">{{ $template->name }}</td>
                                        <td>
                                            <a href="{{ url($template->pdf_url) }}" target="_blank" class="text-primary text-decoration-none">
                                                <i class="ti ti-file-type-pdf text-danger fs-5 align-middle me-1"></i>
                                                {{ basename($template->pdf_url) }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-light-primary text-primary">{{ $template->fields_count }} variables</span>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm" style="max-width: 250px;">
                                                <input type="text" class="form-control form-control-sm bg-light border-0 text-xs text-muted" value="{{ url('api/esign-templates/' . $template->id . '/config') }}" readonly id="api-endpoint-{{ $template->id }}" style="font-family: monospace;">
                                                <button class="btn btn-primary btn-sm px-2.5 py-1" onclick="copyApiEndpoint({{ $template->id }})" title="Copy API Endpoint">
                                                    <i class="ti ti-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>{{ $template->created_at->format('d M Y, h:i A') }}</td>
                                        <td class="text-end">
                                            <div class="action-btn bg-success ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center open-share-modal" 
                                                   data-id="{{ $template->id }}" 
                                                   data-name="{{ $template->name }}" 
                                                   data-bs-toggle="tooltip" 
                                                   title="{{ __('Generate Shareable Link') }}">
                                                    <i class="ti ti-link text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('esign-templates.edit', $template->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{ __('Edit Coordinates & Fields') }}">
                                                    <i class="ti ti-pencil text-white"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                <form action="{{ route('esign-templates.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this template? This will remove all associated variables.');" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="mx-3 btn btn-sm d-inline-flex align-items-center border-0 bg-transparent" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="ti ti-file-description fs-1 text-muted mb-3 d-block"></i>
                                            <h5>{{ __('No Templates Found') }}</h5>
                                            <p class="text-sm">{{ __('Get started by uploading your first PDF form template.') }}</p>
                                            <a href="{{ route('esign-templates.create') }}" class="btn btn-sm btn-primary mt-2">
                                                <i class="ti ti-plus"></i> {{ __('Add Template') }}
                                            </a>
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

    <!-- Shareable Link Modal -->
    <div class="modal fade" id="shareableLinkModal" tabindex="-1" aria-labelledby="shareableLinkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="shareableLinkModalLabel">
                        <i class="ti ti-link text-success me-2"></i>{{ __('Generate Shareable E-Sign Link') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted text-xs uppercase">{{ __('Template Name') }}</label>
                        <div class="form-control bg-light fw-bold text-dark border-0" id="modal-template-name" style="font-size: 0.95rem;"></div>
                        <input type="hidden" id="modal-template-id">
                    </div>
                    <div class="mb-3" id="url-container">
                        <label for="generated-shareable-url" class="form-label fw-bold text-muted text-xs uppercase">{{ __('Public E-Sign URL') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control border-2" id="generated-shareable-url" readonly style="font-family: monospace; font-size: 0.85rem; border-radius: 8px 0 0 8px;">
                            <button class="btn btn-primary" type="button" id="btn-copy-url" style="border-radius: 0 8px 8px 0;">
                                <i class="ti ti-copy me-1"></i> {{ __('Copy') }}
                            </button>
                        </div>
                        <small class="text-success mt-2 d-none fw-semibold" id="copy-success-msg">
                            <i class="ti ti-circle-check"></i> {{ __('Copied to clipboard!') }}
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function copyApiEndpoint(id) {
        const inputEl = document.getElementById('api-endpoint-' + id);
        inputEl.select();
        inputEl.setSelectionRange(0, 99999); // For mobile devices
        navigator.clipboard.writeText(inputEl.value);
        
        if (typeof show_toastr === 'function') {
            show_toastr('Success', 'API Config Endpoint URL copied to clipboard!', 'success');
        } else {
            alert('API Config Endpoint URL copied to clipboard!');
        }
    }

    $(document).ready(function() {
        $(document).on('click', '.open-share-modal', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            $('#modal-template-id').val(id);
            $('#modal-template-name').text(name);
            
            const baseUrl = "{{ url('esign-fill/public') }}";
            const generatedUrl = `${baseUrl}/${id}`;
            $('#generated-shareable-url').val(generatedUrl);
            $('#copy-success-msg').addClass('d-none');
            
            $('#shareableLinkModal').modal('show');
        });

        $('#btn-copy-url').on('click', function() {
            const copyText = document.getElementById('generated-shareable-url');
            if (copyText && copyText.value) {
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyText.value);
                
                $('#copy-success-msg').removeClass('d-none');
                setTimeout(() => {
                    $('#copy-success-msg').addClass('d-none');
                }, 3000);
                
                if (typeof show_toastr === 'function') {
                    show_toastr('Success', 'Shareable E-Sign Link copied to clipboard!', 'success');
                }
            }
        });
    });
</script>
@endpush
