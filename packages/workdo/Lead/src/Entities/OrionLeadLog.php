<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class OrionLeadLog extends Model
{
    use HasFactory;

    protected $table = 'orion_lead_logs';

    protected $fillable = [
        'lead_id',
        'client_code',
        'api_type',
        'request_payload',
        'response_payload',
        'status',
        'error_reason',
        'workspace_id',
        'created_by'
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
