<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;

class LeadCustomFieldValue extends Model
{
    protected $fillable = [
        'lead_id',
        'field_id',
        'value'
    ];

    public function field()
    {
        return $this->belongsTo(LeadCustomField::class , 'field_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class , 'lead_id');
    }
}
