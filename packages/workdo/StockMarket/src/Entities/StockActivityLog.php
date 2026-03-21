<?php

namespace Workdo\StockMarket\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'signal_id',
        'user_id',
        'action',
        'details',
        'workspace_id'
    ];

    public function signal()
    {
        return $this->belongsTo(StockSignal::class, 'signal_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
