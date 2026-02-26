@extends('layouts.main')

@section('page-title')
    {{ __('Edit Visibility Rule') }}
@endsection

@section('page-breadcrumb')
    {{ __('CRM') }},
    {{ __('Visibility Settings') }},
    {{ __('Edit Rule') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-6 col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Edit Visibility Rule') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('leads.visibility.update', $visibility->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="field_name" class="form-label">{{ __('Field Name') }}</label>
                            <select name="field_name" class="form-control" required data-trigger>
                                <option value="">{{ __('Select Field') }}</option>
                                @foreach ($fields as $key => $label)
                                    <option value="{{ $key }}" {{ $visibility->field_name == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="role_id" class="form-label">{{ __('Restrict for Role') }}</label>
                            <select name="role_id" class="form-control">
                                <option value="">{{ __('All Roles') }}</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ $visibility->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('Leave empty to apply to all users.') }}</small>
                        </div>
                        <div class="form-group">
                            <label for="pipeline_id" class="form-label">{{ __('Pipeline') }}</label>
                            <select name="pipeline_id" class="form-control">
                                <option value="">{{ __('Select Pipeline (Optional)') }}</option>
                                @foreach ($pipelines as $pipeline)
                                    <option value="{{ $pipeline->id }}" {{ $visibility->pipeline_id == $pipeline->id ? 'selected' : '' }}>{{ $pipeline->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="stage_id" class="form-label">{{ __('Stage') }}</label>
                            <select name="stage_id[]" id="stage_id" class="form-control" multiple data-trigger>
                                <option value="">{{ __('All Stages') }}</option>
                                <!-- Stages will be loaded via JS -->
                            </select>
                            <small class="text-muted">{{ __('Select pipeline first to load stages') }}</small>
                        </div>
                        <div class="form-group">
                            <label for="encryption_type" class="form-label">{{ __('Visibility Type') }}</label>
                            <select name="encryption_type" id="encryption_type" class="form-control" required data-trigger>
                                <option value="none" {{ $visibility->encryption_type == 'none' ? 'selected' : '' }}>{{ __('Visible') }}</option>
                                <option value="mask" {{ $visibility->encryption_type == 'mask' ? 'selected' : '' }}>{{ __('Mask (Eye Toggle)') }}</option>
                                <option value="hide" {{ $visibility->encryption_type == 'hide' ? 'selected' : '' }}>{{ __('Hide Completely') }}</option>
                            </select>
                        </div>
                        <div class="form-group" id="masking_type_group" style="display: none;">
                            <label for="masking_type" class="form-label">{{ __('Masking Type') }}</label>
                            <select name="masking_type" class="form-control" data-trigger>
                                <option value="partial" {{ $visibility->masking_type == 'partial' ? 'selected' : '' }}>{{ __('Partial (Show Last 4 Chars)') }}</option>
                                <option value="full" {{ $visibility->masking_type == 'full' ? 'selected' : '' }}>{{ __('Full Mask (All Hidden)') }}</option>
                            </select>
                            <small class="text-muted">{{ __('Only applies when Visibility Type is "Mask"') }}</small>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('leads.visibility.settings') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('Update Rule') }}</button>
                        </div>
                    </form>
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
        function loadStages(pipelineId, selectedStageIds) {
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
                                var isSelected = false;
                                if (selectedStageIds && Array.isArray(selectedStageIds)) {
                                     isSelected = selectedStageIds.includes(String(stage.id)) || selectedStageIds.includes(stage.id);
                                }
                                
                                choicesArray.push({
                                    value: stage.id,
                                    label: stage.name,
                                    selected: isSelected,
                                    disabled: false,
                                });
                            });
                        }
                        
                         if (stageChoices) {
                            stageChoices.setChoices(choicesArray, 'value', 'label', true);
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
        }

        // Initial load if pipeline is selected
        var currentPipeline = $('select[name="pipeline_id"]').val();
        
        // Prepare selected stages
        var currentStages = [];
        @if($visibility->stage_id)
            currentStages = {!! json_encode(explode(',', $visibility->stage_id)) !!};
        @endif
        
        if(currentPipeline) {
            loadStages(currentPipeline, currentStages);
        }

        $('select[name="pipeline_id"]').on('change', function() {
            var pipelineId = $(this).val();
            loadStages(pipelineId, []);
        });
    });
</script>
@endpush
