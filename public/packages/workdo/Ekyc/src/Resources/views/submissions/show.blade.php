@extends('layouts.main')

@section('page-title')
    {{ __('Submission Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('ekyc.admin.submissions.index') }}">{{ __('EKYC Submissions') }}</a></li>
    <li class="breadcrumb-item">{{ __('Details') }}</li>
@endsection

@section('content')
    <div class="row">
        <!-- Main Info -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5>{{ __('Submission ID') }}: #{{ $submission->id }}</h5>
                    <span class="badge bg-{{ $submission->status == 'completed' ? 'success' : ($submission->status == 'rejected' ? 'danger' : 'warning') }}">
                        {{ ucwords(str_replace('_', ' ', $submission->status)) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <small class="text-muted d-block">{{ __('Mobile Number') }}</small>
                            <h6>{{ $submission->mobile_number }}</h6>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">{{ __('Email') }}</small>
                            <h6>{{ $submission->email ?? 'N/A' }}</h6>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">{{ __('Started At') }}</small>
                            <h6>{{ $submission->created_at->format('d M Y, h:i A') }}</h6>
                        </div>
                    </div>

                    <div class="accordion" id="kycSections">
                        <!-- PAN Section -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secPan">
                                    <i class="ti ti-id me-2"></i> {{ __('PAN & Identity') }}
                                </button>
                            </h2>
                            <div id="secPan" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <span class="text-muted">{{ __('PAN Number') }}:</span> <strong>{{ $submission->pan_number }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <span class="text-muted">{{ __('Name as per PAN') }}:</span> <strong>{{ $submission->pan_name }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <span class="text-muted">{{ __('Date of Birth') }}:</span> <strong>{{ $submission->pan_dob ? $submission->pan_dob->format('d M Y') : 'N/A' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <span class="text-muted">{{ __('RM/PP Code') }}:</span> <strong>{{ $submission->rm_pp_code ?? 'None' }}</strong>
                                        </div>
                                        <hr>
                                        <div class="col-md-6 mb-3">
                                            <span class="text-muted">{{ __('Aadhaar Status') }}:</span> 
                                            @if($submission->aadhaar_verified_at)
                                                <span class="badge bg-success">{{ __('Verified') }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ __('Pending') }}</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <span class="text-muted">{{ __('Aadhaar ID') }}:</span> <strong>{{ $submission->additional_data['aadhaar_id'] ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <span class="text-muted">{{ __('Name as per Aadhaar') }}:</span> <strong>{{ $submission->additional_data['aadhaar_name'] ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <span class="text-muted">{{ __('Aadhaar Linked Mobile') }}:</span> <strong>{{ $submission->additional_data['aadhaar_linked_mobile'] ?? 'N/A' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Section -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secBank">
                                    <i class="ti ti-building-bank me-2"></i> {{ __('Bank Details') }}
                                </button>
                            </h2>
                            <div id="secBank" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <span class="text-muted">{{ __('Account Number') }}:</span> <strong>{{ $submission->bank_account_number }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <span class="text-muted">{{ __('IFSC') }}:</span> <strong>{{ $submission->bank_ifsc }}</strong>
                                        </div>
                                        <div class="col-md-12">
                                            <span class="text-muted">{{ __('Holder Name') }}:</span> <strong>{{ $submission->bank_account_holder_name }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Details -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secPersonal">
                                    <i class="ti ti-user-circle me-2"></i> {{ __('Additional Details') }}
                                </button>
                            </h2>
                            <div id="secPersonal" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">Father: <strong>{{ $submission->father_name }}</strong></div>
                                        <div class="col-md-6 mb-2">Mother: <strong>{{ $submission->mother_name }}</strong></div>
                                        <div class="col-md-6 mb-2">Marital: <strong>{{ ucfirst($submission->marital_status) }}</strong></div>
                                        <div class="col-md-6 mb-2">Occupation: <strong>{{ ucwords(str_replace('_', ' ', $submission->occupation)) }}</strong></div>
                                        <div class="col-md-6 mb-2">Income: <strong>{{ $submission->annual_income }}</strong></div>
                                        <div class="col-md-6 mb-2">PEP Status: <strong>{{ $submission->is_pep ? 'Yes' : 'No' }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nominee -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secNominee">
                                    <i class="ti ti-users me-2"></i> {{ __('Nominee Details') }}
                                </button>
                            </h2>
                            <div id="secNominee" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    @if($submission->has_nominee == 'yes')
                                        @php $n = json_decode($submission->nominee, true); @endphp
                                        <div class="row">
                                            <div class="col-md-6">Name: <strong>{{ $n['name'] ?? 'N/A' }}</strong></div>
                                            <div class="col-md-6">Relation: <strong>{{ $n['relation'] ?? 'N/A' }}</strong></div>
                                            <div class="col-md-6">Share: <strong>{{ $n['share'] ?? '100' }}%</strong></div>
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">No nominee added.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Documents -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secDocs">
                                    <i class="ti ti-camera me-2"></i> {{ __('Captured Evidence') }}
                                </button>
                            </h2>
                            <div id="secDocs" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <div class="row text-center">
                                        <div class="col-md-6 mb-3">
                                            <p class="small text-muted mb-1">{{ __('Face Selfie') }}</p>
                                            @if($submission->face_selfie)
                                                <img src="{{ route('ekyc.submission.image', ['id'=>$submission->id, 'field'=>'face_selfie']) }}" class="img-fluid rounded border" style="max-height: 200px;">
                                            @else
                                                <div class="p-4 border rounded bg-light">No Image</div>
                                            @endif
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <p class="small text-muted mb-1">{{ __('Signature') }}</p>
                                            @if($submission->signature)
                                                <img src="{{ route('ekyc.submission.image', ['id'=>$submission->id, 'field'=>'signature']) }}" class="img-fluid rounded border bg-white" style="max-height: 150px;">
                                            @else
                                                <div class="p-4 border rounded bg-light">No Signature</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Activity/Logs -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Activity Logs') }}</h5>
                </div>
                <div class="card-body p-3">
                    <ul class="list-group list-group-flush">
                        @php
                            $activities = collect();
                            
                            // Initialize with created_at
                            $activities->push(['title' => 'Form Started', 'status' => 'Initial', 'time' => $submission->created_at, 'icon' => 'player-play', 'color' => 'secondary']);

                            foreach($submission->otpLogs as $log) {
                                $activities->push([
                                    'title' => ucfirst($log->type) . ' Verification',
                                    'status' => ucfirst($log->status),
                                    'time' => $log->created_at,
                                    'icon' => ($log->type == 'email' ? 'mail' : 'device-mobile'),
                                    'color' => ($log->status == 'verified' ? 'success' : 'primary')
                                ]);
                            }

                            if ($submission->mobile_verified_at) {
                                $activities->push(['title' => 'Mobile Verified', 'status' => 'Confirmed', 'time' => $submission->mobile_verified_at, 'icon' => 'phone-check', 'color' => 'success']);
                            }
                            if ($submission->email_verified_at) {
                                $activities->push(['title' => 'Email Verified', 'status' => 'Confirmed', 'time' => $submission->email_verified_at, 'icon' => 'mail-check', 'color' => 'success']);
                            }
                            if ($submission->pan_verified_at) {
                                $activities->push(['title' => 'PAN Verification', 'status' => 'Verified: ' . $submission->pan_number, 'time' => $submission->pan_verified_at, 'icon' => 'id', 'color' => 'success']);
                            }
                            if ($submission->aadhaar_verified_at) {
                                $aadhaarName = $submission->additional_data['aadhaar_name'] ?? 'Verified';
                                $activities->push(['title' => 'Aadhaar Verification', 'status' => 'Name: ' . $aadhaarName, 'time' => $submission->aadhaar_verified_at, 'icon' => 'fingerprint', 'color' => 'success']);
                            }
                            if ($submission->bank_verified_at) {
                                $activities->push(['title' => 'Bank Verification', 'status' => 'Account Verified', 'time' => $submission->bank_verified_at, 'icon' => 'building-bank', 'color' => 'success']);
                            }
                            if ($submission->face_verified_at) {
                                $activities->push(['title' => 'Selfie Verification', 'status' => 'Matched ('. $submission->face_match_score . '%)', 'time' => $submission->face_verified_at, 'icon' => 'camera-check', 'color' => 'success']);
                            }

                            $activities = $activities->sortByDesc('time');
                        @endphp

                        @foreach($activities as $act)
                            <li class="list-group-item px-0 border-0">
                                <div class="d-flex align-items-center">
                                    <div class="icon-shape bg-{{ $act['color'] }} text-white rounded me-3 p-1">
                                        <i class="ti ti-{{ $act['icon'] }} fs-5"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 small fw-bold">{{ __($act['title']) }}</p>
                                        <p class="mb-0 text-muted small">{{ $act['status'] }} at {{ $act['time']->format('H:i A') }}</p>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            @if($submission->status != 'approved' && $submission->status != 'rejected')
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3">Take Action</h6>
                        <div class="grid grid-2 gap-2 d-flex">
                            <button class="btn btn-success flex-grow-1" data-ajax-popup="true" data-url="{{ route('ekyc.admin.submissions.approve', $submission->id) }}" data-title="Approve KYC">Approve</button>
                            <button class="btn btn-danger flex-grow-1 ms-2" data-ajax-popup="true" data-url="{{ route('ekyc.admin.submissions.reject', $submission->id) }}" data-title="Reject KYC">Reject</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
