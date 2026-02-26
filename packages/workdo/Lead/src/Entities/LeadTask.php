<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id','user_id','name','description','date','time','priority','status','workspace'
    ];

    public static $priorities = [
        1 => 'Low',
        2 => 'Medium',
        3 => 'High',
    ];
    public static $status = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'done' => 'Done',
        'overdue' => 'Overdue',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function lead()
    {
        return $this->belongsTo('Workdo\Lead\Entities\Lead', 'lead_id', 'id');
    }

}
