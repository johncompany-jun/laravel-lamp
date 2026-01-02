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
    });
</script>
