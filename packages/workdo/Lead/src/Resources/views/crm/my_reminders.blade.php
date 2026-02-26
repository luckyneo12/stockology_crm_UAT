@extends('layouts.main')

@section('page-title')
    {{ __('My Reminders') }}
@endsection

@section('page-breadcrumb')
    {{ __('CRM') }},
    {{ __('My Reminders') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('All My Reminders') }}</h5>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Reminder Date') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reminders as $reminder)
                                    @php
                                        $remindAt = \Carbon\Carbon::parse($reminder->remind_at);
                                        $now = now();
                                        $colorClass = 'bg-success';
                                        if($remindAt->lt($now)) {
                                            $colorClass = 'bg-danger';
                                        } elseif($remindAt->diffInHours($now) < 24) {
                                            $colorClass = 'bg-warning';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $reminder->title }}</td>
                                        <td>{{ $reminder->description }}</td>
                                        <td>
                                            <span class="badge {{ $colorClass }} p-2 px-3 rounded">
                                                {{ company_datetime_formate($reminder->remind_at) }}
                                            </span>
                                        </td>
                                        <td>{{ ucfirst($reminder->type) }}</td>
                                        <td class="text-end">
                                            {{-- Add actions --}}
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
