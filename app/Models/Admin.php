<?php

namespace App\Models;

use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Admin Model - Completely independent from User model
 *
 * Admins have their own authentication system and are not tied to the users table.
 * They manage document requests, events, lost & found items, and send email notifications.
 */
class Admin extends Authenticatable implements CanResetPassword
{
    use CanResetPasswordTrait, Notifiable;

    protected $table = 'admins';

    protected $primaryKey = 'admin_id';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = ['name', 'email', 'password_hash'];

    protected $hidden = ['password_hash', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new AdminResetPasswordNotification($token));
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

    public function activityLogs(): HasMany
    {
        return $this->hasMany(AdminActivityLog::class, 'admin_id', 'admin_id');
    }
}
