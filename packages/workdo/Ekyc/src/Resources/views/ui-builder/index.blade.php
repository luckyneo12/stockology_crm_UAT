@extends('layouts.main')

@section('page-title')
    {{__('UI/UX Builder')}}
@endsection

@section('page-breadcrumb')
    {{__('eKYC')}},
    {{__('UI Builder')}}
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('UI/UX Builder') }}</h5>
                <small class="text-muted">{{ __('Create custom KYC form designs without coding') }}</small>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    <strong>{{ __('Coming Soon') }}</strong>
                    <p class="mb-0">{{ __('The drag-and-drop UI builder is currently under development. This feature will allow you to create custom KYC form layouts, choose components, and customize the design without writing any code.') }}</p>
                </div>

                <hr>

                <h6 class="mb-3">{{ __('Current Templates') }}</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td>{{ $template->name }}</td>
                                    <td>{{ $template->description }}</td>
                                    <td>
                                        @if($template->is_active)
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                        @endif
                                        @if($template->is_default)
                                            <span class="badge bg-primary">{{ __('Default') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $template->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if(!$template->is_active)
                                            <button class="btn btn-sm btn-primary activate-template" data-id="{{ $template->id }}">
                                                <i class="ti ti-check me-1"></i>{{ __('Activate') }}
                                            </button>
                                        @endif
                                        <a href="{{ route('ekyc.admin.ui-builder.preview', $template->id) }}" class="btn btn-sm btn-info" target="_blank">
                                            <i class="ti ti-eye me-1"></i>{{ __('Preview') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        {{ __('No templates found. The default template will be created automatically.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <hr>

                <h6 class="mb-3">{{ __('Quick Actions') }}</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('ekyc.form.start') }}" target="_blank" class="btn btn-outline-primary w-100">
                            <i class="ti ti-external-link me-2"></i>{{ __('Preview Current Form') }}
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('ekyc.settings') }}" class="btn btn-outline-secondary w-100">
                            <i class="ti ti-settings me-2"></i>{{ __('Back to Settings') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).on('click', '.activate-template', function() {
        var templateId = $(this).data('id');
        
        if(confirm('{{ __("Are you sure you want to activate this template?") }}')) {
            $.ajax({
                url: '{{ url("admin/ekyc/ui-builder/activate") }}/' + templateId,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if(response.success) {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    alert('{{ __("Error activating template") }}');
                }
            });
        }
    });
</script>
@endpush
@endsection
