

<style>
    .section-title {
        border-bottom: 2px solid #edeff1;
        padding-bottom: 8px;
        margin-bottom: 20px;
        margin-top: 10px;
        color: #2f2f2f;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-title i {
        background: rgba(102, 119, 239, 0.1);
        color: #6677ef;
        padding: 6px;
        border-radius: 6px;
        font-size: 1.1rem;
    }
    .form-group label {
        font-weight: 600;
        color: #555;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .form-control:focus {
        border-color: #6677ef;
        box-shadow: 0 0 0 3px rgba(102, 119, 239, 0.15);
    }
    .modal-body {
        max-height: 80vh;
        overflow-y: auto;
        padding: 25px;
    }
    .custom-field-section {
        background: #f8f9fd;
        border-radius: 12px;
        padding: 20px;
        border: 1px solid #eef1f6;
        margin-top: 15px;
    }
    
    /* Fix for "An invalid form control is not focusable" error with Choices.js */
    select.choices {
        display: block !important;
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0,0,0,0) !important;
        border: 0 !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
</style>

{{ Form::model($lead, array('route' => array('leads.update', $lead->id), 'method' => 'PUT','enctype'=>'multipart/form-data', 'id' => 'edit_lead_form', 'novalidate')) }}
    <div class="modal-body">
        <div class="text-end mb-3">
            @if (module_is_active('AIAssistant'))
                @include('aiassistant::ai.generate_ai_btn',['template_module' => 'lead_email','module'=>'Lead'])
            @endif
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-1" role="tabpanel">
                <div class="row">
                    {{ Form::hidden('subject', null) }}
                    
                    <!-- Section: Personal & Basic Info -->
                    <div class="col-12">
                        <h6 class="section-title"><i class="ti ti-user"></i> {{__('Basic Information')}}</h6>
                    </div>
                    
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('user_id', '<i class="ti ti-crown"></i> ' . __('Responsible Person'), ['class'=>'form-label'], false) }}
                        {{ Form::select('user_id', $users,$lead->user_id, array('class' => 'form-control choices-lead-edit', 'disabled' => !$isResponsiblePersonEditable)) }}
                    </div>
                    
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('name', '<i class="ti ti-signature"></i> ' . __('Name'), ['class'=>'form-label'], false) }}<x-required></x-required>
                        {{ Form::text('name', null, array('class' => 'form-control','required'=>'required','placeholder' => __('Enter Name'))) }}
                    </div>
                    
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('email', '<i class="ti ti-mail"></i> ' . __('Email'), ['class'=>'form-label'], false) }}
                        {{ Form::email('email', null, array('class' => 'form-control','placeholder' => __('Enter Email'))) }}
                    </div>
                    
                    <x-mobile name="phone" label="<i class='ti ti-phone'></i> {{__('Phone No')}}" divClass="col-md-6" placeholder="{{__('Enter Phone No')}}"></x-mobile>

                    <!-- Section: Pipeline & Stage -->
                    <div class="col-12 mt-4">
                        <h6 class="section-title"><i class="ti ti-git-branch"></i> {{__('Pipeline & Status')}}</h6>
                    </div>
                    
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('pipeline_id', '<i class="ti ti-columns"></i> ' . __('Pipeline'), ['class'=>'form-label'], false) }}<x-required></x-required>
                        {{ Form::select('pipeline_id', $pipelines,$lead->pipeline_id, array('class' => 'form-control choices-lead-edit', 'id' => 'pipeline_id')) }}
                    </div>
                    
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('stage_id', '<i class="ti ti-list-check"></i> ' . __('Stage'), ['class'=>'form-label'], false) }}<x-required></x-required>
                        {{ Form::select('stage_id', $stages,$lead->stage_id, array('class' => 'form-control choices-lead-edit', 'id' => 'stage_id')) }}
                    </div>
                    
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('date', '<i class="ti ti-calendar-event"></i> ' . __('Created'), ['class'=>'form-label'], false) }}
                        {{ Form::date('date', null, array('class' => 'form-control')) }}
                    </div>

                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('sources', '<i class="ti ti-world"></i> ' . __('Sources'), ['class'=>'form-label'], false) }}
                        {{ Form::select('sources[]', $sources,null, array('class' => 'form-control choices','id'=>'choices-multiple','multiple'=>true)) }}
                    </div>

                    <!-- Section: Additional Details (Lead Layout Builder) -->
                    @if(isset($leadSections) && count($leadSections) > 0)
                        @foreach($leadSections as $section)
                            @php
                                $hasVisibleFields = false;
                                foreach($section->fields as $field) {
                                    $currentStageId = (string)$lead->stage_id;
                                    if (empty($field->visible_stages) || (is_array($field->visible_stages) && in_array($currentStageId, $field->visible_stages))) {
                                        $hasVisibleFields = true;
                                        break;
                                    }
                                }
                            @endphp

                            @if($hasVisibleFields)
                                <div class="col-12 mt-4">
                                    <h6 class="section-title"><i class="ti ti-settings"></i> {{ __($section->name) }}</h6>
                                </div>
                                <div class="col-12">
                                    <div class="custom-field-section row">
                                        @foreach($section->fields as $field)
                                            @php
                                                $value = $leadCustomFieldValues[$field->id] ?? null;
                                                $currentStageId = (string)$lead->stage_id;
                                                
                                                $isVisible = true;
                                                if (!empty($field->visible_stages) && is_array($field->visible_stages)) {
                                                    $isVisible = in_array($currentStageId, $field->visible_stages);
                                                }
                                                
                                                $isRequired = false;
                                                if ($field->is_required == 1) {
                                                    $isRequired = true;
                                                } elseif (!empty($field->required_stages) && is_array($field->required_stages)) {
                                                    $isRequired = in_array($currentStageId, $field->required_stages);
                                                }

                                                $colWidth = 12 / ($section->columns > 0 ? $section->columns : 3);
                                            @endphp

                                            @if($isVisible)
                                                <div class="col-sm-{{ $colWidth }} col-12 form-group">
                                                    {{ Form::label('leadCustomField['.$field->id.']', (!empty($field->icon) ? '<i class="ti ti-'.$field->icon.'"></i> ' : '<i class="ti ti-circle-dot"></i> ') . $field->name, ['class'=>'form-label'], false) }} 
                                                    @if($isRequired)
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                    
                                                    @if($field->type == 'text')
                                                        {{ Form::text('leadCustomField['.$field->id.']', $value, ['class' => 'form-control', 'required' => $isRequired]) }}
                                                    @elseif($field->type == 'email')
                                                        {{ Form::email('leadCustomField['.$field->id.']', $value, ['class' => 'form-control', 'required' => $isRequired]) }}
                                                    @elseif($field->type == 'number')
                                                         {{ Form::number('leadCustomField['.$field->id.']', $value, ['class' => 'form-control', 'required' => $isRequired]) }}
                                                    @elseif($field->type == 'date')
                                                        {{ Form::date('leadCustomField['.$field->id.']', $value, ['class' => 'form-control', 'required' => $isRequired]) }}
                                                    @elseif($field->type == 'textarea')
                                                        {{ Form::textarea('leadCustomField['.$field->id.']', $value, ['class' => 'form-control', 'rows' => 3, 'required' => $isRequired]) }}
                                                    @elseif($field->type == 'select')
                                                        @php 
                                                            $options = array_map('trim', explode(',', $field->options)); 
                                                            $selectOptions = $isRequired ? array_combine($options, $options) : ['' => __('Please Select')] + array_combine($options, $options);
                                                        @endphp
                                                         {{ Form::select('leadCustomField['.$field->id.']', $selectOptions, $value, ['class' => 'form-control choices', 'required' => $isRequired]) }}
                                                    @elseif($field->type == 'multi_select')
                                                        @php 
                                                            $options = array_map('trim', explode(',', $field->options)); 
                                                            $selectOptions = array_combine($options, $options); 
                                                            $selectedValues = !empty($value) ? array_map('trim', explode(',', $value)) : [];
                                                        @endphp
                                                         {{ Form::select('leadCustomField['.$field->id.'][]', $selectOptions, $selectedValues, ['class' => 'form-control choices', 'required' => $isRequired, 'multiple'=>'multiple']) }}
                                                    @elseif($field->type == 'file')
                                                        {{ Form::file('leadCustomField['.$field->id.']', ['class' => 'form-control', 'required' => ($isRequired && empty($value))]) }}
                                                        @if(!empty($value))
                                                            <p class="text-xs text-muted mt-1">{{ __('Current File:') }} <a href="{{ asset('storage/uploads/custom_fields/'.$value) }}" target="_blank" class="text-primary font-weight-bold">{{ $value }}</a></p>
                                                        @endif
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>


    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        <button type="submit" class="btn  btn-primary">{{__('Update')}}</button>
    </div>
{{ Form::close() }}

