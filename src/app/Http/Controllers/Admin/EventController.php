<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::with('creator')
            ->whereNull('parent_event_id') // Only show parent events, not recurring instances
            ->latest('event_date')
            ->paginate(20);

        return view('admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get template events for quick creation
        $templates = Event::where('is_template', true)
            ->orderBy('title')
            ->get();

        return view('admin.events.create', compact('templates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|in:10,20,30',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,open,closed,completed',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'nullable|required_if:is_recurring,true|in:weekly',
            'recurrence_end_date' => 'nullable|required_if:is_recurring,true|date|after:event_date',
            'is_template' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();

        // Create the main event
        $event = Event::create($validated);

        // Generate time slots based on slot_duration
        $this->generateTimeSlots($event);

        // If recurring, create recurring events
        if ($request->boolean('is_recurring') && $request->recurrence_type === 'weekly') {
            $this->createRecurringEvents($event, $request->recurrence_end_date);
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted successfully!');
    }

    /**
     * Generate time slots for an event based on slot duration.
     */
    protected function generateTimeSlots(Event $event)
    {
        $startTime = \Carbon\Carbon::parse($event->start_time);
        $endTime = \Carbon\Carbon::parse($event->end_time);
        $duration = $event->slot_duration;

        $currentTime = $startTime->copy();

        while ($currentTime->lt($endTime)) {
            $slotEnd = $currentTime->copy()->addMinutes($duration);

            if ($slotEnd->lte($endTime)) {
                $event->slots()->create([
                    'start_time' => $currentTime->format('H:i:s'),
                    'end_time' => $slotEnd->format('H:i:s'),
                    'capacity' => 1, // Default capacity, can be made configurable
                ]);
            }

            $currentTime->addMinutes($duration);
        }
    }

    /**
     * Create recurring events based on the parent event.
     */
    protected function createRecurringEvents(Event $parentEvent, $endDate)
    {
        $currentDate = \Carbon\Carbon::parse($parentEvent->event_date)->addWeek();
        $endDate = \Carbon\Carbon::parse($endDate);

        while ($currentDate->lte($endDate)) {
            $recurringEvent = $parentEvent->replicate();
            $recurringEvent->event_date = $currentDate->format('Y-m-d');
            $recurringEvent->parent_event_id = $parentEvent->id;
            $recurringEvent->is_recurring = false; // Child events are not recurring
            $recurringEvent->recurrence_type = null;
            $recurringEvent->recurrence_end_date = null;
            $recurringEvent->save();

            // Generate time slots for recurring event
            $this->generateTimeSlots($recurringEvent);

            $currentDate->addWeek();
        }
    }
}
