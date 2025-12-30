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
            'application_slot_duration' => 'required|in:30,60,90,120',
            'location' => 'nullable|string|max:255',
            'locations' => 'nullable|array|max:3',
            'locations.*' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,open,closed,completed',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'nullable|required_if:is_recurring,true|in:weekly',
            'recurrence_end_date' => 'nullable|required_if:is_recurring,true|date|after:event_date',
            'is_template' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['slot_duration'] = (int) $validated['slot_duration'];
        $validated['application_slot_duration'] = (int) $validated['application_slot_duration'];

        // Filter out empty locations
        if (isset($validated['locations'])) {
            $validated['locations'] = array_values(array_filter($validated['locations'], fn($loc) => !empty($loc)));
        }

        // Create the main event
        $event = Event::create($validated);

        // Generate application slots based on application_slot_duration
        $this->generateApplicationSlots($event);

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
    public function show(Event $event)
    {
        $event->load(['slots.assignments.user', 'applications.user', 'childEvents']);

        return view('admin.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        // Get template events for quick creation
        $templates = Event::where('is_template', true)
            ->where('id', '!=', $event->id)
            ->orderBy('title')
            ->get();

        return view('admin.events.edit', compact('event', 'templates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|in:10,20,30',
            'application_slot_duration' => 'required|in:30,60,90,120',
            'location' => 'nullable|string|max:255',
            'locations' => 'nullable|array|max:3',
            'locations.*' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,open,closed,completed',
            'is_template' => 'boolean',
        ]);

        $validated['slot_duration'] = (int) $validated['slot_duration'];
        $validated['application_slot_duration'] = (int) $validated['application_slot_duration'];

        // Filter out empty locations
        if (isset($validated['locations'])) {
            $validated['locations'] = array_values(array_filter($validated['locations'], fn($loc) => !empty($loc)));
        }

        // Check if time slots need to be regenerated
        $locationsChanged = json_encode($event->locations ?? []) !== json_encode($validated['locations'] ?? []);
        $needsSlotRegeneration =
            $event->start_time !== $validated['start_time'] ||
            $event->end_time !== $validated['end_time'] ||
            $event->slot_duration !== $validated['slot_duration'] ||
            $locationsChanged;

        // Check if application slots need to be regenerated
        $needsApplicationSlotRegeneration =
            $event->start_time !== $validated['start_time'] ||
            $event->end_time !== $validated['end_time'] ||
            $event->application_slot_duration !== $validated['application_slot_duration'];

        // Update the event
        $event->update($validated);

        // Regenerate application slots if needed
        if ($needsApplicationSlotRegeneration) {
            // Delete existing application slots (only if no applications)
            $hasApplications = $event->applicationSlots()->whereHas('applications')->exists();

            if (!$hasApplications) {
                $event->applicationSlots()->delete();
                $this->generateApplicationSlots($event);
            }
        }

        // Regenerate time slots if needed
        if ($needsSlotRegeneration) {
            // Delete existing slots (only if no assignments)
            $hasAssignments = $event->slots()->whereHas('assignments')->exists();

            if (!$hasAssignments) {
                $event->slots()->delete();
                $this->generateTimeSlots($event);
            }
        }

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Event updated successfully!');
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
     * Generate application slots for an event based on application_slot_duration.
     */
    protected function generateApplicationSlots(Event $event)
    {
        $startTime = \Carbon\Carbon::parse($event->start_time);
        $endTime = \Carbon\Carbon::parse($event->end_time);
        $duration = $event->application_slot_duration;

        $currentTime = $startTime->copy();

        while ($currentTime->lt($endTime)) {
            $slotEnd = $currentTime->copy()->addMinutes($duration);

            if ($slotEnd->lte($endTime)) {
                $event->applicationSlots()->create([
                    'start_time' => $currentTime->format('H:i:s'),
                    'end_time' => $slotEnd->format('H:i:s'),
                ]);
            }

            $currentTime->addMinutes($duration);
        }
    }

    /**
     * Generate time slots for an event based on slot duration.
     */
    protected function generateTimeSlots(Event $event)
    {
        $startTime = \Carbon\Carbon::parse($event->start_time);
        $endTime = \Carbon\Carbon::parse($event->end_time);
        $duration = $event->slot_duration;
        $locations = $event->locations ?? [];

        $currentTime = $startTime->copy();

        while ($currentTime->lt($endTime)) {
            $slotEnd = $currentTime->copy()->addMinutes($duration);

            if ($slotEnd->lte($endTime)) {
                // If locations are defined, create a slot for each location
                if (!empty($locations)) {
                    foreach ($locations as $location) {
                        $event->slots()->create([
                            'start_time' => $currentTime->format('H:i:s'),
                            'end_time' => $slotEnd->format('H:i:s'),
                            'location' => $location,
                            'capacity' => 1, // Default capacity, can be made configurable
                        ]);
                    }
                } else {
                    // No locations defined, create a single slot
                    $event->slots()->create([
                        'start_time' => $currentTime->format('H:i:s'),
                        'end_time' => $slotEnd->format('H:i:s'),
                        'capacity' => 1,
                    ]);
                }
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

    /**
     * Show the assignment creation page for an event.
     */
    public function createAssignments(Event $event)
    {
        // Load event with all necessary relationships
        $event->load([
            'slots',
            'applicationSlots.applications' => function ($query) {
                $query->with('user');
            }
        ]);

        // Get all applications for this event, grouped by user
        $applications = \App\Models\EventApplication::where('event_id', $event->id)
            ->with(['user', 'applicationSlot'])
            ->get()
            ->groupBy('user_id');

        // Get existing assignments
        $existingAssignments = \App\Models\EventAssignment::where('event_id', $event->id)
            ->with(['user', 'slot'])
            ->get();

        return view('admin.events.assignments.create', compact('event', 'applications', 'existingAssignments'));
    }

    /**
     * Store assignments for an event.
     */
    public function storeAssignments(Request $request, Event $event)
    {
        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.slot_id' => 'required|exists:event_slots,id',
            'assignments.*.user_id' => 'required|exists:users,id',
            'assignments.*.role' => 'required|in:participant,leader',
        ]);

        // Delete existing assignments for this event
        \App\Models\EventAssignment::where('event_id', $event->id)->delete();

        // Create new assignments
        foreach ($validated['assignments'] as $assignment) {
            \App\Models\EventAssignment::create([
                'event_id' => $event->id,
                'event_time_slot_id' => $assignment['slot_id'],
                'user_id' => $assignment['user_id'],
                'role' => $assignment['role'],
            ]);
        }

        return redirect()->route('admin.events.index')
            ->with('success', 'Assignments created successfully!');
    }
}
