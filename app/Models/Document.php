<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $table = 'document';
    protected $primaryKey = 'request_id';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['request_id', 'field_name', 'field_value'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(DocumentRequest::class, 'request_id', 'request_id');
    }
}
