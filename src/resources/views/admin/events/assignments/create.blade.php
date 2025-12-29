<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Assignments: {{ $event->title }}
            </h2>
            <a href="{{ route('admin.events.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                ← Back to Events
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Event Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Event Details</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Date</p>
                            <p class="font-medium">{{ $event->event_date->format('M d, Y (D)') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Time</p>
                            <p class="font-medium">{{ date('H:i', strtotime($event->start_time)) }} - {{ date('H:i', strtotime($event->end_time)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Applications</p>
                            <p class="font-medium">{{ $applications->count() }} users</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Time Slots ({{ $event->slot_duration }} min each)</p>
                            <p class="font-medium">{{ $event->slots->count() }} slots</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Grid -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Assign Users to Time Slots</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Click on a cell to assign a user to a time slot. Green cells indicate available users, gray cells indicate unavailable users (cannot be selected).
                    </p>

                    <form method="POST" action="{{ route('admin.events.assignments.store', $event) }}" id="assignmentForm">
                        @csrf

                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full border-collapse border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold">Time Slot</th>
                                        @foreach($applications as $userId => $userApps)
                                            <th class="border border-gray-300 px-4 py-2 text-left font-semibold" style="writing-mode: horizontal-tb;">
                                                {{ $userApps->first()->user->name }}
                                                <div class="text-xs font-normal text-gray-500 mt-1">
                                                    @php
                                                        $setupCount = $userApps->where('can_help_setup', true)->count();
                                                        $cleanupCount = $userApps->where('can_help_cleanup', true)->count();
                                                    @endphp
                                                    @if($setupCount > 0)
                                                        <span class="inline-block px-1 py-0.5 bg-blue-100 text-blue-800 rounded text-xs">Setup</span>
                                                    @endif
                                                    @if($cleanupCount > 0)
                                                        <span class="inline-block px-1 py-0.5 bg-blue-100 text-blue-800 rounded text-xs">Cleanup</span>
                                                    @endif
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($event->slots as $slot)
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2 font-medium bg-gray-50">
                                                {{ date('H:i', strtotime($slot->start_time)) }} - {{ date('H:i', strtotime($slot->end_time)) }}
                                            </td>
                                            @foreach($applications as $userId => $userApps)
                                                @php
                                                    // Find application for this specific time slot
                                                    // We need to check if user applied to an application slot that covers this time slot
                                                    $hasApplication = false;
                                                    $isAvailable = false;

                                                    foreach ($userApps as $app) {
                                                        $appSlotStart = \Carbon\Carbon::parse($app->applicationSlot->start_time);
                                                        $appSlotEnd = \Carbon\Carbon::parse($app->applicationSlot->end_time);
                                                        $timeSlotStart = \Carbon\Carbon::parse($slot->start_time);
                                                        $timeSlotEnd = \Carbon\Carbon::parse($slot->end_time);

                                                        // Check if this time slot falls within the application slot
                                                        if ($timeSlotStart->gte($appSlotStart) && $timeSlotEnd->lte($appSlotEnd)) {
                                                            $hasApplication = true;
                                                            if ($app->availability === 'available') {
                                                                $isAvailable = true;
                                                            }
                                                            break;
                                                        }
                                                    }

                                                    // Check if already assigned
                                                    $isAssigned = $existingAssignments->where('event_time_slot_id', $slot->id)
                                                        ->where('user_id', $userId)
                                                        ->isNotEmpty();
                                                @endphp

                                                <td class="border border-gray-300 px-2 py-2 text-center {{ $isAssigned ? 'bg-indigo-100 cursor-pointer' : ($hasApplication && $isAvailable ? 'bg-green-50 cursor-pointer hover:bg-green-100' : ($hasApplication ? 'bg-gray-200' : 'bg-white')) }}"
                                                    data-slot-id="{{ $slot->id }}"
                                                    data-user-id="{{ $userId }}"
                                                    data-available="{{ $isAvailable ? '1' : '0' }}"
                                                    data-has-application="{{ $hasApplication ? '1' : '0' }}"
                                                    data-is-assigned="{{ $isAssigned ? '1' : '0' }}"
                                                    onclick="toggleAssignment(this)"
                                                    title="Slot:{{ $slot->id }}, User:{{ $userId }}, Has:{{ $hasApplication ? 'Y' : 'N' }}, Avail:{{ $isAvailable ? 'Y' : 'N' }}">
                                                    @if($isAssigned)
                                                        <span class="inline-block w-6 h-6 bg-indigo-600 rounded-full" style="display: inline-block !important; width: 24px !important; height: 24px !important; background-color: #4F46E5 !important; border-radius: 50% !important;"></span>
                                                    @elseif($hasApplication && $isAvailable)
                                                        <span class="inline-block w-6 h-6 border-2 border-green-500 rounded-full" style="display: inline-block !important; width: 24px !important; height: 24px !important; border: 2px solid #10B981 !important; border-radius: 50% !important;"></span>
                                                    @elseif($hasApplication && !$isAvailable)
                                                        <span class="inline-block w-6 h-6 bg-gray-400 rounded-full" style="display: inline-block !important; width: 24px !important; height: 24px !important; background-color: #9CA3AF !important; border-radius: 50% !important;" title="Unavailable"></span>
                                                    @else
                                                        <span class="text-gray-300">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            <h4 class="font-semibold mb-2">Legend:</h4>
                            <div class="flex gap-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-6 h-6 bg-indigo-600 rounded-full"></span>
                                    <span>Assigned</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-6 h-6 border-2 border-green-500 rounded-full"></span>
                                    <span>Available (Click to assign)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-block w-6 h-6 bg-gray-400 rounded-full"></span>
                                    <span>Unavailable (Cannot assign)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-300">-</span>
                                    <span>No application</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between items-center">
                            <div id="assignmentCount" class="text-sm text-gray-600">
                                <span class="font-semibold" id="assignedCount">0</span> assignments created
                            </div>
                            <div class="flex gap-4">
                                <a href="{{ route('admin.events.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                    Cancel
                                </a>
                                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700" style="display: block !important; visibility: visible !important; background-color: #4F46E5 !important; color: white !important;">
                                    Save Assignments
                                </button>
                            </div>
                        </div>

                        <!-- Hidden inputs for assignments -->
                        <div id="assignmentInputs"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let assignments = new Set();

        // Load existing assignments
        @foreach($existingAssignments as $assignment)
            assignments.add('{{ $assignment->event_time_slot_id }}_{{ $assignment->user_id }}');
        @endforeach

        function toggleAssignment(cell) {
            const slotId = cell.dataset.slotId;
            const userId = cell.dataset.userId;
            const isAvailable = cell.dataset.available === '1';
            const hasApplication = cell.dataset.hasApplication === '1';

            // Only allow toggling for available applications
            if (!hasApplication || !isAvailable) {
                return;
            }

            const key = `${slotId}_${userId}`;
            const circle = cell.querySelector('span');

            if (assignments.has(key)) {
                // Remove assignment
                assignments.delete(key);
                circle.className = 'inline-block w-6 h-6 border-2 border-green-500 rounded-full';
                circle.style.cssText = 'display: inline-block !important; width: 24px !important; height: 24px !important; border: 2px solid #10B981 !important; border-radius: 50% !important; background-color: transparent !important;';
                cell.classList.remove('bg-indigo-100', 'hover:bg-indigo-200');
                cell.classList.add('bg-green-50', 'hover:bg-green-100');
            } else {
                // Add assignment
                assignments.add(key);
                circle.className = 'inline-block w-6 h-6 bg-indigo-600 rounded-full';
                circle.style.cssText = 'display: inline-block !important; width: 24px !important; height: 24px !important; background-color: #4F46E5 !important; border-radius: 50% !important;';
                cell.classList.remove('bg-green-50', 'hover:bg-green-100');
                cell.classList.add('bg-indigo-100', 'hover:bg-indigo-200');
            }

            updateAssignmentInputs();
            updateAssignmentCount();
        }

        function updateAssignmentInputs() {
            const container = document.getElementById('assignmentInputs');
            container.innerHTML = '';

            let index = 0;
            assignments.forEach(key => {
                const [slotId, userId] = key.split('_');

                const slotInput = document.createElement('input');
                slotInput.type = 'hidden';
                slotInput.name = `assignments[${index}][slot_id]`;
                slotInput.value = slotId;
                container.appendChild(slotInput);

                const userInput = document.createElement('input');
                userInput.type = 'hidden';
                userInput.name = `assignments[${index}][user_id]`;
                userInput.value = userId;
                container.appendChild(userInput);

                index++;
            });
        }

        function updateAssignmentCount() {
            document.getElementById('assignedCount').textContent = assignments.size;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateAssignmentInputs();
            updateAssignmentCount();
        });
    </script>
</x-app-layout>
