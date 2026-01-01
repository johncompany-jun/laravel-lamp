<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('events.create_assignments_title') }}: {{ $event->title }}
            </h2>
            <a href="{{ route('admin.events.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('events.back_to_list') }}
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

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Event Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('events.event_details') }}</h3>
                    <div class="grid grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('events.date') }}</p>
                            <p class="font-medium">{{ $event->event_date->format('Y-m-d (D)') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('events.time') }}</p>
                            <p class="font-medium">{{ date('H:i', strtotime($event->start_time)) }} - {{ date('H:i', strtotime($event->end_time)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">{{ __('events.location') }}</p>
                            <p class="font-medium">{{ $event->location ?? __('events.not_set') }}</p>
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
                        <div id="assignmentInputs">
                            <!-- This ensures assignments array is always sent, even if empty -->
                            <input type="hidden" name="assignments" value="">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Modal -->
    <div id="assignmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50" style="display: none;" onclick="event.target === this && closeAssignmentModal()">
        <div class="relative mx-auto border shadow-lg rounded-md bg-white" style="margin-top: 80px; margin-bottom: 80px; max-height: calc(100vh - 160px); overflow-y: auto; width: 600px; max-width: 90%; padding: 32px;">
            <div>
                <h3 style="font-size: 20px; font-weight: 600; color: #111827; margin-bottom: 16px;">
                    {{ __('events.assign_users_modal_title') }} - <span id="modalTitle"></span>
                </h3>

                <!-- Capacity indicator -->
                <div id="capacityIndicator" style="margin-bottom: 16px; padding: 12px; border-radius: 6px; font-size: 14px;">
                    <!-- Capacity info will be inserted here -->
                </div>

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
        console.log('Script loaded');

        // Store assignments: { slotId: { participants: [userId1, userId2], leader: userId3 } }
        const assignments = new Map();
        let currentSlotId = null;
        let currentCell = null;
        let currentSlotCapacity = null;

        // Available users data
        const availableUsers = @json($availableUsers);
        console.log('Available users:', availableUsers);

        // Slots data with capacity
        const slotsData = new Map();
        @foreach($event->slots as $slot)
            slotsData.set('{{ $slot->id }}', {
                id: '{{ $slot->id }}',
                capacity: {{ $slot->capacity }},
                startTime: '{{ date('H:i', strtotime($slot->start_time)) }}',
                endTime: '{{ date('H:i', strtotime($slot->end_time)) }}',
                location: '{{ $slot->location }}'
            });
        @endforeach

        // Load existing assignments
        @foreach($existingAssignments as $assignment)
            @php
                $slotId = $assignment->event_slot_id;
                $userId = $assignment->user_id;
                $role = $assignment->role;
            @endphp
            if (!assignments.has('{{ $slotId }}')) {
                assignments.set('{{ $slotId }}', { participants: [], leader: null });
            }
            if ('{{ $role }}' === 'leader') {
                assignments.get('{{ $slotId }}').leader = {{ $userId }};
            } else {
                assignments.get('{{ $slotId }}').participants.push({{ $userId }});
            }
        @endforeach

        // Update display for existing assignments
        assignments.forEach((data, slotId) => {
            updateCellDisplay(slotId);
        });

        // 指定した時間帯に他の場所でアサインされているユーザーを取得
        function getUsersAssignedAtTime(timeKey, excludeSlotId) {
            const usersAssigned = new Map(); // userId -> location

            assignments.forEach((assignment, slotId) => {
                if (slotId === excludeSlotId) return; // 現在編集中のスロットは除外

                const slot = slotsData.get(slotId);
                if (!slot) return;

                const slotTimeKey = slot.startTime + '-' + slot.endTime;
                if (slotTimeKey !== timeKey) return; // 違う時間帯はスキップ

                // 参加者を追加
                assignment.participants.forEach(userId => {
                    usersAssigned.set(userId, slot.location);
                });

                // 見守りを追加
                if (assignment.leader) {
                    usersAssigned.set(assignment.leader, slot.location);
                }
            });

            return usersAssigned;
        }

        function openAssignmentModal(cell) {
            console.log('openAssignmentModal called');
            console.log('cell:', cell);

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

            // Get slot data
            const slotData = slotsData.get(currentSlotId);
            currentSlotCapacity = slotData ? slotData.capacity : 0;
            const currentTimeKey = slotData.startTime + '-' + slotData.endTime;

            document.getElementById('modalTitle').textContent = `${time} - ${location}`;

            // Get current assignments for this slot
            const currentAssignment = assignments.get(currentSlotId) || { participants: [], leader: null };

            // 同じ時間帯の他の場所にアサインされているユーザーを取得
            const usersAssignedAtSameTime = getUsersAssignedAtTime(currentTimeKey, currentSlotId);

            // Update capacity indicator
            updateCapacityIndicator(currentAssignment);

            // Populate participants list
            const participantsList = document.getElementById('participantsList');
            participantsList.innerHTML = '';
            availableUsers.forEach(user => {
                const isAssignedElsewhere = usersAssignedAtSameTime.has(user.id);
                const isCurrentlyAssigned = currentAssignment.participants.includes(user.id);
                const isDisabled = isAssignedElsewhere && !isCurrentlyAssigned;

                const div = document.createElement('div');
                div.style.cssText = 'display: flex; align-items: center; padding: 8px 0;';

                let labelStyle = 'cursor: pointer; flex: 1;';
                let warningText = '';

                if (isDisabled) {
                    labelStyle += ' color: #9CA3AF;';
                    const assignedLocation = usersAssignedAtSameTime.get(user.id);
                    warningText = ` <span style="font-size: 11px; color: #EF4444;">(${assignedLocation}にアサイン済み)</span>`;
                }

                div.innerHTML = `
                    <input type="checkbox" id="participant-${user.id}" value="${user.id}"
                        ${isCurrentlyAssigned ? 'checked' : ''}
                        ${isDisabled ? 'disabled' : ''}
                        class="participant-checkbox"
                        style="margin-right: 8px; width: 16px; height: 16px; cursor: ${isDisabled ? 'not-allowed' : 'pointer'};">
                    <label for="participant-${user.id}" style="${labelStyle}">${user.name}${warningText}</label>
                `;
                participantsList.appendChild(div);
            });

            // Add event listeners to checkboxes
            document.querySelectorAll('.participant-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    const tempAssignment = getCurrentModalAssignment();
                    updateCapacityIndicator(tempAssignment);
                });
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
                    class="leader-radio"
                    style="margin-right: 8px; width: 16px; height: 16px; cursor: pointer;">
                <label for="leader-none" style="cursor: pointer; flex: 1;">なし</label>
            `;
            leadersList.appendChild(noneDiv);

            // Add user options
            availableUsers.forEach(user => {
                const isAssignedElsewhere = usersAssignedAtSameTime.has(user.id);
                const isCurrentlyAssigned = currentAssignment.leader === user.id;
                const isDisabled = isAssignedElsewhere && !isCurrentlyAssigned;

                const div = document.createElement('div');
                div.style.cssText = 'display: flex; align-items: center; padding: 8px 0;';

                let labelStyle = 'cursor: pointer; flex: 1;';
                let warningText = '';

                if (isDisabled) {
                    labelStyle += ' color: #9CA3AF;';
                    const assignedLocation = usersAssignedAtSameTime.get(user.id);
                    warningText = ` <span style="font-size: 11px; color: #EF4444;">(${assignedLocation}にアサイン済み)</span>`;
                }

                div.innerHTML = `
                    <input type="radio" name="leader" value="${user.id}" id="leader-${user.id}"
                        ${isCurrentlyAssigned ? 'checked' : ''}
                        ${isDisabled ? 'disabled' : ''}
                        class="leader-radio"
                        style="margin-right: 8px; width: 16px; height: 16px; cursor: ${isDisabled ? 'not-allowed' : 'pointer'};">
                    <label for="leader-${user.id}" style="${labelStyle}">${user.name}${warningText}</label>
                `;
                leadersList.appendChild(div);
            });

            // Add event listeners to radio buttons
            document.querySelectorAll('.leader-radio').forEach(radio => {
                radio.addEventListener('change', () => {
                    const tempAssignment = getCurrentModalAssignment();
                    updateCapacityIndicator(tempAssignment);
                });
            });

            const modal = document.getElementById('assignmentModal');
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        }

        function getCurrentModalAssignment() {
            const participantCheckboxes = document.querySelectorAll('.participant-checkbox:checked');
            const participants = Array.from(participantCheckboxes).map(cb => parseInt(cb.value));

            const leaderRadio = document.querySelector('input[name="leader"]:checked');
            const leader = leaderRadio && leaderRadio.value ? parseInt(leaderRadio.value) : null;

            return { participants, leader };
        }

        function updateCapacityIndicator(assignment) {
            const totalAssigned = assignment.participants.length + (assignment.leader ? 1 : 0);
            const indicator = document.getElementById('capacityIndicator');

            if (totalAssigned > currentSlotCapacity) {
                indicator.style.backgroundColor = '#FEE2E2';
                indicator.style.border = '2px solid #DC2626';
                indicator.style.color = '#991B1B';
                indicator.innerHTML = `⚠️ 定員オーバー: ${totalAssigned}人 / ${currentSlotCapacity}人（${totalAssigned - currentSlotCapacity}人超過）`;
            } else if (totalAssigned === currentSlotCapacity) {
                indicator.style.backgroundColor = '#FEF3C7';
                indicator.style.border = '2px solid #F59E0B';
                indicator.style.color = '#92400E';
                indicator.innerHTML = `✓ 定員ちょうど: ${totalAssigned}人 / ${currentSlotCapacity}人`;
            } else {
                indicator.style.backgroundColor = '#D1FAE5';
                indicator.style.border = '2px solid #10B981';
                indicator.style.color = '#065F46';
                indicator.innerHTML = `✓ 定員内: ${totalAssigned}人 / ${currentSlotCapacity}人（残り${currentSlotCapacity - totalAssigned}人）`;
            }
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
                    html += `<span style="display: inline-block; padding: 4px 8px; background-color: #E0E7FF; color: #3730A3; border-radius: 4px; font-size: 12px; font-weight: 600;">👀 ${leader.name}</span>`;
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
