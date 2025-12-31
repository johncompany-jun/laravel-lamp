<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventAssignmentsRequest extends FormRequest
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
            'assignments' => 'required|array',
            'assignments.*.slot_id' => 'required|exists:event_slots,id',
            'assignments.*.user_id' => 'required|exists:users,id',
            'assignments.*.role' => 'required|in:participant,leader',
        ];
    }
}