<script>
    var stage_id = '{{$lead->stage_id}}';

    $(document).ready(function () {
        var pipeline_id = $('[name=pipeline_id]').val();
        
        // Ensure values are set correctly on underlying selects
        processInitialValues();
        
        getStages(pipeline_id);

        // Init Choices.js with a small delay
        setTimeout(function() {
            initChoices('.choices-lead-edit');
            // Second pass for sync
            syncChoicesValues();
        }, 500);
    });

    function processInitialValues() {
        var currentUserId = '{{$lead->user_id}}';
        var currentPipelineId = '{{$lead->pipeline_id}}';
        var currentStageId = '{{$lead->stage_id}}';
        
        if(currentUserId) {
            $('select[name="user_id"]').val(currentUserId);
        }
        if(currentPipelineId) {
            $('select[name="pipeline_id"]').val(currentPipelineId);
        }
        if(currentStageId) {
            $('select[name="stage_id"]').val(currentStageId);
        }
    }

    function syncChoicesValues() {
        $('.choices-lead-edit, #stage_id, #pipeline_id').each(function() {
            var val = $(this).val();
            if (this.choicesInstance && val) {
                this.choicesInstance.setChoiceByValue(val.toString());
            }
        });
    }

    function initChoices(selector) {
        if (typeof Choices !== 'undefined') {
            var selectElements = document.querySelectorAll(selector || 'select.choices, input.choices');
            
            selectElements.forEach(function(element) {
                if (element.choicesInstance) {
                    element.choicesInstance.destroy();
                }
                
                try {
                    element.choicesInstance = new Choices(element, {
                        removeItemButton: true,
                        placeholderValue: '{{__("Select Options")}}',
                        searchEnabled: true,
                        shouldSort: false,
                        placeholder: true
                    });
                    
                    // Immediately sync value
                    var val = element.value;
                    if(val) {
                        element.choicesInstance.setChoiceByValue(val.toString());
                    }
                } catch (e) {
                    console.error("Choices init error", e);
                }
            });
        }
    }

    $(document).on("change", "#pipeline_id", function () {
        var currVal = $(this).val();
        getStages(currVal);
    });

    function getStages(id) {
        if (!id) return;
        
        var currentPipelineId = '{{$lead->pipeline_id}}';
        var initialStageId = '{{$lead->stage_id}}';
        
        var stageElement = document.querySelector('#stage_id');
        if (stageElement && stageElement.choicesInstance) {
            stageElement.choicesInstance.destroy();
            stageElement.choicesInstance = null;
        }
        
        $.ajax({
            url: '{{route('leads.json')}}',
            data: {pipeline_id: id, _token: $('meta[name="csrf-token"]').attr('content')},
            type: 'POST',
            success: function (data) {
                var stage_cnt = Object.keys(data).length;
                $("#stage_id").html('<option value="">{{ __("Select Stage") }}</option>');
                
                if (stage_cnt > 0) {
                    $.each(data, function (key, data) {
                        // Use initialStageId if we are still on the original pipeline
                        var isSelected = (id == currentPipelineId && key == initialStageId);
                        var select = isSelected ? 'selected' : '';
                        $("#stage_id").append('<option value="' + key + '" ' + select + '>' + data + '</option>');
                    });
                    
                    if(id == currentPipelineId) {
                        $("#stage_id").val(initialStageId);
                    }
                }
                
                setTimeout(function() {
                    initChoices('#stage_id');
                    // Force choice sync
                    var stageSelect = document.querySelector('#stage_id');
                    if(stageSelect && stageSelect.choicesInstance) {
                        var val = $(stageSelect).val();
                        if(val) stageSelect.choicesInstance.setChoiceByValue(val.toString());
                    }
                }, 150);
            }
        });
    }
</script>
<script>
    if ($(".summernote").length > 0) {
        $('.summernote').summernote({
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough']],
                ['list', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'unlink']],
            ],
            height: 200,
        });
    }
</script>
