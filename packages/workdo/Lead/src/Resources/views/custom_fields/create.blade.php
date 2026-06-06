{{ Form::open(array('url' => 'lead-custom-fields','enctype'=>'multipart/form-data')) }}
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
                    {{ Form::select('type', $types, null, array('class' => 'form-control select2', 'required'=>'required', 'id' => 'field_type')) }}
                </div>
                <div class="col-md-6 form-group mb-3">
                     {{ Form::label('icon', __('Field Icon'),['class'=>'form-label fw-bold']) }}
                     {{ Form::text('icon', null, array('class' => 'form-control', 'placeholder' => __('e.g. user, check, star'))) }}
                     <small class="text-muted">{{ __('Feather Icon name. ') }} <a href="https://feathericons.com/" target="_blank" class="text-primary">{{ __('View Icons') }}</a></small>
                </div>
                <div class="col-12 form-group d-none mb-3" id="options_area">
                    {{ Form::label('options', __('Options (Comma Separated)'),['class'=>'form-label fw-bold']) }}
                    {{ Form::textarea('options', null, array('class' => 'form-control', 'rows' => 2, 'placeholder' => __('Option 1, Option 2, Option 3'))) }}
                </div>

                <div class="col-12 mt-2">
                    <div class="card bg-light border-0 shadow-none mb-3">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-3"><i class="ti ti-adjustments me-1 text-primary"></i> {{ __('Behavior Rules') }}</h6>
                            <div class="form-check form-switch mb-3">
                                <input type="checkbox" class="form-check-input" name="is_required" id="is_required">
                                <label class="form-check-label fw-bold" for="is_required">{{ __('Required in ALL stages') }}</label>
                                <small class="text-muted d-block ms-0 mt-1">{{ __('Forces this field to be filled across all stages (overrides specific stage configs).') }}</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" class="form-check-input" name="is_filterable" id="is_filterable">
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
            <div class="form-group mb-0">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <label class="form-label fw-bold mb-0">{{ __('Stage-wise Visibility') }}</label>
                        <small class="text-muted d-block mt-1">{{ __('Configure how this custom field is shown or required per stage.') }}</small>
                    </div>
                </div>
                
                <div class="table-responsive rounded-3 border mb-3" style="max-height: 280px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="py-2 px-3 border-0">{{ __('Pipeline / Stage') }}</th>
                                <th class="py-2 px-3 border-0 text-end" style="width: 150px;">{{ __('Visibility') }}</th>
                                <th class="py-2 px-3 border-0 text-end min-value-col d-none" style="width: 150px;">{{ __('Min Value') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pipelines as $pipeline)
                                <tr class="bg-light">
                                    <td colspan="3" class="py-2 px-3 fw-bold text-muted border-bottom">
                                        <i class="ti ti-git-fork text-primary me-1"></i> {{ $pipeline->name }} {{ __('Pipeline') }}
                                    </td>
                                </tr>
                                @foreach($pipeline->leadStages as $stage)
                                    <tr>
                                        <td class="py-2 px-3 ps-4 border-bottom-0">
                                            <span class="fw-semibold text-dark">{{ $stage->name }}</span>
                                        </td>
                                        <td class="py-2 px-3 text-end border-bottom-0">
                                            <select name="stage_config[{{ $stage->id }}]" class="form-select form-select-sm d-inline-block w-auto py-1">
                                                <option value="hidden">{{ __('🚫 Hidden') }}</option>
                                                <option value="visible" selected>{{ __('👁️ Visible (Optional)') }}</option>
                                                <option value="required">{{ __('✅ Required') }}</option>
                                            </select>
                                        </td>
                                        <td class="py-2 px-3 text-end border-bottom-0 min-value-col d-none">
                                            <input type="number" step="any" name="stage_min_values[{{ $stage->id }}]" class="form-control form-control-sm d-inline-block w-auto py-1" style="max-width: 120px;" placeholder="{{ __('Min value') }}">
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 3: API & Access -->
        <div class="tab-pane fade" id="api-panel" role="tabpanel" aria-labelledby="api-tab">
            <div class="row">
                <!-- Role Permissions -->
                <div class="col-12 form-group mb-3">
                     {{ Form::label('visible_roles', __('Visible to Roles (Optional)'),['class'=>'form-label fw-bold']) }}
                     {{ Form::select('visible_roles[]', $roles, null, array('class' => 'form-control choices-js-modal', 'multiple'=>'multiple', 'id' => 'create_visible_roles')) }}
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
                                                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
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
    <button type="submit" class="btn btn-primary">{{__('Create')}}</button>
</div>
{{ Form::close() }}

<script>
    (function() {
        function toggleMinValCol() {
            var type = $('#field_type').val();
            if (type == 'number') {
                $('.min-value-col').removeClass('d-none');
            } else {
                $('.min-value-col').addClass('d-none');
            }
        }

        $('#field_type').on('change', function() {
            var type = $(this).val();
            if (type == 'select' || type == 'multi_select') {
                $('#options_area').removeClass('d-none');
            } else {
                $('#options_area').addClass('d-none');
            }
            toggleMinValCol();
        });

        // Initial run
        toggleMinValCol();

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
