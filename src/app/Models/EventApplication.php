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
        'user_id',
        'availability',
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
