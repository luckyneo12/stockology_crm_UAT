<?php

namespace Workdo\Ekyc\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EkycPipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'created_by',
        'workspace_id',
    ];

    public function stages()
    {
        return $this->hasMany(EkycStage::class, 'pipeline_id', 'id')->orderBy('order');
    }
}
