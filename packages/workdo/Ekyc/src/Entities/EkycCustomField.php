<?php

namespace Workdo\Ekyc\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EkycCustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'order',
        'workspace_id',
        'created_by',
    ];

    public static $fieldTypes = [
        'text' => 'Text',
        'email' => 'Email',
        'number' => 'Number',
        'date' => 'Date',
        'textarea' => 'Textarea',
        'select' => 'Select Box',
        'file' => 'File',
    ];
}
