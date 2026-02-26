
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
                            @if($field->is_required)
                                <div class="col-sm-6 col-12 form-group">
                                    {{ Form::label('leadCustomField['.$field->id.']', $field->name, ['class'=>'form-label']) }} 
                                    <span class="text-danger">*</span>
                                    
                                    @if($field->type == 'text')
                                        {{ Form::text('leadCustomField['.$field->id.']', null, ['class' => 'form-control', 'required' => true]) }}
                                    @elseif($field->type == 'number')
                                         {{ Form::number('leadCustomField['.$field->id.']', null, ['class' => 'form-control', 'required' => true]) }}
                                    @elseif($field->type == 'date')
                                        {{ Form::date('leadCustomField['.$field->id.']', null, ['class' => 'form-control', 'required' => true]) }}
                                    @elseif($field->type == 'textarea')
                                        {{ Form::textarea('leadCustomField['.$field->id.']', null, ['class' => 'form-control', 'rows' => 3, 'required' => true]) }}
                                    @elseif($field->type == 'select')
                                        @php $options = array_map('trim', explode(',', $field->options)); $selectOptions = array_combine($options, $options); @endphp
                                         {{ Form::select('leadCustomField['.$field->id.']', $selectOptions, null, ['class' => 'form-control choices', 'required' => true]) }}
                                    @elseif($field->type == 'multi_select')
                                        @php $options = array_map('trim', explode(',', $field->options)); $selectOptions = array_combine($options, $options); @endphp
                                         {{ Form::select('leadCustomField['.$field->id.'][]', $selectOptions, null, ['class' => 'form-control choices', 'required' => true, 'multiple'=>'multiple']) }}
                                    @elseif($field->type == 'file')
                                        {{ Form::file('leadCustomField['.$field->id.']', ['class' => 'form-control', 'required' => true]) }}
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
            @if(module_is_active('CustomField') && !$customFields->isEmpty())
            <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                <div class="col-md-6">
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
    }, 500);

    // Load stages when pipeline changes
    $(document).on('change', '#pipeline_id', function() {
        var pipelineId = $(this).val();
        if(pipelineId) {
            $.ajax({
                url: '{{ route("lead.import.stages") }}',
                type: 'GET',
                data: { pipeline_id: pipelineId },
                success: function(data) {
                    $('#stage_id').empty();
                    $('#stage_id').append('<option value="">{{ __("Select Stage") }}</option>');
                    $.each(data, function(key, value) {
                        $('#stage_id').append('<option value="'+ key +'">'+ value +'</option>');
                    });
                    // Re-init Choices
                    initChoices();
                }
            });
        } else {
            $('#stage_id').empty();
            $('#stage_id').append('<option value="">{{ __("Select Stage") }}</option>');
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
