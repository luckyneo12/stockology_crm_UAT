<?php

namespace Workdo\Ekyc\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EkycCustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'ekyc_id',
        'field_id',
        'value'
    ];

    public function field()
    {
        return $this->belongsTo(EkycCustomField::class, 'field_id');
    }
}
