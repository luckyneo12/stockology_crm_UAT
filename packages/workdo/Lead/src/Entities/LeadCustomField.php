<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;

class LeadCustomField extends Model
{
    protected $fillable = [
        'name',
        'type',
        'options',
        'order',
        'is_required',
        'workspace_id',
        'created_by',
        'section_id',
        'width',
        'is_system',
        'system_field_id',
        'visible_stages',
        'visible_roles',
        'required_stages',
        'stage_min_values',
        'is_filterable',
        'icon',
        'api_url',
        'api_method',
        'api_trigger_stage_id',
        'api_response_key',
        'pipeline_id',
    ];

    protected $casts = [
        'visible_stages' => 'array',
        'visible_roles' => 'array',
        'required_stages' => 'array',
        'stage_min_values' => 'array',
        'is_filterable' => 'boolean',
    ];

    public static $fieldTypes = [
        'text' => 'Text',
        'email' => 'Email',
        'number' => 'Number',
        'date' => 'Date',
        'textarea' => 'Textarea',
        'select' => 'Select Box',
        'multi_select' => 'Multi Select',
        'file' => 'File',
    ];
}
