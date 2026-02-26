@extends('layouts.main')

@section('page-title')
    {{ __('Test Webhook Endpoint') }}: {{ $endpoint->name }}
@endsection

@section('content')
<div class="row min-vh-100 justify-content-center align-items-center">
    <div class="col-md-7 col-lg-6">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 text-white"><i class="ti ti-plug me-2"></i>{{ __('Webhook Test Form') }}</h5>
                <p class="small mb-0 opacity-75">{{ __('Fill in the data below to test your webhook integration.') }}</p>
            </div>
            <div class="card-body p-4">
                <form action="{{ $webhookUrl }}" method="POST">
                    @csrf
                    <div class="row">
                        @php
                            $inForm = $mapping['in_form'] ?? [];
                        @endphp

                        {{-- Standard Fields --}}
                        @if(isset($inForm['name']))
                            <div class="form-group col-12 mb-3">
                                <label class="form-label font-weight-bold">{{ __('Lead Name') }}</label>
                                <input type="text" name="{{ $mapping['name'] ?? 'name' }}" class="form-control" placeholder="{{ __('Enter name') }}" required>
                                <small class="text-muted text-xs">{{ __('Maps to JSON key') }}: <code>{{ $mapping['name'] ?? 'name' }}</code></small>
                            </div>
                        @endif

                        @if(isset($inForm['email']))
                            <div class="form-group col-12 mb-3">
                                <label class="form-label font-weight-bold">{{ __('Email Address') }}</label>
                                <input type="email" name="{{ $mapping['email'] ?? 'email' }}" class="form-control" placeholder="{{ __('Enter email') }}">
                                <small class="text-muted text-xs">{{ __('Maps to JSON key') }}: <code>{{ $mapping['email'] ?? 'email' }}</code></small>
                            </div>
                        @endif

                        @if(isset($inForm['phone']))
                            <div class="form-group col-12 mb-3">
                                <label class="form-label font-weight-bold">{{ __('Phone Number') }}</label>
                                <input type="text" name="{{ $mapping['phone'] ?? 'phone' }}" class="form-control" placeholder="{{ __('Enter phone') }}">
                                <small class="text-muted text-xs">{{ __('Maps to JSON key') }}: <code>{{ $mapping['phone'] ?? 'phone' }}</code></small>
                            </div>
                        @endif

                        @if(isset($inForm['subject']))
                            <div class="form-group col-12 mb-3">
                                <label class="form-label font-weight-bold">{{ __('Subject') }}</label>
                                <input type="text" name="{{ $mapping['subject'] ?? 'subject' }}" class="form-control" placeholder="{{ __('Enter subject') }}">
                                <small class="text-muted text-xs">{{ __('Maps to JSON key') }}: <code>{{ $mapping['subject'] ?? 'subject' }}</code></small>
                            </div>
                        @endif

                        {{-- Custom Fields --}}
                        @if(isset($inForm['custom']) && is_array($inForm['custom']))
                            @foreach($inForm['custom'] as $fieldId => $status)
                                @php
                                    $fieldObj = \Workdo\Lead\Entities\LeadCustomField::find($fieldId);
                                @endphp
                                @if($fieldObj)
                                    <div class="form-group col-12 mb-3">
                                        <label class="form-label font-weight-bold">{{ $fieldObj->name }}</label>
                                        @if($fieldObj->type == 'textarea')
                                            <textarea name="{{ $mapping['custom'][$fieldId] }}" class="form-control" rows="3" placeholder="{{ __('Enter') }} {{ $fieldObj->name }}"></textarea>
                                        @elseif($fieldObj->type == 'date')
                                            <input type="date" name="{{ $mapping['custom'][$fieldId] }}" class="form-control">
                                        @elseif($fieldObj->type == 'number')
                                            <input type="number" name="{{ $mapping['custom'][$fieldId] }}" class="form-control" placeholder="{{ __('Enter') }} {{ $fieldObj->name }}">
                                        @else
                                            <input type="text" name="{{ $mapping['custom'][$fieldId] }}" class="form-control" placeholder="{{ __('Enter') }} {{ $fieldObj->name }}">
                                        @endif
                                        <small class="text-muted text-xs">{{ __('Maps to JSON key') }}: <code>{{ $mapping['custom'][$fieldId] }}</code></small>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>

                    <div class="mt-4 text-end">
                        <a href="{{ route('webhook-endpoints.index') }}" class="btn btn-light me-2">{{ __('Back to List') }}</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="ti ti-send me-1"></i> {{ __('Send Test Data') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-4 text-center">
            <div class="alert alert-info d-inline-block py-2 px-4 rounded-pill">
                <i class="ti ti-info-circle me-1"></i> {{ __('Target API Endpoint') }}: <code class="ms-1">{{ str_replace(url('/'), '', $webhookUrl) }}</code>
            </div>
        </div>
    </div>
</div>

<style>
    .form-label { font-size: 0.9rem; margin-bottom: 0.4rem; color: #344767; }
    .form-control:focus { border-color: #5e72e4; box-shadow: 0 0 0 2px rgba(94, 114, 228, 0.2); }
    .card { border-radius: 1rem; }
    .bg-primary { background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%) !important; }
</style>
@endsection
