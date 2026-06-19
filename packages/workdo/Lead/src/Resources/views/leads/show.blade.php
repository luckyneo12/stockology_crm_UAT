@extends(request()->has('layout') && request('layout') == 'iframe' ? 'layouts.iframe' : 'layouts.main')

@section('page-title')
    {{ $lead->name }}
@endsection

@push('scripts')
    @include('lead::leads.click_to_call_script')
@endpush

@section('content')
    @include('lead::layouts.anti_screenshot')

    <div id="react-lead-details"
         data-lead-id="{{ $lead->id }}"
         data-workspace-id="{{ getActiveWorkSpace() }}"
         data-current-user-id="{{ Auth::user()->id }}">
        
        <!-- Server side loader for smooth layout entry -->
        <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted" style="min-height: 400px;">
            <div class="spinner-border text-success mb-3" role="status"></div>
            <span>Loading Lead Details...</span>
        </div>
    </div>

    @vite(['resources/js/app-react.jsx'])
@endsection