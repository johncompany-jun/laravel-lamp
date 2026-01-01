<?php

namespace App\Console\Commands;

use App\Enums\EventStatus;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CompleteFinishedEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:complete-finished';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark closed events as completed after their end time has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for finished events...');

        // Find all closed events where the event date and end time have passed
        $finishedEvents = Event::where('status', EventStatus::CLOSED->value)
            ->where(function ($query) {
                $query->where('event_date', '<', today())
                    ->orWhere(function ($q) {
                        $q->where('event_date', '=', today())
                            ->whereRaw('end_time < ?', [now()->format('H:i:s')]);
                    });
            })
            ->get();

        if ($finishedEvents->isEmpty()) {
            $this->info('No finished events found.');
            return 0;
        }

        $count = $finishedEvents->count();
        $this->info("Found {$count} finished event(s).");

        foreach ($finishedEvents as $event) {
            $event->update(['status' => EventStatus::COMPLETED->value]);
            $this->line("Completed: {$event->title} (Date: {$event->event_date->format('Y-m-d')}, End: {$event->end_time})");
        }

        $this->info("Successfully completed {$count} event(s).");
        return 0;
    }
}
