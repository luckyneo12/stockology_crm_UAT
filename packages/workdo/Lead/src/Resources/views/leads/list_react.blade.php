@extends('layouts.main')

@section('page-title')
    {{ __('Leads List') }}
@endsection

@push('css')
<style>
  /* Ant Design scoped reset — prevent conflicts with Bootstrap */
  #react-leads-list * { box-sizing: border-box; }
  #react-leads-list .ant-table-wrapper { font-family: 'Plus Jakarta Sans', sans-serif; }
</style>
@endpush

@push('scripts')
@include('lead::leads.click_to_call_script')
@endpush

@section('page-action')
@endsection

@section('content')
@include('lead::layouts.anti_screenshot')

@if ($pipeline)
    @php
        $stageOptions = $stages->map(fn($name, $id) => ['value' => (string)$id, 'label' => $name])->values();
        $sourceOptions = $sources->map(fn($name, $id) => ['value' => (string)$id, 'label' => $name])->values();
        $userOptions = $users->map(fn($name, $id) => ['value' => (string)$id, 'label' => $name])->values();
        $creatorOptions = $creators->map(fn($name, $id) => ['value' => (string)$id, 'label' => $name])->values();
        $pipelineOptions = $pipelines->map(fn($name, $id) => ['value' => (string)$id, 'label' => $name])->values();
        $deptOptions = collect($departments)->map(fn($name, $id) => ['value' => (string)$id, 'label' => $name])->values();
        $teamOptions = collect($teams)->map(fn($name, $id) => ['value' => (string)$id, 'label' => $name])->values();
    @endphp

    <div
        id="react-leads-list"
        data-pipeline-id="{{ $pipeline->id }}"
        data-pipeline-options="{{ json_encode($pipelineOptions) }}"
        data-stage-options="{{ json_encode($stageOptions) }}"
        data-source-options="{{ json_encode($sourceOptions) }}"
        data-user-options="{{ json_encode($userOptions) }}"
        data-creator-options="{{ json_encode($creatorOptions) }}"
        data-dept-options="{{ json_encode($deptOptions) }}"
        data-team-options="{{ json_encode($teamOptions) }}"
        data-current-user-id="{{ Auth::user()->id }}"
        data-can-create="{{ Auth::user()->isAbleTo('lead create') ? '1' : '0' }}"
        data-can-edit="{{ Auth::user()->isAbleTo('lead edit') ? '1' : '0' }}"
        data-can-delete="{{ Auth::user()->isAbleTo('lead delete') ? '1' : '0' }}"
        data-create-url="{{ route('leads.create') }}"
        data-csrf="{{ csrf_token() }}"
    >
        {{-- Initial server-side loader --}}
        <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted" style="min-height:300px">
            <div class="spinner-border text-success mb-3" role="status"></div>
            <span style="font-size:0.9rem">Loading Leads List…</span>
        </div>
    </div>
@else
    <div class="row pt-5">
        <div class="col-md-6 offset-md-3 text-center">
            <div class="card p-5 shadow-sm border-0">
                <div class="card-body">
                    <i class="ti ti-alert-triangle text-warning mb-4" style="font-size: 3rem;"></i>
                    <h3 class="mb-3">{{ __('No Pipeline Found') }}</h3>
                    <p class="text-muted mb-4">{{ __('Please create a pipeline in System Setup first.') }}</p>
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
