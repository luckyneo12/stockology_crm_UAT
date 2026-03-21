<?php

namespace Workdo\StockMarket\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockCategory extends Model
{
    use HasFactory;

    protected $table = 'stock_categories';

    protected $fillable = [
        'name',
        'type',
        'workspace',
        'created_by',
    ];

    public static $types = [
        'equity' => 'Equity',
        'fo' => 'F&O (Futures & Options)',
        'commodity' => 'Commodity',
        'currency' => 'Currency',
        'index' => 'Index',
    ];

    public function signals()
    {
        return $this->hasMany(StockSignal::class, 'category_id');
    }
}
