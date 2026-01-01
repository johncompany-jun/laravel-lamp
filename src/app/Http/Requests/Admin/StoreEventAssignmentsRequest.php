<?php

namespace App\Http\Requests\Admin;

use App\Models\EventSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'assignments' => 'nullable|array',
            'assignments.*.slot_id' => 'required|exists:event_slots,id',
            'assignments.*.user_id' => 'required|exists:users,id',
            'assignments.*.role' => 'required|in:participant,leader',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $assignments = $this->input('assignments', []);

            // スロットごとの割り当て人数を集計
            $slotCounts = [];
            foreach ($assignments as $assignment) {
                $slotId = $assignment['slot_id'];
                if (!isset($slotCounts[$slotId])) {
                    $slotCounts[$slotId] = 0;
                }
                $slotCounts[$slotId]++;
            }

            // 各スロットの定員をチェック
            foreach ($slotCounts as $slotId => $count) {
                $slot = EventSlot::find($slotId);
                if ($slot && $count > $slot->capacity) {
                    $validator->errors()->add(
                        'assignments',
                        "スロット「{$slot->start_time} - {$slot->end_time} ({$slot->location})」の定員は{$slot->capacity}人ですが、{$count}人が割り当てられています。"
                    );
                }
            }

            // 同じ時間帯に同じユーザーが複数の場所にアサインされていないかチェック
            $userTimeSlots = [];
            foreach ($assignments as $assignment) {
                $slot = EventSlot::find($assignment['slot_id']);
                if (!$slot) continue;

                $userId = $assignment['user_id'];
                $timeKey = $slot->start_time . '-' . $slot->end_time;

                if (!isset($userTimeSlots[$userId])) {
                    $userTimeSlots[$userId] = [];
                }

                if (isset($userTimeSlots[$userId][$timeKey])) {
                    // 既に同じ時間帯にアサインされている
                    $previousSlot = $userTimeSlots[$userId][$timeKey];
                    $user = \App\Models\User::find($userId);
                    $validator->errors()->add(
                        'assignments',
                        "{$user->name}さんは、{$slot->start_time} - {$slot->end_time}の時間帯に既に「{$previousSlot->location}」にアサインされています。同じ時間帯に複数の場所にアサインすることはできません。"
                    );
                } else {
                    $userTimeSlots[$userId][$timeKey] = $slot;
                }
            }
        });
    }
}
