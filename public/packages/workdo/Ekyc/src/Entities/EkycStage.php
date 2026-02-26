<?php

namespace Workdo\Ekyc\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EkycStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'pipeline_id',
        'created_by',
        'workspace_id',
        'order',
    ];

    public function pipeline()
    {
        return $this->belongsTo(EkycPipeline::class, 'pipeline_id', 'id');
    }
}
