<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventCalendar extends Model
{
    protected $table = 'event_calendar';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['admin_id', 'event_id', 'calendar_date', 'view_type'];

    protected function casts(): array
    {
        return ['calendar_date' => 'date'];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'admin_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'event_id');
    }
}
