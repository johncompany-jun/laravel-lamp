<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('events.create_assignments_title') }}: {{ $event->title }}
            </h2>
            <a href="{{ route('admin.events.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                {{ __('events.back_to_events') }}
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
                    <h3 class="text-lg font-semibold mb-4">{{ __('events.event_details') }}</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('events.date') }}</p>
                            <p class="font-medium">{{ $event->event_date->format('Y-m-d (D)') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('events.time') }}</p>
                            <p class="font-medium">{{ date('H:i', strtotime($event->start_time)) }} - {{ date('H:i', strtotime($event->end_time)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('events.locations') }}</p>
                            <p class="font-medium">{{ !empty($event->locations) ? implode(', ', $event->locations) : __('events.not_set') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment Grid -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('events.assign_users_to_slots') }}</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('events.assignment_instruction') }}
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
                                        <th class="border border-gray-300 px-4 py-2 text-left font-semibold sticky left-0 bg-gray-100 z-10">{{ __('events.time') }}</th>
                                        @if(empty($locations))
                                            <th class="border border-gray-300 px-4 py-2 text-center font-semibold">
                                                {{ __('events.assignments') }}
                                            </th>
                                        @else
                                            @foreach($locations as $location)
                                                <th class="border border-gray-300 px-4 py-2 text-center font-semibold">
                                                    {{ $location }}
                                                    <div class="text-xs font-normal text-gray-500 mt-1">
                                                        {{ __('events.participants_and_leader') }}
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
                                                <td class="border border-gray-300 px-2 py-2 {{ $slot ? 'bg-gray-50 cursor-pointer hover:bg-gray-100' : 'bg-red-50' }}"
                                                    data-slot-id="{{ $slot?->id ?? '' }}"
                                                    data-time="{{ $startTime }}-{{$endTime}}"
                                                    data-location="default"
                                                    onclick="{{ $slot ? 'openAssignmentModal(this)' : 'alert(\'' . __('events.no_slot_found') . '\')' }}">
                                                    <div class="min-h-[60px] assignment-cell" id="cell-{{ $slot?->id ?? 'none' }}">
                                                        @if($slot)
                                                            <span class="text-gray-400 text-sm">{{ __('events.click_to_assign') }}</span>
                                                        @else
                                                            <span class="text-red-500 text-xs">{{ __('events.no_slot') }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                            @else
                                                @foreach($locations as $location)
                                                    @php
                                                        $slot = $slotMatrix[$timeKey][$location] ?? null;
                                                    @endphp
                                                    <td class="border border-gray-300 px-2 py-2 {{ $slot ? 'bg-gray-50 cursor-pointer hover:bg-gray-100' : 'bg-red-50' }}"
                                                        data-slot-id="{{ $slot?->id ?? '' }}"
                                                        data-time="{{ $startTime }}-{{$endTime}}"
                                                        data-location="{{ $location }}"
                                                        onclick="{{ $slot ? 'openAssignmentModal(this)' : 'alert(\'' . $location . __('events.no_slot_for_location') . '\')' }}">
                                                        <div class="min-h-[60px] assignment-cell" id="cell-{{ $slot?->id ?? 'none-' . $loop->index }}">
                                                            @if($slot)
                                                                <span class="text-gray-400 text-sm">{{ __('events.click_to_assign') }}</span>
                                                            @else
                                                                <span class="text-red-500 text-xs">{{ __('events.no_slot') }}</span>
                                                            @endif
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
                                <span class="font-semibold" id="assignedCount">0</span> {{ __('events.assignments_created') }}
                            </div>
                            <div style="display: flex; gap: 16px;">
                                <a href="{{ route('admin.events.index') }}" style="display: inline-block; padding: 8px 16px; background-color: #E5E7EB; color: #374151; border-radius: 6px; text-decoration: none; border: 1px solid #D1D5DB; font-size: 14px; font-weight: 500;">
                                    {{ __('events.cancel') }}
                                </a>
                                <button type="submit" style="display: inline-block; padding: 10px 24px; background-color: #4F46E5; color: white; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#4338CA'" onmouseout="this.style.backgroundColor='#4F46E5'">
                                    {{ __('events.save_assignments') }}
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
    <div id="assignmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50" style="display: none;" onclick="event.target === this && closeAssignmentModal()">
        <div class="relative mx-auto border shadow-lg rounded-md bg-white" style="margin-top: 80px; margin-bottom: 80px; max-height: calc(100vh - 160px); overflow-y: auto; width: 600px; max-width: 90%; padding: 32px;">
            <div>
                <h3 style="font-size: 20px; font-weight: 600; color: #111827; margin-bottom: 24px;">
                    {{ __('events.assign_users_modal_title') }} - <span id="modalTitle"></span>
                </h3>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">{{ __('events.select_participants_2') }}</label>
                    <div id="participantsList" style="max-height: 240px; overflow-y: auto; padding: 12px; border: 1px solid #D1D5DB; border-radius: 6px;">
                        <!-- User checkboxes will be inserted here -->
                    </div>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">{{ __('events.select_leader_optional') }}</label>
                    <div id="leadersList" style="max-height: 160px; overflow-y: auto; padding: 12px; border: 1px solid #D1D5DB; border-radius: 6px;">
                        <!-- Leader radio buttons will be inserted here -->
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; width: 100%;">
                    <button type="button" onclick="closeAssignmentModal()" style="display: inline-block; padding: 8px 16px; background-color: #E5E7EB; color: #374151; border-radius: 6px; border: 1px solid #D1D5DB; cursor: pointer; font-size: 14px; font-weight: 500; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#D1D5DB'" onmouseout="this.style.backgroundColor='#E5E7EB'">
                        {{ __('events.cancel') }}
                    </button>
                    <button type="button" onclick="saveAssignment()" style="display: inline-block; padding: 8px 16px; background-color: #4F46E5; color: white; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#4338CA'" onmouseout="this.style.backgroundColor='#4F46E5'">
                        {{ __('events.save') }}
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
            const slotId{{ $assignment->event_slot_id }} = '{{ $assignment->event_slot_id }}';
            if (!assignments.has(slotId{{ $assignment->event_slot_id }})) {
                assignments.set(slotId{{ $assignment->event_slot_id }}, { participants: [], leader: null });
            }
            if ('{{ $assignment->role }}' === 'leader') {
                assignments.get(slotId{{ $assignment->event_slot_id }}).leader = {{ $assignment->user_id }};
            } else {
                assignments.get(slotId{{ $assignment->event_slot_id }}).participants.push({{ $assignment->user_id }});
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

            console.log('Cell clicked:', cell);
            console.log('Slot ID:', currentSlotId);
            console.log('Time:', time);
            console.log('Location:', location);

            if (!currentSlotId || currentSlotId === 'null' || currentSlotId === 'undefined') {
                alert('{{ __('events.invalid_slot') }}');
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
                div.style.cssText = 'display: flex; align-items: center; padding: 8px 0;';
                div.innerHTML = `
                    <input type="checkbox" id="participant-${user.id}" value="${user.id}"
                        ${currentAssignment.participants.includes(user.id) ? 'checked' : ''}
                        class="participant-checkbox"
                        style="margin-right: 8px; width: 16px; height: 16px; cursor: pointer;">
                    <label for="participant-${user.id}" style="cursor: pointer; flex: 1;">${user.name}</label>
                `;
                participantsList.appendChild(div);
            });

            // Populate leaders list
            const leadersList = document.getElementById('leadersList');
            leadersList.innerHTML = '';

            // Add "None" option
            const noneDiv = document.createElement('div');
            noneDiv.style.cssText = 'display: flex; align-items: center; padding: 8px 0;';
            noneDiv.innerHTML = `
                <input type="radio" name="leader" value="" id="leader-none"
                    ${!currentAssignment.leader ? 'checked' : ''}
                    style="margin-right: 8px; width: 16px; height: 16px; cursor: pointer;">
                <label for="leader-none" style="cursor: pointer; flex: 1;">なし</label>
            `;
            leadersList.appendChild(noneDiv);

            // Add user options
            availableUsers.forEach(user => {
                const div = document.createElement('div');
                div.style.cssText = 'display: flex; align-items: center; padding: 8px 0;';
                div.innerHTML = `
                    <input type="radio" name="leader" value="${user.id}" id="leader-${user.id}"
                        ${currentAssignment.leader === user.id ? 'checked' : ''}
                        style="margin-right: 8px; width: 16px; height: 16px; cursor: pointer;">
                    <label for="leader-${user.id}" style="cursor: pointer; flex: 1;">${user.name}</label>
                `;
                leadersList.appendChild(div);
            });

            const modal = document.getElementById('assignmentModal');
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }

        function closeAssignmentModal() {
            const modal = document.getElementById('assignmentModal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
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
                cell.innerHTML = '<span style="color: #9CA3AF; font-size: 14px;">{{ __('events.click_to_assign') }}</span>';
                return;
            }

            let html = '<div style="display: flex; flex-direction: column; gap: 8px;">';

            // Display participants
            if (assignment.participants.length > 0) {
                html += '<div>';
                assignment.participants.forEach(userId => {
                    const user = availableUsers.find(u => u.id === userId);
                    if (user) {
                        html += `<span style="display: inline-block; padding: 4px 8px; background-color: #D1FAE5; color: #065F46; border-radius: 4px; font-size: 12px; margin-right: 4px; margin-bottom: 4px; font-weight: 500;">${user.name}</span>`;
                    }
                });
                html += '</div>';
            }

            // Display leader
            if (assignment.leader) {
                const leader = availableUsers.find(u => u.id === assignment.leader);
                if (leader) {
                    html += '<div>';
                    html += `<span style="display: inline-block; padding: 4px 8px; background-color: #E0E7FF; color: #3730A3; border-radius: 4px; font-size: 12px; font-weight: 600;">👑 ${leader.name}</span>`;
                    html += '</div>';
                }
            }

            html += '</div>';
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
