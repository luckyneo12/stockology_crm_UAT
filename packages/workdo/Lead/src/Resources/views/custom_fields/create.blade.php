{{ Form::open(array('url' => 'lead-custom-fields','enctype'=>'multipart/form-data')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12 form-group">
            {{ Form::label('name', __('Name'),['class'=>'form-label']) }}
            {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="col-12 form-group mb-3">
            {{ Form::label('type', __('Type'),['class'=>'form-label']) }}
            {{ Form::select('type', $types, null, array('class' => 'form-control select2', 'required'=>'required', 'id' => 'field_type')) }}
        </div>
        <div class="col-12 form-group mb-3">
             {{ Form::label('icon', __('Field Icon'),['class'=>'form-label']) }}
             {{ Form::text('icon', null, array('class' => 'form-control', 'placeholder' => __('e.g. user, check, star'))) }}
             <small class="text-muted">{{ __('Enter Feather Icon name. ') }} <a href="https://feathericons.com/" target="_blank">View Icons</a></small>
        </div>
        <div class="col-12 form-group d-none mb-3" id="options_area">
            {{ Form::label('options', __('Options (Comma Separated)'),['class'=>'form-label']) }}
            {{ Form::textarea('options', null, array('class' => 'form-control', 'rows' => 3)) }}
            <small class="text-muted">{{ __('Example: Option 1,Option 2,Option 3') }}</small>
        </div>
        <!-- Stage Configuration -->
        <div class="col-12 form-group mb-3">
            <label class="form-label">{{ __('Stage Configuration') }}</label>
            <small class="text-muted d-block mb-2">
                {{ __('Configure how this field behaves in each stage') }}
            </small>
            
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Stage') }}</th>
                            <th>{{ __('Visibility & Requirement') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pipelines as $pipeline)
                            <tr>
                                <th colspan="2" class="table-secondary text-center">
                                    {{ $pipeline->name }} {{ __('Pipeline') }}
                                </th>
                            </tr>
                            @foreach($pipeline->leadStages as $stage)
                                <tr>
                                    <td><strong>{{ $stage->name }}</strong></td>
                                    <td>
                                        <select name="stage_config[{{ $stage->id }}]" class="form-select form-select-sm">
                                            <option value="hidden">{{ __('🚫 Hidden') }}</option>
                                            <option value="visible" selected>{{ __('👁️ Visible (Optional)') }}</option>
                                            <option value="required">{{ __('✅ Required') }}</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <small class="text-muted">
                <strong>{{ __('Hidden:') }}</strong> {{ __('Field will not appear') }}<br>
                <strong>{{ __('Visible (Optional):') }}</strong> {{ __('Field appears but can be left blank') }}<br>
                <strong>{{ __('Required:') }}</strong> {{ __('Field appears and must be filled') }}
            </small>
        </div>

        <!-- Visible to Roles -->
        <div class="col-12 form-group mb-3">
             {{ Form::label('visible_roles', __('Visible to Roles (Optional)'),['class'=>'form-label']) }}
             {{ Form::select('visible_roles[]', $roles, null, array('class' => 'form-control choices-js-modal', 'multiple'=>'multiple', 'id' => 'create_visible_roles')) }}
             <small class="text-muted">{{ __('Leave empty to show to all roles.') }}</small>
        </div>

        <!-- Is Filterable -->
        <div class="col-12 form-group mb-3">
             <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" name="is_filterable" id="is_filterable">
                <label class="form-check-label" for="is_filterable">{{ __('Is Filterable (Show in Sidebar)') }}</label>
            </div>
        </div>

        <!-- Global Required Override -->
        <div class="col-12 form-group mb-3">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" name="is_required" id="is_required">
                <label class="form-check-label" for="is_required">{{ __('Required in ALL stages (overrides stage config)') }}</label>
            </div>
            <small class="text-muted">{{ __('Check this to make the field required in all stages, regardless of stage configuration above.') }}</small>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
    <button type="submit" class="btn  btn-primary">{{__('Create')}}</button>
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        $('#field_type').on('change', function() {
            var type = $(this).val();
            if (type == 'select' || type == 'multi_select') {
                $('#options_area').removeClass('d-none');
            } else {
                $('#options_area').addClass('d-none');
            }
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
    });
</script>
