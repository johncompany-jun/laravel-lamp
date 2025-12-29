<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'event_date',
        'start_time',
        'end_time',
        'slot_duration',
        'location',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    /**
     * Get the user who created this event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all time slots for this event.
     */
    public function slots(): HasMany
    {
        return $this->hasMany(EventSlot::class);
    }

    /**
     * Get all applications for this event.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(EventApplication::class);
    }

    /**
     * Get all assignments for this event.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(EventAssignment::class);
    }
}
