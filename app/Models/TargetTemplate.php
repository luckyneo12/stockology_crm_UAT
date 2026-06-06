<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'target_type',
        'pipeline_id',
        'stage_id',
        'custom_date_field',
        'workspace',
        'created_by'
    ];

    public function pipeline()
    {
        if (module_is_active('Lead')) {
            return $this->belongsTo(\Workdo\Lead\Entities\Pipeline::class, 'pipeline_id');
        }
        return null;
    }

    public function stage()
    {
        if (module_is_active('Lead')) {
            return $this->belongsTo(\Workdo\Lead\Entities\LeadStage::class, 'stage_id');
        }
        return null;
    }
}
