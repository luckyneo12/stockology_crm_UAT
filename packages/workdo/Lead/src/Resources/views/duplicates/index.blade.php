@extends('layouts.main')
@section('page-title')
    {{ __('Manage Duplicate Leads') }}
@endsection
@section('page-breadcrumb')
    {{ __('Lead') }}, {{ __('Duplicates') }}
@endsection

@push('css')
<style>
    .duplicate-group {
        border-left: 5px solid var(--theme-color);
        margin-bottom: 2rem;
        background: #fff;
        border-radius: 0 10px 10px 0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .group-header {
        padding: 1rem 1.5rem;
        background: #f8f9fd;
        border-bottom: 1px solid #edf1f9;
        font-weight: 700;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .badge-duplicate {
        background: #ff3a6e;
        color: #fff;
        padding: 0.5em 1em;
        border-radius: 30px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-email-tab" data-bs-toggle="pill" data-bs-target="#pills-email" type="button" role="tab" aria-controls="pills-email" aria-selected="true">{{ __('By Email') }} ({{ count($leadsByEmail) }})</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-phone-tab" data-bs-toggle="pill" data-bs-target="#pills-phone" type="button" role="tab" aria-controls="pills-phone" aria-selected="false">{{ __('By Phone') }} ({{ count($leadsByPhone) }})</button>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-email" role="tabpanel" aria-labelledby="pills-email-tab">
                        @if(count($leadsByEmail) > 0)
                            @foreach($leadsByEmail as $email => $leads)
                                <div class="duplicate-group">
                                    <div class="group-header">
                                        <span>{{ __('Email') }}: <strong>{{ $email }}</strong></span>
                                        <span class="badge badge-duplicate">{{ count($leads) }} {{ __('Duplicates') }}</span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Pipeline') }}</th>
                                                    <th>{{ __('Stage') }}</th>
                                                    <th>{{ __('Created At') }}</th>
                                                    <th class="text-end">{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($leads as $lead)
                                                    <tr>
                                                        <td>{{ $lead->name }}</td>
                                                        <td>{{ !empty($lead->pipeline) ? $lead->pipeline->name : '-' }}</td>
                                                        <td>{{ !empty($lead->stage) ? $lead->stage->name : '-' }}</td>
                                                        <td>{{ $lead->created_at->diffForHumans() }}</td>
                                                        <td class="text-end">
                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['leads.duplicates.destroy', $lead->id]]) !!}
                                                                <a href="#!" class="mx-3 btn btn-sm align-items-center show_confirm" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center p-5">
                                <i class="ti ti-circle-check text-success display-1"></i>
                                <h4 class="mt-3">{{ __('No Duplicate Emails Found!') }}</h4>
                            </div>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="pills-phone" role="tabpanel" aria-labelledby="pills-phone-tab">
                        @if(count($leadsByPhone) > 0)
                            @foreach($leadsByPhone as $phone => $leads)
                                <div class="duplicate-group">
                                    <div class="group-header">
                                        <span>{{ __('Phone') }}: <strong>{{ $phone }}</strong></span>
                                        <span class="badge badge-duplicate">{{ count($leads) }} {{ __('Duplicates') }}</span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Pipeline') }}</th>
                                                    <th>{{ __('Stage') }}</th>
                                                    <th>{{ __('Created At') }}</th>
                                                    <th class="text-end">{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($leads as $lead)
                                                    <tr>
                                                        <td>{{ $lead->name }}</td>
                                                        <td>{{ !empty($lead->pipeline) ? $lead->pipeline->name : '-' }}</td>
                                                        <td>{{ !empty($lead->stage) ? $lead->stage->name : '-' }}</td>
                                                        <td>{{ $lead->created_at->diffForHumans() }}</td>
                                                        <td class="text-end">
                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['leads.duplicates.destroy', $lead->id]]) !!}
                                                                <a href="#!" class="mx-3 btn btn-sm align-items-center show_confirm" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center p-5">
                                <i class="ti ti-circle-check text-success display-1"></i>
                                <h4 class="mt-3">{{ __('No Duplicate Phone Numbers Found!') }}</h4>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
