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

        // Get user's applications
        $myApplications = EventApplication::where('user_id', $user->id)
            ->with(['event', 'applicationSlot'])
            ->latest()
            ->limit(5)
            ->get();

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
