<?php

namespace App\Http\Requests\Admin;

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
            'slot_duration' => 'required|in:10,20,30',
            'application_slot_duration' => 'required|in:30,60,90,120',
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
        $this->merge([
            'slot_duration' => (int) $this->slot_duration,
            'application_slot_duration' => (int) $this->application_slot_duration,
        ]);
    }
}
