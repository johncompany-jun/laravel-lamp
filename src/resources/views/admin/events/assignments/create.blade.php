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
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
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
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Date</p>
                            <p class="font-medium">{{ $event->event_date->format('M d, Y (D)') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Time</p>
                            <p class="font-medium">{{ date('H:i', strtotime($event->start_time)) }} - {{ date('H:i', strtotime($event->end_time)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Locations</p>
                            <p class="font-medium">{{ !empty($event->locations) ? implode(', ', $event->locations) : 'Not set' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Grid -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Assign Users to Time Slots × Locations</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Click on cells to open user selection. Each location typically needs 2 participants + 1 leader (optional).
                    </p>

                    <form method="POST" action="{{ route('admin.events.assignments.store', $event) }}" id="assignmentForm">
                        @csrf

                        @php
                            // Get unique time slots (sorted)
                            $timeSlots = $event->slots->unique(function ($slot) {
                                return $slot->start_time . '-' . $slot->end_time;
                            })->sortBy('start_time')->values();

                            // Get locations
                            $locations = $event->locations ?? [];

                            // Group slots by time and location
                            $slotMatrix = [];
                            foreach ($event->slots as $slot) {
                                $timeKey = $slot->start_time . '-' . $slot->end_time;
                                $locationKey = $slot->location ?? 'default';
                                $slotMatrix[$timeKey][$locationKey] = $slot;
                            }

                            // Get all users with their applications
                            $availableUsers = $applications->map(function ($userApps, $userId) {
                                return [
                                    'id' => $userId,
                                    'name' => $userApps->first()->user->name,
                                    'applications' => $userApps,
                                ];
                            })->values();
                        @endphp

                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full border-collapse border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold sticky left-0 bg-gray-100 z-10">Time</th>
                                        @if(empty($locations))
                                            <th class="border border-gray-300 px-4 py-2 text-center font-semibold">
                                                Assignments
                                            </th>
                                        @else
                                            @foreach($locations as $location)
                                                <th class="border border-gray-300 px-4 py-2 text-center font-semibold">
                                                    {{ $location }}
                                                    <div class="text-xs font-normal text-gray-500 mt-1">
                                                        Participants (2) + Leader (1)
                                                    </div>
                                                </th>
                                            @endforeach
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($timeSlots as $timeSlot)
                                        @php
                                            $timeKey = $timeSlot->start_time . '-' . $timeSlot->end_time;
                                            $startTime = date('H:i', strtotime($timeSlot->start_time));
                                            $endTime = date('H:i', strtotime($timeSlot->end_time));
                                        @endphp
                                        <tr>
                                            <td class="border border-gray-300 px-4 py-2 font-medium sticky left-0 bg-white z-10">
                                                {{ $startTime }}<br>
                                                <span class="text-xs text-gray-500">{{ $endTime }}</span>
                                            </td>
                                            @if(empty($locations))
                                                @php
                                                    $slot = $slotMatrix[$timeKey]['default'] ?? null;
                                                @endphp
                                                <td class="border border-gray-300 px-2 py-2 bg-gray-50 cursor-pointer hover:bg-gray-100"
                                                    data-slot-id="{{ $slot?->id }}"
                                                    data-time="{{ $startTime }}-{{$endTime}}"
                                                    data-location="default"
                                                    onclick="openAssignmentModal(this)">
                                                    <div class="min-h-[60px] assignment-cell" id="cell-{{ $slot?->id }}">
                                                        <span class="text-gray-400 text-sm">Click to assign</span>
                                                    </div>
                                                </td>
                                            @else
                                                @foreach($locations as $location)
                                                    @php
                                                        $slot = $slotMatrix[$timeKey][$location] ?? null;
                                                    @endphp
                                                    <td class="border border-gray-300 px-2 py-2 bg-gray-50 cursor-pointer hover:bg-gray-100"
                                                        data-slot-id="{{ $slot?->id }}"
                                                        data-time="{{ $startTime }}-{{$endTime}}"
                                                        data-location="{{ $location }}"
                                                        onclick="openAssignmentModal(this)">
                                                        <div class="min-h-[60px] assignment-cell" id="cell-{{ $slot?->id }}">
                                                            <span class="text-gray-400 text-sm">Click to assign</span>
                                                        </div>
                                                    </td>
                                                @endforeach
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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

    <!-- Assignment Modal -->
    <div id="assignmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Assign Users - <span id="modalTitle"></span>
                </h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Participants (Select 2)</label>
                    <div id="participantsList" class="space-y-2 max-h-60 overflow-y-auto p-2 border rounded">
                        <!-- User checkboxes will be inserted here -->
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Leader (Optional, Select 1)</label>
                    <div id="leadersList" class="space-y-2 max-h-40 overflow-y-auto p-2 border rounded">
                        <!-- Leader radio buttons will be inserted here -->
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeAssignmentModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="button" onclick="saveAssignment()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Store assignments: { slotId: { participants: [userId1, userId2], leader: userId3 } }
        const assignments = new Map();
        let currentSlotId = null;
        let currentCell = null;

        // Available users data
        const availableUsers = @json($availableUsers);

        // Load existing assignments
        @foreach($existingAssignments as $assignment)
            const slotId{{ $assignment->event_time_slot_id }} = '{{ $assignment->event_time_slot_id }}';
            if (!assignments.has(slotId{{ $assignment->event_time_slot_id }})) {
                assignments.set(slotId{{ $assignment->event_time_slot_id }}, { participants: [], leader: null });
            }
            if ('{{ $assignment->role }}' === 'leader') {
                assignments.get(slotId{{ $assignment->event_time_slot_id }}).leader = {{ $assignment->user_id }};
            } else {
                assignments.get(slotId{{ $assignment->event_time_slot_id }}).participants.push({{ $assignment->user_id }});
            }
        @endforeach

        // Update display for existing assignments
        assignments.forEach((data, slotId) => {
            updateCellDisplay(slotId);
        });

        function openAssignmentModal(cell) {
            currentCell = cell;
            currentSlotId = cell.dataset.slotId;
            const time = cell.dataset.time;
            const location = cell.dataset.location;

            if (!currentSlotId) {
                alert('Invalid slot');
                return;
            }

            document.getElementById('modalTitle').textContent = `${time} - ${location}`;

            // Get current assignments for this slot
            const currentAssignment = assignments.get(currentSlotId) || { participants: [], leader: null };

            // Populate participants list
            const participantsList = document.getElementById('participantsList');
            participantsList.innerHTML = '';
            availableUsers.forEach(user => {
                const div = document.createElement('div');
                div.className = 'flex items-center';
                div.innerHTML = `
                    <input type="checkbox" id="participant-${user.id}" value="${user.id}"
                        ${currentAssignment.participants.includes(user.id) ? 'checked' : ''}
                        class="participant-checkbox mr-2">
                    <label for="participant-${user.id}" class="cursor-pointer">${user.name}</label>
                `;
                participantsList.appendChild(div);
            });

            // Populate leaders list
            const leadersList = document.getElementById('leadersList');
            leadersList.innerHTML = '<div class="flex items-center"><input type="radio" name="leader" value="" id="leader-none" ' + (!currentAssignment.leader ? 'checked' : '') + ' class="mr-2"><label for="leader-none" class="cursor-pointer">なし</label></div>';
            availableUsers.forEach(user => {
                const div = document.createElement('div');
                div.className = 'flex items-center';
                div.innerHTML = `
                    <input type="radio" name="leader" value="${user.id}" id="leader-${user.id}"
                        ${currentAssignment.leader === user.id ? 'checked' : ''}
                        class="mr-2">
                    <label for="leader-${user.id}" class="cursor-pointer">${user.name}</label>
                `;
                leadersList.appendChild(div);
            });

            document.getElementById('assignmentModal').classList.remove('hidden');
        }

        function closeAssignmentModal() {
            document.getElementById('assignmentModal').classList.add('hidden');
            currentSlotId = null;
            currentCell = null;
        }

        function saveAssignment() {
            if (!currentSlotId) return;

            // Get selected participants
            const participantCheckboxes = document.querySelectorAll('.participant-checkbox:checked');
            const participants = Array.from(participantCheckboxes).map(cb => parseInt(cb.value));

            // Get selected leader
            const leaderRadio = document.querySelector('input[name="leader"]:checked');
            const leader = leaderRadio && leaderRadio.value ? parseInt(leaderRadio.value) : null;

            // Save assignment
            assignments.set(currentSlotId, {
                participants: participants,
                leader: leader
            });

            // Update cell display
            updateCellDisplay(currentSlotId);

            // Update hidden inputs
            updateAssignmentInputs();
            updateAssignmentCount();

            closeAssignmentModal();
        }

        function updateCellDisplay(slotId) {
            const cell = document.getElementById(`cell-${slotId}`);
            if (!cell) return;

            const assignment = assignments.get(slotId);
            if (!assignment || (assignment.participants.length === 0 && !assignment.leader)) {
                cell.innerHTML = '<span class="text-gray-400 text-sm">Click to assign</span>';
                return;
            }

            let html = '';

            // Display participants
            if (assignment.participants.length > 0) {
                html += '<div class="mb-2"><span class="text-xs font-semibold text-gray-600">Participants:</span><br>';
                assignment.participants.forEach(userId => {
                    const user = availableUsers.find(u => u.id === userId);
                    if (user) {
                        html += `<span class="inline-block px-2 py-1 bg-green-100 text-green-800 rounded text-xs mr-1 mb-1">${user.name}</span>`;
                    }
                });
                html += '</div>';
            }

            // Display leader
            if (assignment.leader) {
                const leader = availableUsers.find(u => u.id === assignment.leader);
                if (leader) {
                    html += '<div><span class="text-xs font-semibold text-gray-600">Leader:</span><br>';
                    html += `<span class="inline-block px-2 py-1 bg-indigo-100 text-indigo-800 rounded text-xs">${leader.name}</span>`;
                    html += '</div>';
                }
            }

            cell.innerHTML = html;
        }

        function updateAssignmentInputs() {
            const container = document.getElementById('assignmentInputs');
            container.innerHTML = '';

            let index = 0;
            assignments.forEach((data, slotId) => {
                // Add participants
                data.participants.forEach(userId => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `assignments[${index}][slot_id]`;
                    input.value = slotId;
                    container.appendChild(input);

                    const userInput = document.createElement('input');
                    userInput.type = 'hidden';
                    userInput.name = `assignments[${index}][user_id]`;
                    userInput.value = userId;
                    container.appendChild(userInput);

                    const roleInput = document.createElement('input');
                    roleInput.type = 'hidden';
                    roleInput.name = `assignments[${index}][role]`;
                    roleInput.value = 'participant';
                    container.appendChild(roleInput);

                    index++;
                });

                // Add leader
                if (data.leader) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `assignments[${index}][slot_id]`;
                    input.value = slotId;
                    container.appendChild(input);

                    const userInput = document.createElement('input');
                    userInput.type = 'hidden';
                    userInput.name = `assignments[${index}][user_id]`;
                    userInput.value = data.leader;
                    container.appendChild(userInput);

                    const roleInput = document.createElement('input');
                    roleInput.type = 'hidden';
                    roleInput.name = `assignments[${index}][role]`;
                    roleInput.value = 'leader';
                    container.appendChild(roleInput);

                    index++;
                }
            });
        }

        function updateAssignmentCount() {
            let total = 0;
            assignments.forEach(data => {
                total += data.participants.length + (data.leader ? 1 : 0);
            });
            document.getElementById('assignedCount').textContent = total;
        }

        // Initial update
        updateAssignmentCount();
    </script>
</x-app-layout>
