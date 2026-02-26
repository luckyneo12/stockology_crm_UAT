<?php

namespace Workdo\Ekyc\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EkycRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'digio_id',
        'step_name',
        'status',
        'response_data',
        'error_message',
        'workspace_id',
    ];

    protected $casts = [
        'response_data' => 'array',
    ];

    public function lead()
    {
        return $this->belongsTo('Workdo\Ekyc\Entities\EkycLead', 'lead_id', 'id');
    }
}
