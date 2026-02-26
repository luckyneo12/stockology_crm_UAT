<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadFieldVisibility extends Model
{
    use HasFactory;

    protected $fillable = [
        'field_name',
        'role_id',
        'pipeline_id',
        'stage_id',
        'encryption_type',
        'masking_type',
        'workspace_id',
        'created_by',
    ];

    public function role()
    {
        return $this->hasOne('App\Models\Role', 'id', 'role_id');
    }
}
