@extends('layouts.main')

@section('page-title')
    {{ __('Manage Lead Custom Fields') }}
@endsection

@section('page-breadcrumb')
    {{ __('Lead Custom Fields') }}
@endsection

@section('page-action')
    <div class="d-flex">
        <a href="{{ route('lead-builder.index') }}" class="btn btn-sm btn-info me-2" data-bs-toggle="tooltip" title="{{ __('Manage Layout') }}">
             <i class="ti ti-layout-dashboard text-white"></i> {{ __('Layout Builder') }}
        </a>
        <a class="btn btn-sm btn-primary btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Create Custom Field') }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Create Custom Field') }}" data-url="{{ route('lead-custom-fields.create') }}">
            <i class="ti ti-plus text-white"></i>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="custom-fields">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Required') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customFields as $field)
                                    <tr>
                                        <td>{{ $field->name }}</td>
                                        <td>{{ ucfirst($field->type) }}</td>
                                        <td>{{ $field->is_required ? __('Yes') : __('No') }}</td>
                                        <td class="Action">
                                            <span>
                                                <div class="action-btn me-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bg-info" data-url="{{ route('lead-custom-fields.edit', $field->id) }}" data-ajax-popup="true" data-title="{{ __('Edit Custom Field') }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-original-title="{{ __('Edit') }}">
                                                        <i class="ti ti-pencil text-white"></i>
                                                    </a>
                                                </div>
                                                <div class="action-btn">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['lead-custom-fields.destroy', $field->id], 'id' => 'delete-form-' . $field->id]) !!}
                                                        <a href="#!" class="mx-3 btn btn-sm align-items-center show_confirm bg-danger" data-bs-toggle="tooltip" title="{{ __('Delete') }}" data-original-title="{{ __('Delete') }}" data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action can not be undone. Do you want to continue?') }}">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
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
