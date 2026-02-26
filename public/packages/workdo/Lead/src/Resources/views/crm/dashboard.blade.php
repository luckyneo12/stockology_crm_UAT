@extends('layouts.main')

@section('page-title')
    {{ __('CRM Dashboard') }}
@endsection

@section('page-breadcrumb')
    {{ __('CRM Dashboard') }}
@endsection

@section('content')
    <div class="row">
        <!-- Summary Cards -->
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="theme-avtar bg-primary">
                        <i class="ti ti-list-check"></i>
                    </div>
                    <p class="text-muted text-sm mt-4 mb-2">{{ __('Completion Rate') }}</p>
                    <h6 class="mb-3">{{ __('Tasks Completed') }}</h6>
                    <h3 class="mb-0">{{ $completionRate }}% <span class="text-sm text-muted">({{ $completedTasks }}/{{ $totalTasks }})</span></h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="theme-avtar bg-warning">
                        <i class="ti ti-clock"></i>
                    </div>
                    <p class="text-muted text-sm mt-4 mb-2">{{ __('Pending') }}</p>
                    <h6 class="mb-3">{{ __('Pending Tasks') }}</h6>
                    <h3 class="mb-0">{{ count($tasks) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="theme-avtar bg-info">
                        <i class="ti ti-bell"></i>
                    </div>
                    <p class="text-muted text-sm mt-4 mb-2">{{ __('Upcoming') }}</p>
                    <h6 class="mb-3">{{ __('Active Reminders') }}</h6>
                    <h3 class="mb-0">{{ count($reminders) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- My Tasks -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('My Pending Tasks') }}</h5>
                    <a href="{{ route('leads.my.tasks') }}" class="btn btn-sm btn-primary">{{ __('View All') }}</a>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Task') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tasks->take(5) as $task)
                                    <tr>
                                        <td>{{ $task->name }}</td>
                                        <td>{{ company_date_formate($task->date) }}</td>
                                        <td>
                                            @if ($task->priority == 'high')
                                                <span class="badge bg-danger p-2 px-3 rounded">{{ __('High') }}</span>
                                            @elseif($task->priority == 'medium')
                                                <span class="badge bg-warning p-2 px-3 rounded">{{ __('Medium') }}</span>
                                            @else
                                                <span class="badge bg-success p-2 px-3 rounded">{{ __('Low') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $task->status)) }}</td>
                                    </tr>
                                @empty
                                    @include('layouts.nodatafound')
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Reminders -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('Upcoming Reminders') }}</h5>
                    <a href="{{ route('leads.my.reminders') }}" class="btn btn-sm btn-primary">{{ __('View All') }}</a>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Type') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reminders->take(5) as $reminder)
                                    @php
                                        // Dynamic Color Logic
                                        $remindAt = \Carbon\Carbon::parse($reminder->remind_at);
                                        $now = now();
                                        $colorClass = 'bg-success'; // Future
                                        if($remindAt->lt($now)) {
                                            $colorClass = 'bg-danger'; // Overdue/Now
                                        } elseif($remindAt->diffInHours($now) < 24) {
                                            $colorClass = 'bg-warning'; // Soon
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $reminder->title }}</td>
                                        <td>
                                            <span class="badge {{ $colorClass }} p-2 px-3 rounded">
                                                {{ company_datetime_formate($reminder->remind_at) }}
                                            </span>
                                        </td>
                                        <td>{{ ucfirst($reminder->type) }}</td>
                                    </tr>
                                @empty
                                    @include('layouts.nodatafound')
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
