@extends('layouts.admin')
@section('page-title')
    {{ __('Message Audit Log') }}
@endsection
@section('title')
    {{ __('Message Audit Log') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Message Audit Log') }}</li>
@endsection
@section('action-btn')
    
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="d-flex align-items-center justify-content-between">
                         <h5 class="mb-0">{{ __('Audit Logs') }}</h5>
                        <form action="{{ route('messenger.audit') }}" method="GET" class="d-flex align-items-center gap-2">
                            <select name="user_id" class="form-control select2">
                                <option value="">{{ __('Select User') }}</option>
                                @foreach($users as $id => $name)
                                    <option value="{{ $id }}" {{ request('user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <input type="date" name="date_start" value="{{ request('date_start') }}" class="form-control" placeholder="Start Date">
                            <input type="date" name="date_end" value="{{ request('date_end') }}" class="form-control" placeholder="End Date">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-search"></i></button>
                            <a href="{{ route('messenger.audit') }}" class="btn btn-danger btn-sm"><i class="ti ti-refresh"></i></a>
                        </form>
                    </div>
                   
                    <div class="table-responsive mt-3">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Action') }}</th>
                                    <th>{{ __('Performed By') }}</th>
                                    <th>{{ __('Message Content') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if($log->action == 'soft_delete')
                                                <span class="badge bg-warning">{{ __('User Deleted') }}</span>
                                            @elseif($log->action == 'force_delete')
                                                <span class="badge bg-danger">{{ __('Force Deleted') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $log->action }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $log->performer ? $log->performer->name : 'Unknown' }}</td>
                                        <td>
                                            @if($log->message_content_snapshot)
                                                {{ \Illuminate\Support\Str::limit($log->message_content_snapshot, 50) }}
                                            @elseif($log->file_path_snapshot)
                                                <i class="ti ti-file"></i> {{ basename($log->file_path_snapshot) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->message && $log->message->deleted_at)
                                                {{ __('Soft Deleted') }}
                                            @elseif(!$log->message && $log->message_id)
                                                {{ __('Permanently Deleted') }}
                                            @else
                                                {{ __('Active') }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->message)
                                                <a href="{{ route('messenger.audit.force_delete', $log->id) }}" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('Are you sure you want to permanently delete this message? This cannot be undone.') }}')">
                                                    <i class="ti ti-trash"></i> {{ __('Delete Permanently') }}
                                                </a>
                                            @else
                                                <button class="btn btn-secondary btn-sm" disabled>{{ __('Already Deleted') }}</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end">
                            {{ $logs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
