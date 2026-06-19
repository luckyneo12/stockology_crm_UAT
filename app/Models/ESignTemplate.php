<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ESignTemplate extends Model
{
    use HasFactory;

    protected $table = 'esign_templates';

    protected $fillable = [
        'name',
        'pdf_url'
    ];

    public function fields()
    {
        return $this->hasMany(ESignTemplateField::class, 'esign_template_id');
    }
}
