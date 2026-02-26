<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;

class StageCustomField extends Model
{
    protected $fillable = [
        'stage_id',
        'custom_field_id',
        'entity_type',
        'is_required',
        'created_by',
        'workspace_id',
    ];
}
