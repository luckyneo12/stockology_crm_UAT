
{{ Form::open(array('url' => 'leads','enctype'=>'multipart/form-data','class'=>'needs-validation','novalidate')) }}
    <div class="modal-body">
        <div class="text-end mb-3">
            <!-- @if (module_is_active('AIAssistant'))
                @include('aiassistant::ai.generate_ai_btn',['template_module' => 'lead','module'=>'Lead'])
            @endif -->
        </div>
        @if(module_is_active('CustomField') && !$customFields->isEmpty())
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" href="#tab-1" role="tab" aria-controls="pills-home" aria-selected="true">{{__('Lead Detail')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" href="#tab-2" role="tab" aria-controls="pills-profile" aria-selected="false">{{__('Custom Fields')}}</a>
                </li>
            </ul>
        @endif
        <div class="tab-content tab-bordered">
            <div class="tab-pane fade show active" id="tab-1" role="tabpanel">
                <div class="row">
                    {{ Form::hidden('subject', 'New Lead') }}
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('user_id', __('Responsible Person'),['class'=>'form-label']) }}
                        {{ Form::select('user_id', $users,null, array('class' => 'form-control choices', 'disabled' => !$isResponsiblePersonEditable)) }}
                        @if(!$isResponsiblePersonEditable)
                            {{ Form::hidden('user_id', Auth::user()->id) }}
                        @endif
                        @if(count($users) == 1)
                            <div class="text-muted text-xs">
                                {{__('Please create new users')}} <a href="{{route('users')}}">{{__('here')}}</a>.
                            </div>
                        @endif
                    </div>
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('name', __('Name'),['class'=>'form-label']) }}
                        {{ Form::text('name', null, array('class' => 'form-control','placeholder' => __('Enter Name'))) }}
                    </div>
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('email', __('Email'),['class'=>'form-label']) }}
                        {{ Form::text('email', null, array('class' => 'form-control','placeholder' => __('Enter Email'))) }}
                    </div>
                    <x-mobile name="phone" label="{{__('Phone No')}}" divClass="col-md-6" placeholder="{{__('Enter Phone No')}}" required="required"></x-mobile>
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('pipeline_id', __('Pipeline'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                        {{ Form::select('pipeline_id', $pipelines, $pipeline->id ?? null, array('class' => 'form-control choices', 'required' => 'required', 'id' => 'pipeline_id')) }}
                    </div>
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('stage_id', __('Stage'),['class'=>'form-label']) }}<span class="text-danger">*</span>
                        {{ Form::select('stage_id', $stages, $stage->id ?? null, array('class' => 'form-control choices', 'required' => 'required', 'id' => 'stage_id')) }}
                    </div>
                    <div class="col-sm-6 col-12 form-group">
                        {{ Form::label('date', __('Created'),['class'=>'form-label']) }}
                        {{ Form::date('date', date('Y-m-d'), array('class' => 'form-control')) }}
                    </div>
                
                    @if(isset($leadCustomFields))
                        @foreach($leadCustomFields as $field)
                            <div class="col-sm-6 col-12 form-group lead-custom-field-group" data-id="{{ $field->id }}" style="display:none;">
                                {{ Form::label('leadCustomField['.$field->id.']', $field->name, ['class'=>'form-label']) }} 
                                <span class="text-danger required-indicator" style="{{ $field->is_required ? '' : 'display:none' }}">*</span>
                                
                                @if($field->type == 'text')
                                    {{ Form::text('leadCustomField['.$field->id.']', null, ['class' => 'form-control', 'required' => $field->is_required]) }}
                                @elseif($field->type == 'number')
                                     {{ Form::number('leadCustomField['.$field->id.']', null, ['class' => 'form-control', 'required' => $field->is_required]) }}
                                @elseif($field->type == 'date')
                                    {{ Form::date('leadCustomField['.$field->id.']', null, ['class' => 'form-control', 'required' => $field->is_required]) }}
                                @elseif($field->type == 'textarea')
                                    {{ Form::textarea('leadCustomField['.$field->id.']', null, ['class' => 'form-control', 'rows' => 3, 'required' => $field->is_required]) }}
                                @elseif($field->type == 'select')
                                    @php $options = array_map('trim', explode(',', $field->options)); $selectOptions = array_combine($options, $options); @endphp
                                     {{ Form::select('leadCustomField['.$field->id.']', $selectOptions, null, ['class' => 'form-control choices', 'required' => $field->is_required]) }}
                                @elseif($field->type == 'multi_select')
                                    @php $options = array_map('trim', explode(',', $field->options)); $selectOptions = array_combine($options, $options); @endphp
                                     {{ Form::select('leadCustomField['.$field->id.'][]', $selectOptions, null, ['class' => 'form-control choices', 'required' => $field->is_required, 'multiple'=>'multiple']) }}
                                @elseif($field->type == 'file')
                                    {{ Form::file('leadCustomField['.$field->id.']', ['class' => 'form-control', 'required' => $field->is_required]) }}
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            @if(module_is_active('CustomField') && !$customFields->isEmpty())
            <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                <div class="col-md-12">
                    @include('custom-field::formBuilder')
                </div>
            </div>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
        <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
    </div>

{{ Form::close() }}

