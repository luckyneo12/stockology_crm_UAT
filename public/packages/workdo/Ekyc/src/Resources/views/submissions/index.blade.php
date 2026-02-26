@extends('layouts.main')

@section('page-title')
    {{ __('EKYC Submissions') }}
@endsection

@section('page-breadcrumb')
    {{ __('eKYC') }}, {{ __('Submissions') }}
@endsection

@section('page-action')
    <div class="float-end">
        @can('ekyc manage')
            <a href="#" data-url="{{ route('ekyc-leads.create') }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Create New Lead') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Create') }}">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    @include('ekyc::leads.filter_bar')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('KYC Applications Status') }}</h5>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>{{ __('Contact Info') }}</th>
                                    <th>{{ __('Current Step') }}</th>
                                    <th>{{ __('Progress') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Date Started') }}</th>
                                    <th width="150px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($submissions as $submission)
                                    @php
                                        $enabledSteps = \Workdo\Ekyc\Entities\EkycSubmission::getEnabledSteps();
                                        $totalSteps = count($enabledSteps);
                                        $completedCount = 0;
                                        foreach($enabledSteps as $num => $info) {
                                            if($submission->isStepCompleted($num)) $completedCount++;
                                        }
                                        $percent = $totalSteps > 0 ? round(($completedCount / $totalSteps) * 100) : 0;
                                        $currentStepName = $enabledSteps[$submission->getNextStep()]['name'] ?? 'Completed';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="ms-2">
                                                    <h6 class="mb-0">{{ $submission->mobile_number ?? 'N/A' }}</h6>
                                                    <p class="text-muted mb-0 small">{{ $submission->email ?? 'No email' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light-primary text-primary">{{ $currentStepName }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar bg-{{ $percent == 100 ? 'success' : 'info' }}" role="progressbar" style="width: {{ $percent }}%"></div>
                                                </div>
                                                <span class="small font-weight-bold">{{ $percent }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($submission->status == 'completed')
                                                <span class="badge bg-success">{{ __('Completed') }}</span>
                                            @elseif($submission->status == 'rejected')
                                                <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ ucwords(str_replace('_', ' ', $submission->status)) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $submission->created_at->format('d M Y, h:i A') }}</td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ route('ekyc.admin.submissions.show', $submission->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{ __('View Details') }}">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                                @if(Auth::user()->can('ekyc manage') || Auth::user()->type == 'company')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['ekyc.admin.submissions.destroy', $submission->id], 'id' => 'delete-form-' . $submission->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" data-bs-toggle="tooltip" title="{{ __('Delete (Move to Trash)') }}" data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action will move the submission to trash. Continue?') }}">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                    <div class="action-btn bg-dark ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['ekyc.admin.submissions.force-destroy', $submission->id], 'id' => 'force-delete-form-' . $submission->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm" data-bs-toggle="tooltip" title="{{ __('Hard Delete (Permanent)') }}" data-confirm="{{ __('Are You Absolutely Sure?') }}" data-text="{{ __('This action is PERMANENT and cannot be undone. All data will be wiped!') }}">
                                                                <i class="ti ti-circle-x text-white"></i>
                                                            </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    <div class="d-flex justify-content-end mt-3">
                        {{ $submissions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
