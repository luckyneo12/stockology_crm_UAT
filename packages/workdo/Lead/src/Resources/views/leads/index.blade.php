@extends('layouts.main')

@section('page-title')
    {{ __('Leads') }}
@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/plugins/summernote-0.8.18-dist/summernote-lite.min.js') }}"></script>
    @include('lead::leads.click_to_call_script')
@endpush

@section('page-action')
@endsection

@section('content')
@include('lead::layouts.anti_screenshot')

@if ($pipeline)
    @php
        // Prepare stage array with permission properties so React knows locks/perms
        $stageList = [];
        foreach ($pipeline->leadStages as $stg) {
            $perms = $stg->permissions();
            if ($perms->can_view) {
                $stageList[] = [
                    'id' => $stg->id,
                    'name' => $stg->name,
                    'permissions' => [
                        'can_view' => $perms->can_view,
                        'can_move' => $perms->can_move,
                        'can_edit' => $perms->can_edit,
                    ]
                ];
            }
        }
    @endphp

    <div id="react-leads-board"
         data-pipelines="{{ json_encode($pipelines) }}"
         data-stages="{{ json_encode($stageList) }}"
         data-sources="{{ json_encode($sources) }}"
         data-users="{{ json_encode($users) }}"
         data-departments="{{ json_encode($departments) }}"
         data-teams="{{ json_encode($teams) }}"
         data-current-pipeline-id="{{ $pipeline ? $pipeline->id : '' }}"
         data-current-user-id="{{ Auth::user()->id }}"
         data-workspace-id="{{ getActiveWorkSpace() }}">
        
        <!-- Server side loader for smooth layout entry -->
        <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
            <div class="spinner-border text-primary mb-2" role="status"></div>
            <span>Loading Leads Board...</span>
        </div>
    </div>
@else
    <div class="row pt-5">
        <div class="col-md-8 offset-md-2 text-center">
            <div class="card p-5 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-warning mb-4">
                        <i class="ti ti-alert-triangle" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="mb-3">{{ __('No Pipeline Found') }}</h3>
                    <p class="text-muted mb-4">
                        {{ __('There are no pipelines or stages defined in your active workspace. Please go to System Setup to create one, or verify your workspace selection.') }}
                    </p>
                    <a href="{{ route('pipelines.index') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-2"></i>{{ __('Manage Pipelines') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif

@vite(['resources/js/app-react.jsx'])
@endsection