<script>
$(document).ready(function() {
    // Init Choices.js for all choices elements with delay
    setTimeout(function() {
        initChoices();
        if ($('#stage_id').val()) {
            $('#stage_id').trigger('change');
        } else {
             // Hide all standard custom fields initially if no stage is selected
             $('input[name^="customField"], select[name^="customField"], textarea[name^="customField"]').each(function() {
                 $(this).closest('.form-group').hide();
             });
        }
    }, 500);

    // Dynamic Duplicate Check
    const duplicateFields = {!! json_encode($duplicateFields ?? []) !!};
    
    let duplicateCheckTimer;
    $(document).on('keyup change', 'input[name="name"], input[name="email"], input[name="phone"], input[name^="leadCustomField"]', function() {
        clearTimeout(duplicateCheckTimer);
        const $input = $(this);
        const name = $input.attr('name');
        const value = $input.val();
        
        duplicateCheckTimer = setTimeout(function() {
            let checkField = name;
            if (name.startsWith('leadCustomField[')) {
                const match = name.match(/\[(\d+)\]/);
                if (match) {
                    checkField = 'custom_' + match[1];
                }
            }

            if (duplicateFields.includes(checkField) && value) {
                $.ajax({
                    url: '{{ route("leads.check.duplicate") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        field: checkField,
                        value: value
                    },
                    success: function(response) {
                        $input.parent().find('.duplicate-warning').remove();
                        if (response.exists) {
                            $input.addClass('border-danger text-danger');
                            $input.after('<div class="duplicate-warning text-danger mt-1 text-xs" style="font-weight: 600;"><i class="ti ti-alert-triangle me-1"></i> {{ __("Duplicate Alert: This data already exists!") }}</div>');
                        } else {
                            $input.removeClass('border-danger text-danger');
                        }
                    }
                });
            } else {
                $input.parent().find('.duplicate-warning').remove();
                $input.removeClass('border-danger text-danger');
            }
        }, 800);
    });

    // Load stages when pipeline changes
    $(document).on('change', '#pipeline_id', function() {
        var pipelineId = $(this).val();
        if(pipelineId) {
            $.ajax({
                url: '{{ route("leads.get.stages") }}',
                type: 'GET',
                data: { pipeline_id: pipelineId },
                success: function(data) {
                    $('#stage_id').empty();
                    $('#stage_id').append('<option value="">{{ __("Select Stage") }}</option>');
                    // Data from LeadController@getStagesByPipeline has a 'stages' property if using newer version
                    let stages = data.stages ? data.stages : data;
                    
                    $.each(stages, function(key, stage) {
                        // Handle both [id => name] and [{id, name}] formats
                        let id = stage.id ? stage.id : key;
                        let name = stage.name ? stage.name : stage;
                        $('#stage_id').append('<option value="'+ id +'">'+ name +'</option>');
                    });

                    // Re-init Choices
                    initChoices();
                }
            });
        }
    });

    // Stage-Based Requirements and Visibility
    $(document).on('change', '#stage_id', function() {
        var stageId = $(this).val();
        if(stageId) {
            $.ajax({
                url: '{{ route("leads.get.stage.requirements") }}',
                type: 'GET',
                data: { stage_id: stageId },
                success: function(response) {
                    // Handle Dedicated Lead Custom Fields
                    $('.lead-custom-field-group').each(function() {
                        const fieldId = $(this).data('id').toString();
                        const $group = $(this);
                        const $input = $group.find('input, select, textarea');
                        const $indicator = $group.find('.required-indicator');

                        // Visibility
                        if (response.hidden_lead.includes(fieldId)) {
                            $group.hide();
                            $input.prop('required', false);
                        } else {
                            $group.show();
                            // Requirement
                            if (response.required_lead.includes(fieldId)) {
                                $input.prop('required', true);
                                $indicator.show();
                            } else {
                                $input.prop('required', false);
                                $indicator.hide();
                            }
                        }
                    });

                    // Handle standard CustomFields (CustomField module)
                    // We'll hide/show them based on visible_custom and required_custom
                    // This typically requires matching IDs in the custom field form builder
                    if (response.hidden_custom !== undefined) {
                         // Note: This part depends on how custom-field::formBuilder generates IDs
                         // We'll try to find inputs with name customField[id]
                         $('input[name^="customField"], select[name^="customField"], textarea[name^="customField"]').each(function() {
                             const name = $(this).attr('name');
                             const match = name.match(/\[(\d+)\]/);
                             if (match) {
                                 const cfId = parseInt(match[1]);
                                 const $parent = $(this).closest('.form-group');
                                 
                                 if (response.hidden_custom.includes(cfId)) {
                                     $parent.hide();
                                     $(this).prop('required', false);
                                 } else {
                                     $parent.show();
                                     if (response.required_custom.includes(cfId)) {
                                         $(this).prop('required', true);
                                         if ($parent.find('.text-danger').length == 0) {
                                             $parent.find('label').after(' <span class="text-danger">*</span>');
                                         }
                                     } else {
                                         $(this).prop('required', false);
                                         $parent.find('.text-danger').remove();
                                     }
                                 }
                             }
                         });
                    }
                }
            });
        }
    });
});

function initChoices() {
    if (typeof Choices !== 'undefined') {
        var selectElements = document.querySelectorAll('select.choices, input.choices');
        selectElements.forEach(function(element) {
            if (element.choicesInstance) {
                element.choicesInstance.destroy();
            }
            try {
                element.choicesInstance = new Choices(element, {
                    removeItemButton: true,
                    placeholderValue: '{{__("Select Options")}}',
                    searchEnabled: true,
                    shouldSort: false
                });
            } catch (e) {
                console.error("Choices init error", e);
            }
        });
    }
}
</script>
