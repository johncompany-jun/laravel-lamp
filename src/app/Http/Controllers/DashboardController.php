<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventApplication;
use App\Models\EventAssignment;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get open events (events accepting applications)
        // Include both parent events and child events (recurring events)
        $openEvents = Event::where('status', EventStatus::OPEN)
            ->where('is_template', false)
            ->where('event_date', '>=', today())
            ->orderBy('event_date')
            ->limit(5)
            ->get();

        // Get user's applications grouped by event
        $myApplications = EventApplication::where('user_id', $user->id)
            ->with(['event.applicationSlots', 'applicationSlot'])
            ->latest()
            ->get()
            ->groupBy('event_id')
            ->map(function ($applications) {
                return [
                    'event' => $applications->first()->event,
                    'applications' => $applications->keyBy('event_application_slot_id'),
                    'applied_at' => $applications->first()->created_at,
                ];
            })
            ->sortByDesc(function ($item) {
                return $item['event']->event_date;
            })
            ->take(5);

        // Get user's assignments (confirmed schedules)
        // Show assignments for events with status 'closed' (confirmed) or 'completed' (cancelled)
        $myAssignments = EventAssignment::where('user_id', $user->id)
            ->with(['event', 'slot'])
            ->whereHas('event', function ($query) {
                $query->where('event_date', '>=', today())
                    ->whereIn('status', [EventStatus::CLOSED, EventStatus::COMPLETED]);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('openEvents', 'myApplications', 'myAssignments'));
    }
}
