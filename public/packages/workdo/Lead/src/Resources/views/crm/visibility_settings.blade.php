@extends('layouts.main')

@section('page-title')
    {{ __('Data Visibility Settings') }}
@endsection

@section('page-breadcrumb')
    {{ __('CRM') }},
    {{ __('Visibility Settings') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Create Visibility Rule') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('leads.visibility.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="field_name" class="form-label">{{ __('Field Name') }}</label>
                            <select name="field_name" class="form-control" required data-trigger>
                                <option value="">{{ __('Select Field') }}</option>
                                @foreach ($fields as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="role_id" class="form-label">{{ __('Restrict for Role') }}</label>
                            <select name="role_id" class="form-control">
                                <option value="">{{ __('All Roles') }}</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('Leave empty to apply to all users.') }}</small>
                        </div>
                        <div class="form-group">
                            <label for="pipeline_id" class="form-label">{{ __('Pipeline') }}</label>
                            <select name="pipeline_id" class="form-control">
                                <option value="">{{ __('Select Pipeline (Optional)') }}</option>
                                @foreach ($pipelines as $pipeline)
                                    <option value="{{ $pipeline->id }}">{{ $pipeline->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="stage_id" class="form-label">{{ __('Stage') }}</label>
                            <select name="stage_id[]" id="stage_id" class="form-control" multiple>
                                <option value="">{{ __('All Stages') }}</option>
                            </select>
                            <small class="text-muted">{{ __('Select pipeline first to load stages') }}</small>
                        </div>
                        <div class="form-group">
                            <label for="encryption_type" class="form-label">{{ __('Visibility Type') }}</label>
                            <select name="encryption_type" id="encryption_type" class="form-control" required data-trigger>
                                <option value="none">{{ __('Visible') }}</option>
                                <option value="mask">{{ __('Mask (Eye Toggle)') }}</option>
                                <option value="hide">{{ __('Hide Completely') }}</option>
                            </select>
                        </div>
                        <div class="form-group" id="masking_type_group">
                            <label for="masking_type" class="form-label">{{ __('Masking Type') }}</label>
                            <select name="masking_type" class="form-control" data-trigger>
                                <option value="partial">{{ __('Partial (Show Last 4 Chars)') }}</option>
                                <option value="full">{{ __('Full Mask (All Hidden)') }}</option>
                            </select>
                            <small class="text-muted">{{ __('Only applies when Visibility Type is "Mask"') }}</small>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">{{ __('Save Rule') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Existing Rules') }}</h5>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Field') }}</th>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Pipeline') }}</th>
                                    <th>{{ __('Stage') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($visibilities as $rule)
                                    <tr>
                                        <td>{{ $fields[$rule->field_name] ?? $rule->field_name }}</td>
                                        <td>{{ $rule->role->name ?? __('All') }}</td>
                                        <td>{{ $rule->pipeline_id ?? __('Any') }}</td>
                                        <td>
                                            @if($rule->stage_id)
                                                @php
                                                    $stageIds = explode(',', $rule->stage_id);
                                                    $stageNames = \Workdo\Lead\Entities\LeadStage::whereIn('id', $stageIds)->pluck('name')->toArray();
                                                    echo implode(', ', $stageNames);
                                                @endphp
                                            @else
                                                {{ __('All') }}
                                            @endif
                                        </td>
                                         <td>
                                            @if($rule->encryption_type == 'mask')
                                                <span class="badge bg-warning">{{ __('Masked') }}</span>
                                                @if($rule->masking_type == 'partial')
                                                    <small>({{ __('Partial') }})</small>
                                                @else
                                                    <small>({{ __('Full') }})</small>
                                                @endif
                                            @elseif($rule->encryption_type == 'hide')
                                                <span class="badge bg-danger">{{ __('Hidden') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ __('Visible') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-btn d-flex">
                                                <a href="#" class="mx-2 btn btn-sm align-items-center bg-warning edit-visibility" 
                                                   data-id="{{ $rule->id }}"
                                                   data-bs-toggle="tooltip" title="{{ __('Edit') }}">
                                                    <span class="text-white"><i class="ti ti-pencil"></i></span>
                                                </a>
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['leads.visibility.delete', $rule->id], 'id' => 'delete-visibility-' . $rule->id, 'class' => 'd-inline']) !!}
                                                <a href="#!" class="btn btn-sm align-items-center show_confirm bg-danger" 
                                                   data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Delete') }}"
                                                   data-confirm="{{ __('Are You Sure?') }}" 
                                                   data-text="{{ __('This action can not be undone. Do you want to continue?') }}">
                                                    <span class="text-white"><i class="ti ti-trash"></i></span>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                        </td>
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

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle masking type field visibility
        function toggleMaskingType() {
            if ($('#encryption_type').val() === 'mask') {
                $('#masking_type_group').show();
            } else {
                $('#masking_type_group').hide();
            }
        }
        
        toggleMaskingType();
        $('#encryption_type').on('change', toggleMaskingType);
        
        var stageChoices;
        
        // Initialize choices manually for stage_id to control it
        var stageSelectElement = document.getElementById('stage_id');
        if (stageSelectElement) {
            stageChoices = new Choices(stageSelectElement, {
                removeItemButton: true,
            });
        }
        
        // Load stages dynamically when pipeline is selected
        $('select[name="pipeline_id"]').on('change', function() {
            var pipelineId = $(this).val();
            
            if (stageChoices) {
                 stageChoices.clearStore();
                 stageChoices.clearInput();
            }
            
            if (pipelineId) {
                $.ajax({
                    url: '{{ route("leads.get.stages") }}',
                    type: 'GET',
                    data: { pipeline_id: pipelineId },
                    success: function(response) {
                        var choicesArray = [];
                        if (response.stages && response.stages.length > 0) {
                            $.each(response.stages, function(index, stage) {
                                choicesArray.push({
                                    value: stage.id,
                                    label: stage.name,
                                    selected: false,
                                    disabled: false,
                                });
                            });
                        }
                        
                        if (stageChoices) {
                            stageChoices.setChoices(choicesArray, 'value', 'label', true);
                        } else {
                            // Fallback if choices not init
                             var stageSelect = $('#stage_id');
                             stageSelect.html(''); // Clear current
                             $.each(response.stages, function(index, stage) {
                                stageSelect.append('<option value="' + stage.id + '">' + stage.name + '</option>');
                            });
                        }
                    },
                    error: function() {
                         if (stageChoices) {
                            stageChoices.setChoices([], 'value', 'label', true);
                        }
                    }
                });
            } else {
                if (stageChoices) {
                    stageChoices.setChoices([], 'value', 'label', true);
                }
            }
        });
    });
</script>
@endpush
