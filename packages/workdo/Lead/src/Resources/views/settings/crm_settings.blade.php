@extends('layouts.main')

@section('page-title')
    {{ __('CRM Settings') }}
@endsection

@section('page-breadcrumb')
    {{ __('Setup') }},
    {{ __('CRM Settings') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-3 col-12">
            @include('lead::layouts.system_setup')
        </div>
        <div class="col-xl-9">
            <div class="card shadow-sm border-0 glass-effect">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-1 text-primary">{{ __('Duplicate Prevention Settings') }}</h5>
                            <p class="text-xs text-muted mb-0">{{ __('Select fields that should be checked for duplicates in real-time during Lead creation.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-body mt-4">
                    <form action="{{ route('crm.settings.save') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-4">
                                <div class="alert alert-info border-0 shadow-sm bg-light-info">
                                    <div class="d-flex align-items-center">
                                        <div class="alert-icon-me-2">
                                            <i class="ti ti-info-circle f-20 text-info"></i>
                                        </div>
                                        <div class="ms-2">
                                            {{ __('Enabling duplicate check for a field will alert the user instantly if they enter a value that already exists in your workspace.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <h6 class="mb-3">{{ __('Fields to Check') }}</h6>
                                <div class="row g-3">
                                    @foreach ($fields as $key => $label)
                                        <div class="col-md-4">
                                            <div class="form-check form-switch custom-switch-v1 mb-2">
                                                <input type="checkbox" class="form-check-input input-primary" 
                                                       name="duplicate_fields[]" value="{{ $key }}" 
                                                       id="field_{{ $key }}"
                                                       {{ in_array($key, $duplicateFields) ? 'checked' : '' }}>
                                                <label class="form-check-label f-w-500" for="field_{{ $key }}">{{ __($label) }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center shadow-sm">
                                <i class="ti ti-device-floppy me-2"></i>
                                {{ __('Save Configuration') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Workflow Automation Card -->
            <div class="card shadow-sm border-0 glass-effect mt-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-1 text-primary">{{ __('Lead Copy Automation Workflows') }}</h5>
                            <p class="text-xs text-muted mb-0">{{ __('Automatically copy a Lead to another Pipeline and Stage when it reaches a specific Stage.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-body mt-4">
                    <form action="{{ route('crm.settings.save') }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush" id="workflow-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('When Lead reaches Pipeline') }}</th>
                                        <th>{{ __('And Stage') }}</th>
                                        <th>{{ __('Copy it to Pipeline') }}</th>
                                        <th>{{ __('At Stage') }}</th>
                                        <th class="text-end">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workflows as $index => $wf)
                                        <tr class="workflow-row" data-index="{{ $index }}">
                                            <td>
                                                <select name="workflows[{{ $index }}][from_pipeline_id]" class="form-select from-pipeline" required>
                                                    <option value="">{{ __('Select Pipeline') }}</option>
                                                    @foreach($pipelines as $p)
                                                        <option value="{{ $p->id }}" {{ $p->id == $wf['from_pipeline_id'] ? 'selected' : '' }}>{{ $p->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="workflows[{{ $index }}][from_stage_id]" class="form-select from-stage" required>
                                                    <option value="">{{ __('Select Stage') }}</option>
                                                    @php
                                                        $fromStages = $pipelines->where('id', $wf['from_pipeline_id'])->first()?->leadStages ?? collect();
                                                    @endphp
                                                    @foreach($fromStages as $stage)
                                                        <option value="{{ $stage->id }}" {{ $stage->id == $wf['from_stage_id'] ? 'selected' : '' }}>{{ $stage->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="workflows[{{ $index }}][to_pipeline_id]" class="form-select to-pipeline" required>
                                                    <option value="">{{ __('Select Pipeline') }}</option>
                                                    @foreach($pipelines as $p)
                                                        <option value="{{ $p->id }}" {{ $p->id == $wf['to_pipeline_id'] ? 'selected' : '' }}>{{ $p->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="workflows[{{ $index }}][to_stage_id]" class="form-select to-stage" required>
                                                    <option value="">{{ __('Select Stage') }}</option>
                                                    @php
                                                        $toStages = $pipelines->where('id', $wf['to_pipeline_id'])->first()?->leadStages ?? collect();
                                                    @endphp
                                                    @foreach($toStages as $stage)
                                                        <option value="{{ $stage->id }}" {{ $stage->id == $wf['to_stage_id'] ? 'selected' : '' }}>{{ $stage->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-danger remove-workflow-btn"><i class="ti ti-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-between">
                            <button type="button" class="btn btn-sm btn-secondary" id="add-workflow-btn">
                                <i class="ti ti-plus me-1"></i> {{ __('Add Rule') }}
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> {{ __('Save Configuration') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const pipelinesData = @json($pipelines);

            function getStagesForPipeline(pipelineId) {
                const pipeline = pipelinesData.find(p => p.id == pipelineId);
                return pipeline ? pipeline.lead_stages : [];
            }

            function populateStages(pipelineSelect, stageSelect, selectedStageId = null) {
                const pipelineId = pipelineSelect.value;
                const stages = getStagesForPipeline(pipelineId);
                
                stageSelect.innerHTML = '<option value="">{{ __("Select Stage") }}</option>';
                stages.forEach(stage => {
                    const opt = document.createElement('option');
                    opt.value = stage.id;
                    opt.textContent = stage.name;
                    if (stage.id == selectedStageId) {
                        opt.selected = true;
                    }
                    stageSelect.appendChild(opt);
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                let rowCount = document.querySelectorAll('.workflow-row').length;

                // Handle Pipeline Change dynamically
                document.addEventListener('change', function(e) {
                    if (e.target.classList.contains('from-pipeline')) {
                        const row = e.target.closest('tr');
                        const stageSelect = row.querySelector('.from-stage');
                        populateStages(e.target, stageSelect);
                    }
                    if (e.target.classList.contains('to-pipeline')) {
                        const row = e.target.closest('tr');
                        const stageSelect = row.querySelector('.to-stage');
                        populateStages(e.target, stageSelect);
                    }
                });

                // Remove rule row
                document.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-workflow-btn') || e.target.closest('.remove-workflow-btn')) {
                        const btn = e.target.classList.contains('remove-workflow-btn') ? e.target : e.target.closest('.remove-workflow-btn');
                        btn.closest('tr').remove();
                    }
                });

                // Add rule row
                document.getElementById('add-workflow-btn').addEventListener('click', function() {
                    const tbody = document.querySelector('#workflow-table tbody');
                    const index = rowCount++;

                    let pipelineOptions = '<option value="">{{ __("Select Pipeline") }}</option>';
                    pipelinesData.forEach(p => {
                        pipelineOptions += `<option value="${p.id}">${p.name}</option>`;
                    });

                    const newRow = document.createElement('tr');
                    newRow.className = 'workflow-row';
                    newRow.dataset.index = index;
                    newRow.innerHTML = `
                        <td>
                            <select name="workflows[\${index}][from_pipeline_id]" class="form-select from-pipeline" required>
                                \${pipelineOptions}
                            </select>
                        </td>
                        <td>
                            <select name="workflows[\${index}][from_stage_id]" class="form-select from-stage" required>
                                <option value="">{{ __("Select Stage") }}</option>
                            </select>
                        </td>
                        <td>
                            <select name="workflows[\${index}][to_pipeline_id]" class="form-select to-pipeline" required>
                                \${pipelineOptions}
                            </select>
                        </td>
                        <td>
                            <select name="workflows[\${index}][to_stage_id]" class="form-select to-stage" required>
                                <option value="">{{ __("Select Stage") }}</option>
                            </select>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-danger remove-workflow-btn"><i class="ti ti-trash"></i></button>
                        </td>
                    `;
                    tbody.appendChild(newRow);
                });
            });
        </script>
    @endpush
@endsection

<style>
    .glass-effect {
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .bg-light-info {
        background-color: rgba(63, 153, 222, 0.1) !important;
    }
    .custom-switch-v1 .form-check-input:checked {
        background-color: #584ed2;
        border-color: #584ed2;
    }
</style>
