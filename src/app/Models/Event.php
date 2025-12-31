<?php

namespace App\Models;

use App\Enums\EventStatus;
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
        'application_slot_duration',
        'location',
        'locations',
        'notes',
        'status',
        'created_by',
        'is_recurring',
        'recurrence_type',
        'recurrence_end_date',
        'parent_event_id',
        'is_template',
    ];

    protected $casts = [
        'event_date' => 'date',
        'recurrence_end_date' => 'date',
        'is_recurring' => 'boolean',
        'is_template' => 'boolean',
        'locations' => 'array',
        'status' => EventStatus::class,
    ];

    /**
     * Get the user who created this event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all time slots for this event (for assignment).
     */
    public function slots(): HasMany
    {
        return $this->hasMany(EventSlot::class);
    }

    /**
     * Get all application slots for this event (for user applications).
     */
    public function applicationSlots(): HasMany
    {
        return $this->hasMany(EventApplicationSlot::class);
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

    /**
     * Get the parent event (for recurring events).
     */
    public function parentEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'parent_event_id');
    }

    /**
     * Get child events (recurring instances).
     */
    public function childEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'parent_event_id');
    }
}
