<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CrmSheet extends Model
{
    protected $table = 'crm_sheets';

    protected $fillable = [
        'name',
        'workspace_id',
        'created_by',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function collaborators()
    {
        return $this->hasMany(CrmSheetCollaborator::class, 'sheet_id');
    }

    public function acceptedCollaborators()
    {
        return $this->hasMany(CrmSheetCollaborator::class, 'sheet_id')->where('status', 'accepted');
    }
}
