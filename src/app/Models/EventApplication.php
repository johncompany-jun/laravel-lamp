<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_application_slot_id',
        'user_id',
        'availability',
        'can_help_setup',
        'can_help_cleanup',
        'comment',
    ];

    /**
     * Get the event this application belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the application slot this application belongs to.
     */
    public function applicationSlot(): BelongsTo
    {
        return $this->belongsTo(EventApplicationSlot::class, 'event_application_slot_id');
    }

    /**
     * Get the user who applied.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if user is available.
     */
    public function isAvailable(): bool
    {
        return $this->availability === 'available';
    }
}
