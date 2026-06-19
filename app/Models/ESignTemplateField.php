<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ESignTemplateField extends Model
{
    use HasFactory;

    protected $table = 'esign_template_fields';

    protected $fillable = [
        'esign_template_id',
        'field_key',
        'label',
        'type',
        'page_num',
        'x_coordinate',
        'y_coordinate',
        'width',
        'height'
    ];

    public function template()
    {
        return $this->belongsTo(ESignTemplate::class, 'esign_template_id');
    }
}
