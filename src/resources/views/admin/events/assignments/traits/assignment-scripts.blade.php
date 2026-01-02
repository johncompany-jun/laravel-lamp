<script>
    // Store assignments: { slotId: { participants: [userId1, userId2], leader: userId3 } }
    const assignments = new Map();
    let currentSlotId = null;
    let currentCell = null;
    let currentSlotCapacity = null;

    // Available users data
    const availableUsers = @json($availableUsers);

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
        currentCell = cell;
        currentSlotId = cell.dataset.slotId;
        const time = cell.dataset.time;
        const location = cell.dataset.location;

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
            let badges = '';

            // Add setup/cleanup badges
            if (user.can_help_setup) {
                badges += ' <span style="font-size: 10px; padding: 2px 6px; background-color: #DBEAFE; color: #1E40AF; border-radius: 3px; margin-left: 4px; font-weight: 600;">準備</span>';
            }
            if (user.can_help_cleanup) {
                badges += ' <span style="font-size: 10px; padding: 2px 6px; background-color: #FEF3C7; color: #92400E; border-radius: 3px; margin-left: 4px; font-weight: 600;">片付</span>';
            }
            if (user.can_transport_by_car) {
                badges += ' <span style="font-size: 10px; padding: 2px 6px; background-color: #E9D5FF; color: #6B21A8; border-radius: 3px; margin-left: 4px; font-weight: 600;">車運搬</span>';
            }

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
                <label for="participant-${user.id}" style="${labelStyle}">${user.name}${badges}${warningText}</label>
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
            let badges = '';

            // Add setup/cleanup badges
            if (user.can_help_setup) {
                badges += ' <span style="font-size: 10px; padding: 2px 6px; background-color: #DBEAFE; color: #1E40AF; border-radius: 3px; margin-left: 4px; font-weight: 600;">準備</span>';
            }
            if (user.can_help_cleanup) {
                badges += ' <span style="font-size: 10px; padding: 2px 6px; background-color: #FEF3C7; color: #92400E; border-radius: 3px; margin-left: 4px; font-weight: 600;">片付</span>';
            }
            if (user.can_transport_by_car) {
                badges += ' <span style="font-size: 10px; padding: 2px 6px; background-color: #E9D5FF; color: #6B21A8; border-radius: 3px; margin-left: 4px; font-weight: 600;">車運搬</span>';
            }

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
                <label for="leader-${user.id}" style="${labelStyle}">${user.name}${badges}${warningText}</label>
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
