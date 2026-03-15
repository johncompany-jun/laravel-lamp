<script>
    // Toggle recurrence fields
    document.getElementById('is_recurring').addEventListener('change', function() {
        const recurrenceFields = document.getElementById('recurrence_fields');
        recurrenceFields.style.display = this.checked ? 'block' : 'none';
    });

    // Show recurrence fields if already checked (validation errors)
    if (document.getElementById('is_recurring').checked) {
        document.getElementById('recurrence_fields').style.display = 'block';
    }

    // Template auto-fill
    document.getElementById('template_id')?.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            document.getElementById('title').value = option.dataset.title || '';
            document.getElementById('start_time').value = option.dataset.startTime?.substring(0, 5) || '';
            document.getElementById('end_time').value = option.dataset.endTime?.substring(0, 5) || '';
            document.getElementById('slot_duration').value = option.dataset.slotDuration || '';
            document.getElementById('location').value = option.dataset.location || '';
            document.getElementById('notes').value = option.dataset.notes || '';
        }
        checkSlotWarnings();
    });

    // Slot duration remainder warning
    function checkSlotWarnings() {
        const startVal = document.getElementById('start_time').value;
        const endVal   = document.getElementById('end_time').value;
        if (!startVal || !endVal) return;

        const [sh, sm] = startVal.split(':').map(Number);
        const [eh, em] = endVal.split(':').map(Number);
        const totalMinutes = (eh * 60 + em) - (sh * 60 + sm);
        if (totalMinutes <= 0) return;

        const appDuration    = parseInt(document.getElementById('application_slot_duration')?.value || '0');
        const assignDuration = parseInt(document.getElementById('slot_duration')?.value || '0');

        const appWarning    = document.getElementById('app_slot_warning');
        const assignWarning = document.getElementById('assign_slot_warning');

        if (appWarning && appDuration > 0) {
            appWarning.classList.toggle('hidden', totalMinutes % appDuration === 0);
        }
        if (assignWarning && assignDuration > 0) {
            assignWarning.classList.toggle('hidden', totalMinutes % assignDuration === 0);
        }
    }

    ['start_time', 'end_time', 'application_slot_duration', 'slot_duration'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', checkSlotWarnings);
    });

    checkSlotWarnings();
</script>
