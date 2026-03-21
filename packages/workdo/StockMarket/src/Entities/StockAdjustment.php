<?php

namespace Workdo\StockMarket\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $table = 'stock_adjustments';

    protected $fillable = [
        'signal_id',
        'target',
        'stoploss',
        'quantity',
        'note',
        'created_by',
    ];

    public function signal()
    {
        return $this->belongsTo(StockSignal::class, 'signal_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
