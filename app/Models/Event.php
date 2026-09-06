<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    protected $table = 'events';

    protected $primaryKey = 'event_id';

    public $timestamps = false;

    protected $fillable = ['title', 'description', 'event_date', 'start_time', 'end_time', 'created_by'];

    protected function casts(): array
    {
        return ['event_date' => 'date'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by', 'admin_id');
    }

    public function calendars(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'event_calendar', 'event_id', 'admin_id')
            ->withPivot(['calendar_date', 'view_type']);
    }
}
