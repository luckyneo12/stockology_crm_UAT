@extends('layouts.main')

@section('page-title')
    {{ __('My Tasks') }}
@endsection

@section('page-breadcrumb')
    {{ __('CRM') }},
    {{ __('My Tasks') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('All My Tasks') }}</h5>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Task') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Time') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks as $task)
                                    <tr>
                                        <td>{{ $task->name }}</td>
                                        <td>{{ company_date_formate($task->date) }}</td>
                                        <td>{{ $task->time }}</td>
                                        <td>
                                            @if ($task->priority == 1)
                                                <span class="badge bg-success p-2 px-3 rounded">{{ __('Low') }}</span>
                                            @elseif($task->priority == 2)
                                                <span class="badge bg-warning p-2 px-3 rounded">{{ __('Medium') }}</span>
                                            @else
                                                <span class="badge bg-danger p-2 px-3 rounded">{{ __('High') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</td>
                                        <td class="text-end">
                                            <div class="action-btn d-flex justify-content-end">
                                                <a href="#" class="mx-2 btn btn-sm align-items-center bg-warning" 
                                                   data-url="{{ route('leads.tasks.edit', [$task->lead_id, $task->id]) }}"
                                                   data-ajax-popup="true"
                                                   data-title="{{ __('Edit Task') }}"
                                                   data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                    <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                </a>
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['leads.tasks.destroy', $task->lead_id, $task->id], 'class' => 'd-inline']) !!}
                                                <a href="#!" class="btn btn-sm align-items-center show_confirm bg-danger" 
                                                   data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                   data-confirm="{{ __('Are You Sure?') }}" 
                                                   data-text="{{ __('This action can not be undone. Do you want to continue?') }}">
                                                    <span class="text-white"><i class="ti ti-trash"></i></span>
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
            </div>
        </div>
    </div>
@endsection
