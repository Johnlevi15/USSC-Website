<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LostFoundItem extends Model
{
    protected $table = 'lost_found_items';
    protected $primaryKey = 'item_id';
    public $timestamps = false;
    protected $fillable = ['posted_by', 'item_name', 'category', 'description', 'status', 'reviewed_by'];

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by', 'admin_id');
    }
}
