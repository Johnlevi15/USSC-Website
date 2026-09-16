<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTypeField extends Model
{
    protected $fillable = [
        'document_type_id',
        'field_name',
        'field_label',
        'field_type',
        'field_options',
        'is_required',
        'validation_rules',
        'display_order',
    ];

    protected $casts = [
        'field_options' => 'array',
        'is_required' => 'boolean',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }
}
