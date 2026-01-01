<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventApplication;
use App\Models\EventAssignment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get open events (events accepting applications)
        $openEvents = Event::where('status', 'open')
            ->whereNull('parent_event_id')
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
        $myAssignments = EventAssignment::where('user_id', $user->id)
            ->with(['event', 'slot'])
            ->whereHas('event', function ($query) {
                $query->where('event_date', '>=', today());
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('openEvents', 'myApplications', 'myAssignments'));
    }
}
