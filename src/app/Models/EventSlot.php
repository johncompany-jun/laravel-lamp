<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'start_time',
        'end_time',
        'location',
        'capacity',
    ];

    /**
     * Get the event this slot belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get all assignments for this slot.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(EventAssignment::class);
    }

    /**
     * Check if slot is full.
     */
    public function isFull(): bool
    {
        return $this->assignments()->count() >= $this->capacity;
    }
}
