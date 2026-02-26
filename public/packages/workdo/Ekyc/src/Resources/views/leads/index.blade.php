@extends('layouts.main')

@section('page-title')
    {{ __('Manage eKYC Leads') }}
@endsection

@section('page-breadcrumb')
    {{ __('eKYC') }}, {{ __('Leads') }}
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
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="assets">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Phone') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Assigned To') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($leads as $lead)
                                    <tr>
                                        <td>{{ $lead->name }}</td>
                                        <td>{{ $lead->email }}</td>
                                        <td>{{ $lead->phone ?? '-' }}</td>
                                        <td>
                                            @php
                                                $status_colors = [
                                                    'fresh' => 'primary',
                                                    'in-progress' => 'info',
                                                    'pending' => 'warning',
                                                    'verified' => 'success',
                                                    'rejected' => 'danger'
                                                ];
                                                $color = $status_colors[$lead->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }} p-2 px-3 rounded">{{ ucfirst($lead->status) }}</span>
                                        </td>
                                        <td>{{ !empty($lead->assignedUser) ? $lead->assignedUser->name : '-' }}</td>
                                        <td>{{ company_date_formate($lead->created_at) }}</td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="{{ route('client.kyc.journey', $lead->id) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('View Journey') }}" target="_blank">
                                                        <i class="ti ti-eye text-white"></i>
                                                    </a>
                                                </div>
                                                @can('ekyc manage')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#" data-url="{{ route('ekyc-leads.edit', $lead->id) }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Edit Lead') }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                            <i class="ti ti-pencil text-white"></i>
                                                        </a>
                                                    </div>
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['ekyc-leads.destroy', $lead->id], 'id' => 'delete-form-' . $lead->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action can not be undone. Do you want to continue?') }}" data-confirm-yes="delete-form-{{ $lead->id }}">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                @endcan
                                            </span>
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
