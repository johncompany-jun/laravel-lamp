<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventApplicationSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'start_time',
        'end_time',
    ];

    /**
     * Get the event this application slot belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get all applications for this slot.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(EventApplication::class, 'event_application_slot_id');
    }
}
