<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitEventApplicationRequest extends FormRequest
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
            'slots' => 'required|array',
            'slots.*.slot_id' => 'required|exists:event_application_slots,id',
            'slots.*.availability' => 'required|in:available,unavailable',
            'can_help_setup' => 'nullable|boolean',
            'can_help_cleanup' => 'nullable|boolean',
            'can_transport_by_car' => 'nullable|boolean',
            'comment' => 'nullable|string|max:500',
        ];
    }
}
