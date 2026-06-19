{{ Form::model($customField, array('route' => array('lead-custom-fields.update', $customField->id), 'method' => 'PUT','enctype'=>'multipart/form-data')) }}
<div class="modal-body pb-0">
    @if(isset($pipelineId))
        <input type="hidden" name="pipeline_id" value="{{ $pipelineId }}">
    @endif
    <!-- Dynamic Tabs Header -->
    <ul class="nav nav-pills nav-fill bg-light p-1 rounded-3 mb-4" id="custom-field-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-3 py-2" id="general-tab" data-bs-toggle="pill" data-bs-target="#general-panel" type="button" role="tab" aria-controls="general-panel" aria-selected="true">
                <i class="ti ti-settings me-2 fs-5"></i>{{ __('General') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-3 py-2" id="stage-tab" data-bs-toggle="pill" data-bs-target="#stage-panel" type="button" role="tab" aria-controls="stage-panel" aria-selected="false">
                <i class="ti ti-git-branch me-2 fs-5"></i>{{ __('Stage Config') }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-3 py-2" id="api-tab" data-bs-toggle="pill" data-bs-target="#api-panel" type="button" role="tab" aria-controls="api-panel" aria-selected="false">
                <i class="ti ti-api me-2 fs-5"></i>{{ __('API & Access') }}
            </button>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="custom-field-tab-content">
        <!-- Tab 1: General -->
        <div class="tab-pane fade show active" id="general-panel" role="tabpanel" aria-labelledby="general-tab">
            <div class="row">
                <div class="col-12 form-group mb-3">
                    {{ Form::label('name', __('Name'),['class'=>'form-label fw-bold']) }}
                    {{ Form::text('name', null, array('class' => 'form-control','required'=>'required', 'placeholder' => __('Enter field name'))) }}
                </div>
                <div class="col-md-6 form-group mb-3">
                    {{ Form::label('type', __('Type'),['class'=>'form-label fw-bold']) }}
                    {{ Form::select('type', $types, null, array('class' => 'form-control select2', 'required'=>'required', 'id' => 'field_type_edit')) }}
                </div>
                <div class="col-md-6 form-group mb-3">
                     {{ Form::label('icon', __('Field Icon'),['class'=>'form-label fw-bold']) }}
                     {{ Form::text('icon', null, array('class' => 'form-control', 'placeholder' => __('e.g. user, check, star'))) }}
                     <small class="text-muted">{{ __('Feather Icon name. ') }} <a href="https://feathericons.com/" target="_blank" class="text-primary">{{ __('View Icons') }}</a></small>
                </div>
                <div class="col-12 form-group {{ ($customField->type == 'select' || $customField->type == 'multi_select') ? '' : 'd-none' }} mb-3" id="options_area_edit">
                    {{ Form::label('options', __('Options (Comma Separated)'),['class'=>'form-label fw-bold']) }}
                    {{ Form::textarea('options', null, array('class' => 'form-control', 'rows' => 2, 'placeholder' => __('Option 1, Option 2, Option 3'))) }}
                </div>

                <div class="col-12 mt-2">
                    <div class="card bg-light border-0 shadow-none mb-3">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-3"><i class="ti ti-adjustments me-1 text-primary"></i> {{ __('Behavior Rules') }}</h6>
                            <div class="form-check form-switch mb-3">
                                <input type="checkbox" class="form-check-input" name="is_required" id="is_required" {{ $customField->is_required ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_required">{{ __('Required in ALL stages') }}</label>
                                <small class="text-muted d-block ms-0 mt-1">{{ __('Forces this field to be filled across all stages (overrides specific stage configs).') }}</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" class="form-check-input" name="is_filterable" id="is_filterable" {{ $customField->is_filterable ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_filterable">{{ __('Is Filterable (Show in Sidebar)') }}</label>
                                <small class="text-muted d-block ms-0 mt-1">{{ __('Allows users to filter leads by this field in the main view sidebar.') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Stage Settings -->
        <div class="tab-pane fade" id="stage-panel" role="tabpanel" aria-labelledby="stage-tab">
            <style>
                .segmented-control {
                    display: inline-flex;
                    background-color: #f8fafc;
                    border-radius: 50px;
                    padding: 2px;
                    border: 1px solid #cbd5e1;
                    height: 30px;
                    align-items: center;
                    flex-shrink: 0;
                }

                .segmented-control .btn-segment {
                    font-size: 0.7rem !important;
                    font-weight: 600 !important;
                    color: #64748b !important;
                    background: transparent !important;
                    transition: all 0.15s ease-in-out;
                    border: none !important;
                    box-shadow: none !important;
                    height: 24px;
                    padding: 2px 10px !important;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    line-height: 1;
                }

                .segmented-control .btn-segment:hover {
                    color: #1e293b !important;
                    background-color: rgba(0,0,0,0.04) !important;
                    border-radius: 50px !important;
                }

                /* Active States */
                .segmented-control[data-active-val="visible"] .btn-segment[data-value="visible"] {
                    background-color: #d1e7dd !important;
                    color: #0f5132 !important;
                    box-shadow: 0 2px 4px rgba(15, 81, 50, 0.12) !important;
                    border-radius: 50px !important;
                }

                .segmented-control[data-active-val="hidden"] .btn-segment[data-value="hidden"] {
                    background-color: #f8d7da !important;
                    color: #842029 !important;
                    box-shadow: 0 2px 4px rgba(132, 32, 41, 0.12) !important;
                    border-radius: 50px !important;
                }

                .segmented-control[data-active-val="required"] .btn-segment[data-value="required"] {
                    background-color: #e0e6ff !important;
                    color: #2b46b8 !important;
                    box-shadow: 0 2px 4px rgba(43, 70, 184, 0.12) !important;
                    border-radius: 50px !important;
                }
            </style>
            <div class="form-group mb-0">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <label class="form-label fw-bold mb-0">{{ __('Stage-wise Visibility') }}</label>
                        <small class="text-muted d-block mt-1">{{ __('Configure how this custom field is shown or required per stage.') }}</small>
                    </div>
                </div>
                
                <div class="rounded-3 border mb-3 p-3 bg-white shadow-sm" style="max-height: 280px; overflow-y: auto;">
                    @foreach($pipelines as $pipeline)
                        <div class="d-flex align-items-center gap-2 mb-2 @if($loop->first) mt-0 @else mt-4 @endif">
                            <span class="badge bg-light-primary text-primary px-3 py-2 rounded-pill fw-bold" style="font-size: 0.75rem; border: 1px solid rgba(24, 191, 107, 0.15); display: inline-flex; align-items: center;">
                                <i class="ti ti-git-fork me-1"></i> {{ $pipeline->name }} {{ __('Pipeline') }}
                            </span>
                            <div class="flex-grow-1 border-bottom border-light" style="border-bottom-style: dashed !important;"></div>
                        </div>
                        @foreach($pipeline->leadStages as $stage)
                            @php
                                // Determine current config for this stage
                                $currentConfig = 'visible'; // default
                                
                                $isInVisible = !empty($customField->visible_stages) && is_array($customField->visible_stages) && in_array($stage->id, $customField->visible_stages);
                                $isInRequired = !empty($customField->required_stages) && is_array($customField->required_stages) && in_array($stage->id, $customField->required_stages);
                                
                                if ($isInRequired) {
                                    $currentConfig = 'required';
                                } elseif ($isInVisible) {
                                    $currentConfig = 'visible';
                                } elseif (!empty($customField->visible_stages)) {
                                    $currentConfig = 'hidden';
                                }
                            @endphp
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-light" style="border-bottom-style: dashed !important; min-height: 48px;">
                                <div class="d-flex align-items-center gap-2" style="flex: 1; min-width: 0; padding-right: 10px;">
                                    <div class="icon-circle rounded-circle d-flex align-items-center justify-content-center bg-light text-primary" style="width: 24px; height: 24px; flex-shrink: 0;">
                                        <i class="ti ti-git-commit fs-6"></i>
                                    </div>
                                    <span class="fw-semibold text-dark text-truncate" style="font-size: 0.82rem;" title="{{ $stage->name }}">{{ $stage->name }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <select name="stage_config[{{ $stage->id }}]" class="field-stage-select d-none" data-stage-id="{{ $stage->id }}">
                                        <option value="hidden" {{ $currentConfig == 'hidden' ? 'selected' : '' }}>hidden</option>
                                        <option value="visible" {{ $currentConfig == 'visible' ? 'selected' : '' }}>visible</option>
                                        <option value="required" {{ $currentConfig == 'required' ? 'selected' : '' }}>required</option>
                                    </select>
                                    
                                    <div class="segmented-control bg-light p-1 rounded-pill border d-flex gap-1" data-target-stage-id="{{ $stage->id }}" data-active-val="{{ $currentConfig }}">
                                        <button type="button" class="btn btn-xs rounded-pill btn-segment d-flex align-items-center gap-1 border-0" data-value="visible">
                                            <i class="ti ti-eye"></i> <span>{{ __('Visible') }}</span>
                                        </button>
                                        <button type="button" class="btn btn-xs rounded-pill btn-segment d-flex align-items-center gap-1 border-0" data-value="hidden">
                                            <i class="ti ti-eye-off"></i> <span>{{ __('Hidden') }}</span>
                                        </button>
                                        <button type="button" class="btn btn-xs rounded-pill btn-segment d-flex align-items-center gap-1 border-0" data-value="required">
                                            <i class="ti ti-lock"></i> <span>{{ __('Required') }}</span>
                                        </button>
                                    </div>
                                    
                                    <div class="min-value-col {{ $customField->type == 'number' ? '' : 'd-none' }}" style="width: 80px; flex-shrink: 0;">
                                        <input type="number" step="any" name="stage_min_values[{{ $stage->id }}]" value="{{ $customField->stage_min_values[$stage->id] ?? '' }}" class="form-control" style="width: 100%; height: 30px; border-radius: 20px; font-size: 0.72rem; text-align: center; border: 1px solid #cbd5e1; padding: 2px 5px;" placeholder="{{ __('Min') }}">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Tab 3: API & Access -->
        <div class="tab-pane fade" id="api-panel" role="tabpanel" aria-labelledby="api-tab">
            <div class="row">
                <!-- Role Permissions -->
                <div class="col-12 form-group mb-3">
                     {{ Form::label('visible_roles', __('Visible to Roles (Optional)'),['class'=>'form-label fw-bold']) }}
                     {{ Form::select('visible_roles[]', $roles, $customField->visible_roles, array('class' => 'form-control choices-js-modal', 'multiple'=>'multiple', 'id' => 'edit_visible_roles')) }}
                     <small class="text-muted">{{ __('Leave empty to make the field accessible to everyone.') }}</small>
                </div>

                <!-- API Integration -->
                <div class="col-12 mt-2">
                    <div class="card border border-primary-subtle shadow-none mb-0">
                        <div class="card-header bg-light-primary py-2 px-3">
                            <h6 class="fw-bold mb-0 text-primary"><i class="ti ti-api me-1"></i> {{ __('Automation API Trigger') }}</h6>
                        </div>
                        <div class="card-body p-3">
                            <small class="text-muted d-block mb-3">
                                {{ __('Automatically fetch and populate this custom field via an external API call when a lead enters a stage.') }}
                            </small>

                            <div class="mb-3">
                                {{ Form::label('api_url', __('API Endpoint URL'),['class'=>'form-label fw-semibold']) }}
                                {{ Form::text('api_url', null, array('class' => 'form-control', 'placeholder' => 'https://api.example.com/check-status')) }}
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    {{ Form::label('api_method', __('Request Method'),['class'=>'form-label fw-semibold']) }}
                                    {{ Form::select('api_method', ['GET' => 'GET', 'POST' => 'POST'], null, array('class' => 'form-select')) }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    {{ Form::label('api_trigger_stage_id', __('Trigger Stage'),['class'=>'form-label fw-semibold']) }}
                                    <select name="api_trigger_stage_id" class="form-select">
                                        <option value="">{{ __('Select Stage to Trigger API') }}</option>
                                        @foreach($pipelines as $pipeline)
                                            <optgroup label="{{ $pipeline->name }}">
                                                @foreach($pipeline->leadStages as $stage)
                                                    <option value="{{ $stage->id }}" {{ $customField->api_trigger_stage_id == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-0">
                                {{ Form::label('api_response_key', __('API Response Key Path'),['class'=>'form-label fw-semibold']) }}
                                {{ Form::text('api_response_key', null, array('class' => 'form-control', 'placeholder' => 'data.result')) }}
                                <small class="text-muted">{{ __('Supports nested dot-notation (e.g. data.user.status). Leave blank for raw body response.') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer pt-3">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    <button type="submit" class="btn btn-primary">{{__('Update')}}</button>
</div>
{{ Form::close() }}

<script>
    (function() {
        function toggleMinValCol() {
            var type = $('#field_type_edit').val();
            if (type == 'number') {
                $('.min-value-col').removeClass('d-none');
            } else {
                $('.min-value-col').addClass('d-none');
            }
        }

        $('#field_type_edit').on('change', function() {
            var type = $(this).val();
            if (type == 'select' || type == 'multi_select') {
                $('#options_area_edit').removeClass('d-none');
            } else {
                $('#options_area_edit').addClass('d-none');
            }
            toggleMinValCol();
        });

        // Initial run
        toggleMinValCol();

        // Click handler for segmented control buttons inside stage settings panel
        $(document).off('click', '#stage-panel .btn-segment').on('click', '#stage-panel .btn-segment', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var val = $btn.attr('data-value') || $btn.data('value');
            var stageId = $btn.parent().attr('data-target-stage-id') || $btn.parent().data('target-stage-id');
            
            // Find and update hidden select
            var $select = $('#stage-panel').find(`.field-stage-select[data-stage-id="${stageId}"]`);
            if ($select.length) {
                $select.val(val).trigger('change');
            }
            
            // Update parent active attribute
            $btn.parent().attr('data-active-val', val);
        });

        // Init Choices.js
        var elements = document.querySelectorAll('.choices-js-modal');
        elements.forEach(function(element) {
            new Choices(element, {
                removeItemButton: true,
                placeholderValue: 'Select Options',
                searchEnabled: true,
                shouldSort: false,
            });
        });
    })();
</script>
