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
            'assignments.*.slot_id' => 'nullable|exists:event_slots,id',
            'assignments.*.user_id' => 'required|exists:users,id',
            'assignments.*.role' => 'required|in:participant,leader',
            'assignments.*.special_role' => 'nullable|in:setup,cleanup,transport_first,transport_second',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $assignments = $this->input('assignments', []);

            // Separate regular and special assignments
            $regularAssignments = [];
            $specialAssignments = [];
            foreach ($assignments as $assignment) {
                if (!empty($assignment['special_role'])) {
                    $specialAssignments[] = $assignment;
                } else {
                    $regularAssignments[] = $assignment;
                }
            }

            // スロットごとの割り当て人数を集計（通常のアサインのみ）
            $slotCounts = [];
            foreach ($regularAssignments as $assignment) {
                $slotId = $assignment['slot_id'] ?? null;
                if (!$slotId) continue;

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
            foreach ($regularAssignments as $assignment) {
                $slotId = $assignment['slot_id'] ?? null;
                if (!$slotId) continue;

                $slot = EventSlot::find($slotId);
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

            // 特殊役割の定員チェック
            $specialRoleCounts = [];
            foreach ($specialAssignments as $assignment) {
                $role = $assignment['special_role'];
                if (!isset($specialRoleCounts[$role])) {
                    $specialRoleCounts[$role] = 0;
                }
                $specialRoleCounts[$role]++;
            }

            $specialRoleLimits = [
                'setup' => 2,
                'cleanup' => 2,
                'transport_first' => 1,
                'transport_second' => 1,
            ];

            $roleNames = [
                'setup' => '準備',
                'cleanup' => '片付け',
                'transport_first' => '車運搬前半',
                'transport_second' => '車運搬後半',
            ];

            foreach ($specialRoleCounts as $role => $count) {
                $limit = $specialRoleLimits[$role] ?? 0;
                if ($count > $limit) {
                    $validator->errors()->add(
                        'assignments',
                        "「{$roleNames[$role]}」の定員は{$limit}人ですが、{$count}人が割り当てられています。"
                    );
                }
            }
        });
    }
}
