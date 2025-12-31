<?php

namespace App\Http\Requests\Admin;

use App\Enums\ApplicationSlotDuration;
use App\Enums\AssignmentSlotDuration;
use App\Enums\EventStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => ['required', Rule::enum(AssignmentSlotDuration::class)],
            'application_slot_duration' => ['required', Rule::enum(ApplicationSlotDuration::class)],
            'location' => 'nullable|string|max:255',
            'locations' => 'nullable|array|max:3',
            'locations.*' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => ['required', Rule::enum(EventStatus::class)],
            'is_template' => 'boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->slot_duration !== null && $this->slot_duration !== '') {
            $merge['slot_duration'] = (int) $this->slot_duration;
        }

        if ($this->application_slot_duration !== null && $this->application_slot_duration !== '') {
            $merge['application_slot_duration'] = (int) $this->application_slot_duration;
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }
}
