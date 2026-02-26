<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadFilter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'filters',
        'workspace_id',
    ];

    protected $casts = [
        'filters' => 'json',
    ];
}
