<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Admin extends Model
{
    protected $table = 'admins';
    protected $primaryKey = 'admin_id';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['admin_id', 'password_hash'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function reviewedRequests(): HasMany
    {
        return $this->hasMany(DocumentRequest::class, 'reviewed_by', 'admin_id');
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by', 'admin_id');
    }

    public function calendarEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_calendar', 'admin_id', 'event_id')
            ->withPivot(['calendar_date', 'view_type']);
    }

    public function reviewedItems(): HasMany
    {
        return $this->hasMany(LostFoundItem::class, 'reviewed_by', 'admin_id');
    }

    public function emailNotifications(): HasMany
    {
        return $this->hasMany(EmailNotification::class, 'sent_by', 'admin_id');
    }
}
