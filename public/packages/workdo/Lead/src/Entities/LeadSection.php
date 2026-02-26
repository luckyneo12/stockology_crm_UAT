<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;

class LeadSection extends Model
{
    protected $fillable = [
        'name',
        'order',
        'columns',
        'workspace_id',
        'is_system'
    ];

    public function fields()
    {
        return $this->hasMany(LeadCustomField::class, 'section_id')->orderBy('order');
    }
}
