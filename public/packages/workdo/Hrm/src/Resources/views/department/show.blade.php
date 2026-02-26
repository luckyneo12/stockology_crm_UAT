@extends('layouts.main')
@section('page-title')
    {{ __('Department Details') }}
@endsection
@section('page-breadcrumb')
    {{ __('Department') }}
@endsection
@section('page-action')
    <div class="text-end">
        @permission('employee create')
            <a href="#" data-size="md" data-url="{{ route('department.add_employee', $department->id) }}" data-ajax-popup="true" data-title="{{ __('Add Employee') }}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i> {{ __('Add Employee') }}
            </a>
        @endpermission
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h5>{{ $department->name }}</h5>
                        </div>
                        <div class="col-6 text-end">
                            <h5 class="text-muted">{{ __('Branch') }}: {{ $department->branch->name ?? '-' }}</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employees as $employee)
                                    <tr>
                                        <td>{{ $employee->name }}</td>
                                        <td>{{ $employee->user->type ?? '-' }}</td>
                                        <td class="Action">
                                            @permission('department edit')
                                                <div class="action-btn">
                                                    {{ Form::open(['route' => ['department.remove_employee', $department->id, $employee->id], 'class' => 'm-0']) }}
                                                    @method('DELETE')
                                                    <a class="mx-3 btn bg-danger btn-sm align-items-center bs-pass-para show_confirm"
                                                        data-bs-toggle="tooltip" title="{{ __('Remove from Department') }}"
                                                        data-bs-original-title="Remove" aria-label="Delete"
                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                        data-text="{{ __('This action will remove the employee from this department.') }}"
                                                        data-confirm-yes="delete-form-{{ $employee->id }}">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {{ Form::close() }}
                                                </div>
                                            @endpermission
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
