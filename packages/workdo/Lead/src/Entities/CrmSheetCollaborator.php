<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CrmSheetCollaborator extends Model
{
    protected $table = 'crm_sheet_collaborators';

    protected $fillable = [
        'sheet_id',
        'user_id',
        'status',
    ];

    public function sheet()
    {
        return $this->belongsTo(CrmSheet::class, 'sheet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
