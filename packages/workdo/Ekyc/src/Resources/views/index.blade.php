@extends('layouts.main')

@section('page-title')
    {{ __('eKYC Dashboard') }}
@endsection

@section('page-breadcrumb')
    {{ __('Dashboard') }}, {{ __('eKYC Dashboard') }}
@endsection

@push('css')
<style>
    :root {
        --ekyc-primary: #6610f2;
        --ekyc-secondary: #6f42c1;
        --ekyc-success: #28a745;
        --ekyc-warning: #ffc107;
        --ekyc-danger: #dc3545;
        --ekyc-info: #17a2b8;
        --ekyc-light: #f8f9fa;
        --ekyc-dark: #343a40;
    }

    .ekyc-dashboard .stat-card {
        border: none;
        border-radius: 15px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        position: relative;
    }

    .ekyc-dashboard .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .ekyc-dashboard .stat-card .card-body {
        padding: 1.5rem;
        z-index: 1;
        position: relative;
    }

    .ekyc-dashboard .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .ekyc-dashboard .nav-tabs-custom {
        border-bottom: none;
        gap: 10px;
        margin-bottom: 20px;
    }

    .ekyc-dashboard .nav-tabs-custom .nav-link {
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        color: var(--ekyc-dark);
        font-weight: 600;
        background: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .ekyc-dashboard .nav-tabs-custom .nav-link.active {
        background: var(--ekyc-primary);
        color: #fff;
    }

    .ekyc-dashboard .lead-card {
        border: none;
        border-radius: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        background: #fff;
        border-left: 5px solid transparent;
    }

    .ekyc-dashboard .lead-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .ekyc-dashboard .lead-card.border-fresh { border-left-color: var(--ekyc-info); }
    .ekyc-dashboard .lead-card.border-pending { border-left-color: var(--ekyc-warning); }
    .ekyc-dashboard .lead-card.border-verified { border-left-color: var(--ekyc-success); }
    .ekyc-dashboard .lead-card.border-rejected { border-left-color: var(--ekyc-danger); }

    .ekyc-dashboard .avatar-circle {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #fff;
    }

    .ekyc-dashboard .progress-slim {
        height: 6px;
        border-radius: 10px;
    }

    .ekyc-dashboard .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 700;
    }

    .bg-soft-primary { background: rgba(102, 16, 242, 0.1); color: #6610f2; }
    .bg-soft-success { background: rgba(40, 167, 69, 0.1); color: #28a745; }
    .bg-soft-warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
    .bg-soft-info { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
</style>
@endpush

@section('content')
<div class="row ekyc-dashboard">
    <!-- Stat Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-soft-primary"><i class="ti ti-users"></i></div>
                <h6 class="text-muted mb-1">{{ __('Total Leads') }}</h6>
                <h3 class="mb-0">1,284</h3>
                <span class="text-success small pt-1 fw-bold">+12%</span> <span class="text-muted small pt-2 ps-1">since last month</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-soft-info"><i class="ti ti-user-plus"></i></div>
                <h6 class="text-muted mb-1">{{ __('Fresh Leads') }}</h6>
                <h3 class="mb-0">452</h3>
                <span class="text-info small pt-1 fw-bold">85 New</span> <span class="text-muted small pt-2 ps-1">today</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-soft-warning"><i class="ti ti-clock"></i></div>
                <h6 class="text-muted mb-1">{{ __('Pending KYC') }}</h6>
                <h3 class="mb-0">126</h3>
                <span class="text-warning small pt-1 fw-bold">15 Urgent</span> <span class="text-muted small pt-2 ps-1">waiting</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-soft-success"><i class="ti ti-user-check"></i></div>
                <h6 class="text-muted mb-1">{{ __('Verified') }}</h6>
                <h3 class="mb-0">706</h3>
                <span class="text-success small pt-1 fw-bold">98%</span> <span class="text-muted small pt-2 ps-1">accuracy</span>
            </div>
        </div>
    </div>

    <div class="col-12">
        <!-- Tabs -->
        <ul class="nav nav-tabs nav-tabs-custom" id="kycTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">{{ __('Common') }}</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="open-tab" data-bs-toggle="tab" data-bs-target="#open" type="button" role="tab">{{ __('Open') }}</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="converted-tab" data-bs-toggle="tab" data-bs-target="#converted" type="button" role="tab">{{ __('Converted') }}</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">{{ __('Rejected') }}</button>
            </li>
        </ul>

        <div class="tab-content" id="kycTabsContent">
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                <div class="row">
                    <!-- Lead Card 1 -->
                    @foreach(['Rajesh Kumar' => 'primary', 'Suresh Raina' => 'success', 'Amit Shah' => 'warning', 'Priya Singh' => 'info'] as $name => $color)
                    <div class="col-12">
                        <div class="card lead-card shadow-sm border-{{ $color == 'primary' ? 'fresh' : ($color == 'warning' ? 'pending' : ($color == 'success' ? 'verified' : 'rejected')) }}">
                            <div class="card-body py-3">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="avatar-circle bg-{{ $color }}">{{ substr($name, 0, 1) }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="mb-0">{{ $name }}</h6>
                                        <small class="text-muted">+91 98765 43210</small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="text-muted small mb-1">{{ __('City') }}</div>
                                        <div class="fw-bold">Mumbai</div>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="text-muted small mb-1">{{ __('Status') }}</div>
                                        <span class="status-badge bg-soft-{{ $color }}">{{ $color == 'primary' ? 'Fresh' : ($color == 'warning' ? 'Pending' : ($color == 'success' ? 'Verified' : 'Rejected')) }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="text-muted">{{ __('Progress') }}</small>
                                            <small class="fw-bold">{{ $color == 'success' ? '100' : ($color == 'warning' ? '40' : '10') }}%</small>
                                        </div>
                                        <div class="progress progress-slim">
                                            <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $color == 'success' ? '100' : ($color == 'warning' ? '40' : '10') }}%"></div>
                                        </div>
                                    </div>
                                    <div class="col-auto ms-auto">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('client.kyc.journey', 1) }}"><i class="ti ti-eye me-2"></i>{{ __('View Journey') }}</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="ti ti-mail me-2"></i>{{ __('Resend Link') }}</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#"><i class="ti ti-trash me-2"></i>{{ __('Delete') }}</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <!-- Other tab panes would follow the same structure -->
            <div class="tab-pane fade" id="open" role="tabpanel">...</div>
            <div class="tab-pane fade" id="converted" role="tabpanel">...</div>
            <div class="tab-pane fade" id="rejected" role="tabpanel">...</div>
        </div>
    </div>
</div>
@endsection
