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
                                        <div class="col-md-12 mt-2">
                                            <span class="text-muted d-block mb-2">{{ __('Aadhaar Photo') }}:</span>
                                            @if(!empty($submission->additional_data['id_proof_path']))
                                                <img src="{{ route('ekyc.admin.submission.image', ['id'=>$submission->id, 'field'=>'aadhaar_photo']) }}" class="img-fluid rounded border" style="max-height: 200px;">
                                            @else
                                                <div class="p-3 border rounded bg-light small text-muted">No photo found in Aadhaar data</div>
                                            @endif
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

                        <!-- Biometric Match Report -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#secBiometrics">
                                    <i class="ti ti-scan me-2"></i> {{ __('Biometric Match Report') }}
                                </button>
                            </h2>
                            <div id="secBiometrics" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-5 text-center">
                                            <p class="small text-muted mb-2">{{ __('Identity Reference (Aadhaar)') }}</p>
                                            @if(!empty($submission->additional_data['id_proof_path']))
                                                <div class="position-relative d-inline-block">
                                                    <img src="{{ route('ekyc.admin.submission.image', ['id'=>$submission->id, 'field'=>'aadhaar_photo']) }}" class="img-fluid rounded shadow-sm border" style="max-height: 220px; border-width: 2px !important;">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary border border-light">ID</span>
                                                </div>
                                            @else
                                                <div class="p-4 border rounded bg-light-warning">No Aadhaar Photo</div>
                                            @endif
                                        </div>
                                        
                                        <div class="col-md-2 text-center py-4">
                                            @php
                                                $score = (float)$submission->face_match_score;
                                                $color = $score >= 60 ? 'success' : ($score >= 35 ? 'warning' : 'danger');
                                                $icon = $score >= 60 ? 'circle-check' : ($score >= 35 ? 'exclamation-circle' : 'circle-x');
                                            @endphp
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="mb-2 text-{{ $color }}">
                                                    <i class="ti ti-{{ $icon }} fs-1 h1 mb-0"></i>
                                                </div>
                                                <div class="h4 fw-bold mb-0 text-{{ $color }}">{{ number_format($score, 1) }}%</div>
                                                <small class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem;">Match Score</small>
                                            </div>
                                        </div>

                                        <div class="col-md-5 text-center">
                                            <p class="small text-muted mb-2">{{ __('Live Captured Selfie') }}</p>
                                            @if($submission->selfie_path)
                                                <div class="position-relative d-inline-block">
                                                    <img src="{{ route('ekyc.admin.submission.image', ['id'=>$submission->id, 'field'=>'selfie_path']) }}" class="img-fluid rounded shadow-sm border" style="max-height: 220px; border-width: 2px !important;">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-{{ $color }} border border-light">Live</span>
                                                </div>
                                            @else
                                                <div class="p-4 border rounded bg-light-danger">No Selfie Captured</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-3 border-top">
                                        <div class="row small text-muted">
                                            <div class="col-md-4">
                                                <span>Method:</span> <strong class="text-dark">{{ $submission->additional_data['biometrics']['verification_method'] ?? 'Browser AI (face-api.js)' }}</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <span>Status:</span> <strong class="text-{{ $color }}">{{ ucwords($submission->status) }}</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <span>Verified At:</span> <strong class="text-dark">{{ $submission->face_verified_at ? $submission->face_verified_at->format('d M, h:i A') : 'N/A' }}</strong>
                                            </div>
                                        </div>
                                        @php
                                            $loc = $submission->additional_data['biometrics']['capture_location'] ?? null;
                                        @endphp
                                        @if($loc && $loc['latitude'])
                                            <div class="mt-3 p-3 bg-light-info rounded-3 border-start border-4 border-info">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <small class="fw-bold"><i class="ti ti-map-pin me-1"></i> Capture Coordinates</small>
                                                    <a href="https://www.google.com/maps?q={{ $loc['latitude'] }},{{ $loc['longitude'] }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.65rem;">
                                                        <i class="ti ti-external-link me-1"></i> View on Map
                                                    </a>
                                                </div>
                                                <div class="small mb-1 text-muted">{{ number_format($loc['latitude'], 6) }}, {{ number_format($loc['longitude'], 6) }}</div>
                                                
                                                @if(!empty($loc['address']))
                                                    <div class="mt-2 pt-2 border-top">
                                                        <small class="fw-bold d-block mb-1 text-dark">Fingerprinted Address:</small>
                                                        <div class="small p-2 bg-white rounded border" style="line-height: 1.4;">{{ $loc['address'] }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        @if(!empty($submission->additional_data['biometrics']['status_reason']))
                                            <div class="mt-2 p-2 bg-light rounded-2 border-start border-4 border-{{ $color }}">
                                                <small><strong>Decision Logic:</strong> {{ $submission->additional_data['biometrics']['status_reason'] }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Other Captured Documents -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secDocs">
                                    <i class="ti ti-camera me-2"></i> {{ __('Other Evidence') }}
                                </button>
                            </h2>
                            <div id="secDocs" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <div class="row text-center">
                                        <div class="col-md-6 mb-3">
                                            <p class="small text-muted mb-1">{{ __('Signature') }}</p>
                                            @if($submission->signature)
                                                <img src="{{ route('ekyc.admin.submission.image', ['id'=>$submission->id, 'field'=>'signature']) }}" class="img-fluid rounded border bg-white" style="max-height: 150px;">
                                            @else
                                                <div class="p-4 border rounded bg-light">No Signature Found</div>
                                            @endif
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <!-- Placeholder for other documents if needed -->
                                            <p class="small text-muted mb-1">{{ __('Identity Document') }}</p>
                                            @if($submission->id_proof)
                                                <img src="{{ route('ekyc.admin.submission.image', ['id'=>$submission->id, 'field'=>'id_proof']) }}" class="img-fluid rounded border" style="max-height: 150px;">
                                            @else
                                               <div class="p-4 border rounded bg-light">Not Uploaded</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Signed PDFs -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#secSignedPdfs">
                                    <i class="ti ti-file-certificate me-2"></i> {{ __('Signed Documents') }}
                                </button>
                            </h2>
                            <div id="secSignedPdfs" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    @php 
                                        $esignDocs = $submission->additional_data['esign_docs'] ?? [];
                                        $signedCount = 0;
                                    @endphp

                                    @if(!empty($esignDocs))
                                        @foreach($esignDocs as $templateId => $docInfo)
                                            @if(($docInfo['status'] ?? '') === 'signed')
                                                @php $signedCount++; @endphp
                                                <div class="d-flex align-items-center p-3 border rounded mb-3 bg-light">
                                                    <div class="bg-primary text-white p-2 rounded me-3">
                                                        <i class="ti ti-file-certificate fs-3"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">{{ $docInfo['name'] ?? __('Signed Document') }}</h6>
                                                        <small class="text-muted">{{ __('Completed at') }}: {{ \Carbon\Carbon::parse($docInfo['signed_at'])->format('d M Y, h:i A') }}</small>
                                                    </div>
                                                    <a href="{{ \Storage::url($docInfo['signed_path']) }}" target="_blank" class="btn btn-sm btn-primary">
                                                        <i class="ti ti-download me-1"></i> {{ __('Download') }}
                                                    </a>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif

                                    @if($signedCount === 0)
                                        <div class="text-center p-4">
                                            <i class="ti ti-file-off fs-1 text-muted d-block mb-3"></i>
                                            <p class="text-muted">{{ __('No signed documents available yet.') }}</p>
                                            <div class="mt-3">
                                                <a href="{{ route('ekyc.form.view-esign', ['template_id' => 'combined', 'submission_id' => $submission->id, 'format' => 'html']) }}" class="btn btn-sm btn-outline-primary preview-btn">
                                                    <i class="ti ti-eye me-1"></i> {{ __('View e-Sign Preview') }}
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Aadhaar Raw Data (Admin Debug) -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed text-danger" type="button" data-bs-toggle="collapse" data-bs-target="#secRawData">
                                    <i class="ti ti-json me-2"></i> {{ __('Aadhaar Raw Data (JSON)') }}
                                </button>
                            </h2>
                            <div id="secRawData" class="accordion-collapse collapse">
                                <div class="accordion-body p-0">
                                    <pre class="bg-dark text-success p-3 mb-0" style="max-height: 500px; overflow-y: auto; font-size: 0.8rem;">{{ json_encode($submission->aadhaar_data, JSON_PRETTY_PRINT) }}</pre>
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

@push('scripts')
<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div class="modal-header bg-light border-0 py-3">
                <h5 class="modal-title fw-bold" id="previewModalTitle">e-Sign Document Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh; background: #f8fafc;">
                <div id="previewLoader" class="d-flex align-items-center justify-content-center h-100">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted fw-bold">Generating premium preview...</p>
                    </div>
                </div>
                <iframe id="previewFrame" src="" frameborder="0" style="width: 100%; height: 100%; display: none;"></iframe>
            </div>
            <div class="modal-footer border-0 bg-light py-3">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal" style="border-radius: 10px;">Close Preview</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.preview-btn').on('click', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            
            $('#previewFrame').hide().attr('src', url);
            $('#previewLoader').show();
            $('#previewModal').modal('show');

            $('#previewFrame').off('load').on('load', function() {
                $('#previewLoader').hide();
                $(this).fadeIn();
            });
        });
    });
</script>
@endpush
