<?php

namespace App\Console\Commands;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Console\Command;

class CloseExpiredEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:close-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close events that have passed their event date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired events...');

        // Find all open events where the event date has passed
        $expiredEvents = Event::where('status', EventStatus::OPEN->value)
            ->where('event_date', '<', today())
            ->get();

        if ($expiredEvents->isEmpty()) {
            $this->info('No expired events found.');
            return 0;
        }

        $count = $expiredEvents->count();
        $this->info("Found {$count} expired event(s).");

        foreach ($expiredEvents as $event) {
            $event->update(['status' => EventStatus::CLOSED->value]);
            $this->line("Closed: {$event->title} (Date: {$event->event_date->format('Y-m-d')})");
        }

        $this->info("Successfully closed {$count} event(s).");
        return 0;
    }
}
