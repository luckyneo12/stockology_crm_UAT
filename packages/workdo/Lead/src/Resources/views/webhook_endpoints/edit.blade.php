{{ Form::model($webhookEndpoint, array('route' => array('webhook-endpoints.update', $webhookEndpoint->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-12">
            {{ Form::label('name', __('Endpoint Name'),['class'=>'form-label']) }}
            {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-12">
            {{ Form::label('assign_to', __('Default Assignee'),['class'=>'form-label']) }}
            {{ Form::select('assign_to', $users, null, array('class' => 'form-control', 'placeholder' => __('Select Default Assignee'))) }}
        </div>
        <div class="form-group col-12">
            {{ Form::label('auto_convert', __('Processing Mode'),['class'=>'form-label']) }}
            {{ Form::select('auto_convert', [1 => __('Direct Lead (Auto Convert)'), 0 => __('Webhook Logs Only (Manual Review)')], null, array('class' => 'form-control', 'id' => 'auto_convert')) }}
        </div>
        <div class="form-group col-6">
            {{ Form::label('pipeline_id', __('Default Pipeline'),['class'=>'form-label']) }}
            {{ Form::select('pipeline_id', $pipelines, null, array('class' => 'form-control', 'id' => 'pipeline_id', 'required' => 'required', 'placeholder' => __('Select Pipeline'))) }}
        </div>
        <div class="form-group col-6">
            {{ Form::label('stage_id', __('Default Stage'),['class'=>'form-label']) }}
            {{ Form::select('stage_id', $stages, null, array('class' => 'form-control', 'id' => 'stage_id', 'required' => 'required', 'placeholder' => __('Select Stage'))) }}
        </div>
        <div class="form-group col-12">
            {{ Form::label('source_id', __('Default Source'),['class'=>'form-label']) }}
            {{ Form::select('source_id', $sources, null, array('class' => 'form-control', 'id' => 'source_id', 'placeholder' => __('Select Source'))) }}
        </div>

        <div class="form-group col-12">
            {{ Form::label('edit_permissions', __('Edit Permissions (Users who can edit this endpoint)'),['class'=>'form-label']) }}
            {!! Form::select('edit_permissions[]', $users, $webhookEndpoint->edit_permissions, array('class' => 'form-control choices', 'multiple' => 'multiple', 'id' => 'edit_permissions')) !!}
        </div>

        <div class="form-group col-12">
            {{ Form::label('view_permissions', __('View Permissions (Users who can see & transfer incoming data)'),['class'=>'form-label']) }}
            {!! Form::select('view_permissions[]', $users, $webhookEndpoint->view_permissions, array('class' => 'form-control choices', 'multiple' => 'multiple', 'id' => 'view_permissions')) !!}
        </div>

        <hr>
        <div class="col-12">
            <h6>{{__('Field Mapping')}}</h6>
            <p class="text-xs text-muted">{{__('Define which JSON keys from the incoming payload should map to Lead fields.')}}</p>
            @php
                $mapping = $webhookEndpoint->field_mapping ?? [];
            @endphp
            <div class="table-responsive">
                <table class="table table-bordered" id="mapping_table">
                    <thead>
                        <tr>
                            <th>{{__('Lead Field')}}</th>
                            <th>{{__('JSON Key')}}</th>
                            <th class="text-center">{{__('Test Form?')}}</th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{__('Name')}} <span class="text-danger">*</span></td>
                            <td>{{ Form::text('field_mapping[name]', $mapping['name'] ?? 'name', array('class' => 'form-control', 'required' => 'required', 'placeholder' => __('e.g. customer_name'))) }}</td>
                            <td class="text-center">{{ Form::checkbox('field_mapping[in_form][name]', 1, isset($mapping['in_form']['name'])) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>{{__('Email')}}</td>
                            <td>{{ Form::text('field_mapping[email]', $mapping['email'] ?? 'email', array('class' => 'form-control', 'placeholder' => __('e.g. customer_email'))) }}</td>
                            <td class="text-center">{{ Form::checkbox('field_mapping[in_form][email]', 1, isset($mapping['in_form']['email'])) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>{{__('Phone')}}</td>
                            <td>{{ Form::text('field_mapping[phone]', $mapping['phone'] ?? 'phone', array('class' => 'form-control', 'placeholder' => __('e.g. mobile_number'))) }}</td>
                            <td class="text-center">{{ Form::checkbox('field_mapping[in_form][phone]', 1, isset($mapping['in_form']['phone'])) }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>{{__('Subject')}}</td>
                            <td>{{ Form::text('field_mapping[subject]', $mapping['subject'] ?? 'subject', array('class' => 'form-control', 'placeholder' => __('e.g. interest'))) }}</td>
                            <td class="text-center">{{ Form::checkbox('field_mapping[in_form][subject]', 1, isset($mapping['in_form']['subject'])) }}</td>
                            <td></td>
                        </tr>
                        @foreach($customFields as $field)
                            <tr>
                                <td>{{ $field->name }} ({{__('Custom')}})</td>
                                <td>{{ Form::text('field_mapping[custom]['.$field->id.']', $mapping['custom'][$field->id] ?? '', array('class' => 'form-control', 'placeholder' => __('JSON Key'))) }}</td>
                                <td class="text-center">{{ Form::checkbox('field_mapping[in_form][custom]['.$field->id.']', 1, isset($mapping['in_form']['custom'][$field->id])) }}</td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-primary add_custom_field_btn mt-2"><i class="ti ti-plus"></i> {{__('Add New Dynamic Field')}}</button>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}

<script>
    if ($(".choices").length > 0) {
        $($(".choices")).each(function(index,element) {
            var id = $(element).attr('id');
            var multipleCancelButton = new Choices(
                '#'+id, {
                    removeItemButton: true,
                }
            );
        });
    }

    $(document).on('change', '#pipeline_id', function() {
        var pipeline_id = $(this).val();
        getStages(pipeline_id);
    });

    function getStages(id) {
        $.ajax({
            url: '{{route('stages.json')}}',
            data: {pipeline_id: id, _token: $('meta[name="csrf-token"]').attr('content')},
            type: 'POST',
            success: function(data) {
                var stage_select = $('#stage_id');
                stage_select.empty();
                stage_select.append('<option value="">{{__('Select Stage')}}</option>');
                $.each(data, function(key, data) {
                    var selected = '';
                    if (key == '{{ $webhookEndpoint->stage_id }}') {
                        selected = 'selected';
                    }
                    stage_select.append('<option value="' + key + '" ' + selected + '>' + data + '</option>');
                });
            }
        });
    }

    $(document).on('click', '.add_custom_field_btn', function() {
        var html = `
            <tr>
                <td>
                    <input type="text" name="field_mapping[new][labels][]" class="form-control" placeholder="{{__('Field Label')}}" required>
                    <select name="field_mapping[new][types][]" class="form-control mt-1" required>
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="email">Email</option>
                        <option value="date">Date</option>
                        <option value="textarea">Textarea</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="field_mapping[new][keys][]" class="form-control" placeholder="{{__('JSON Key')}}" required>
                </td>
                <td class="text-center">
                    <input type="checkbox" name="field_mapping[new][in_form][]" value="1" checked>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove_mapping_row"><i class="ti ti-trash"></i></button>
                </td>
            </tr>
        `;
        $('#mapping_table tbody').append(html);
    });

    $(document).on('click', '.remove_mapping_row', function() {
        $(this).closest('tr').remove();
    });
</script>
