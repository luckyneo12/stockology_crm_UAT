<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;

class LeadDocumentFile extends Model
{
    protected $fillable = [
        'lead_id',
        'document_id',
        'file_name',
        'file_path'
    ];

    public function document() {
        return $this->belongsTo(LeadDocument::class, 'document_id');
    }
}
