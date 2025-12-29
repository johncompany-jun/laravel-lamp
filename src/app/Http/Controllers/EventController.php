<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventApplication;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of open events.
     */
    public function index()
    {
        $events = Event::where('status', 'open')
            ->whereNull('parent_event_id')
            ->where('event_date', '>=', today())
            ->orderBy('event_date')
            ->paginate(10);

        return view('events.index', compact('events'));
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        $event->load(['applicationSlots.applications']);

        $user = auth()->user();
        $existingApplications = EventApplication::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->with('applicationSlot')
            ->get()
            ->keyBy('event_application_slot_id');

        return view('events.show', compact('event', 'existingApplications'));
    }

    /**
     * Store a new event application.
     */
    public function apply(Request $request, Event $event)
    {
        $user = auth()->user();

        // Custom validation to handle the dynamic slots array
        $slots = $request->input('slots', []);

        // Filter out slots that don't have all required fields
        $validSlots = [];
        foreach ($slots as $slotId => $slotData) {
            if (isset($slotData['slot_id']) && isset($slotData['availability'])) {
                $validSlots[] = [
                    'slot_id' => $slotData['slot_id'],
                    'availability' => $slotData['availability'],
                ];
            }
        }

        if (empty($validSlots)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['slots' => 'Please select at least one time slot.']);
        }

        // Validate the filtered data
        $validated = $request->validate([
            'can_help_setup' => 'nullable|boolean',
            'can_help_cleanup' => 'nullable|boolean',
            'comment' => 'nullable|string|max:500',
        ]);

        $canHelpSetup = $request->input('can_help_setup', false);
        $canHelpCleanup = $request->input('can_help_cleanup', false);
        $comment = $validated['comment'] ?? null;

        // Delete existing applications for this event
        EventApplication::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->delete();

        // Create new applications for each selected slot
        foreach ($validSlots as $slotData) {
            EventApplication::create([
                'event_id' => $event->id,
                'event_application_slot_id' => $slotData['slot_id'],
                'user_id' => $user->id,
                'availability' => $slotData['availability'],
                'can_help_setup' => $canHelpSetup,
                'can_help_cleanup' => $canHelpCleanup,
                'comment' => $comment,
            ]);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Application submitted successfully!');
    }
}
