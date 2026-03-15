<script>
    // Template auto-fill
    document.getElementById('template_id')?.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            document.getElementById('title').value = option.dataset.title || '';

            // Only update time fields if they're not disabled
            const startTimeField = document.getElementById('start_time');
            const endTimeField = document.getElementById('end_time');
            const slotDurationField = document.querySelector('select[name="slot_duration"]');
            const applicationSlotDurationField = document.querySelector('select[name="application_slot_duration"]');

            if (!startTimeField.disabled) {
                startTimeField.value = option.dataset.startTime?.substring(0, 5) || '';
            }
            if (!endTimeField.disabled) {
                endTimeField.value = option.dataset.endTime?.substring(0, 5) || '';
            }
            if (!slotDurationField.disabled) {
                slotDurationField.value = option.dataset.slotDuration || '';
            }
            if (!applicationSlotDurationField.disabled) {
                applicationSlotDurationField.value = option.dataset.applicationSlotDuration || '';
            }

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

        const appDuration    = parseInt(document.querySelector('select[name="application_slot_duration"]')?.value || '0');
        const assignDuration = parseInt(document.querySelector('select[name="slot_duration"]')?.value || '0');

        const appWarning    = document.getElementById('app_slot_warning');
        const assignWarning = document.getElementById('assign_slot_warning');

        if (appWarning && appDuration > 0) {
            appWarning.classList.toggle('hidden', totalMinutes % appDuration === 0);
        }
        if (assignWarning && assignDuration > 0) {
            assignWarning.classList.toggle('hidden', totalMinutes % assignDuration === 0);
        }
    }

    ['start_time', 'end_time'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', checkSlotWarnings);
    });
    ['application_slot_duration', 'slot_duration'].forEach(name => {
        document.querySelector(`select[name="${name}"]`)?.addEventListener('change', checkSlotWarnings);
    });

    checkSlotWarnings();
</script>
