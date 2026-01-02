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
    });
</script>
