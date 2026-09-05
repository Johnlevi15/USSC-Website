<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentRequest extends Model
{
    protected $table = 'document_requests';
    protected $primaryKey = 'request_id';
    public $timestamps = false;
    protected $fillable = ['user_id', 'document_type', 'status', 'reviewed_by', 'submitted_at'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by', 'admin_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(Document::class, 'request_id', 'request_id');
    }

    public function emailNotifications(): HasMany
    {
        return $this->hasMany(EmailNotification::class, 'request_id', 'request_id');
    }
}
