@extends('layouts.main')

@section('page-title')
    {{ __('eKYC Performance Reports') }}
@endsection

@section('page-breadcrumb')
    {{ __('eKYC') }}, {{ __('Reports') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Company Performance') }}</h5>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="assets">
                            <thead>
                                <tr>
                                    <th>{{ __('Company Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Total KYC Requests') }}</th>
                                    <th>{{ __('Verified') }}</th>
                                    <th>{{ __('Pending') }}</th>
                                    <th>{{ __('Success Rate') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reportData as $data)
                                    <tr>
                                        <td>{{ $data['company_name'] }}</td>
                                        <td>{{ $data['email'] }}</td>
                                        <td>{{ $data['total_kyc'] }}</td>
                                        <td><span class="text-success">{{ $data['verified_kyc'] }}</span></td>
                                        <td><span class="text-warning">{{ $data['pending_kyc'] }}</span></td>
                                        <td>
                                            <div class="progress" style="height: 6px; width: 100px;">
                                                <div class="progress-bar bg-{{ $data['success_rate'] > 70 ? 'success' : ($data['success_rate'] > 30 ? 'warning' : 'danger') }}" role="progressbar" style="width: {{ $data['success_rate'] }}%" aria-valuenow="{{ $data['success_rate'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small>{{ $data['success_rate'] }}%</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
