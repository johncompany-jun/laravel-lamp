<?php

namespace App\Domain\Event\Services;

use App\Domain\Event\ValueObjects\VolunteerCapabilities;
use App\Enums\SlotAvailability;
use InvalidArgumentException;

/**
 * イベント申込に関するポリシー（ビジネスルール）のドメインサービス
 *
 * 「申込に関してどんなルールが適用されるか」という純粋なビジネスロジックを担当。
 * DB アクセスは含まない。
 *
 * 既存の EventApplicationService に混在していた検証ロジックをここに集約し、
 * EventApplicationService はインフラ操作（DB の読み書き）に専念させる。
 */
final class ApplicationPolicyService
{
    /**
     * フォームから送られたスロットデータを検証・フィルタリングして返す
     *
     * 'available' または 'unavailable' のみ受け付け、それ以外は除外する。
     *
     * @param  array<mixed, mixed> $rawSlots  リクエストから受け取った生のスロットデータ
     * @return array<int, array{slot_id: int, availability: string}>
     *
     * @throws InvalidArgumentException スロットが1件も有効でない場合
     */
    public function validateAndFilterSlots(array $rawSlots): array
    {
        $validSlots = [];

        foreach ($rawSlots as $slotData) {
            if (!isset($slotData['slot_id'], $slotData['availability'])) {
                continue;
            }

            $availability = SlotAvailability::tryFrom($slotData['availability']);

            if ($availability === null) {
                continue;
            }

            $validSlots[] = [
                'slot_id'      => (int) $slotData['slot_id'],
                'availability' => $availability->value,
            ];
        }

        if (empty($validSlots)) {
            throw new InvalidArgumentException('少なくとも1つの時間帯を選択してください。');
        }

        return $validSlots;
    }

    /**
     * キャンセル後に残りの申込に適用すべきCapabilitiesを計算する
     *
     * ルール:
     * - 最初のスロットをキャンセル → can_help_setup を false に
     * - 最後のスロットをキャンセル → can_help_cleanup を false に
     */
    public function capabilitiesAfterCancellation(
        VolunteerCapabilities $original,
        bool                  $wasCancellingFirstSlot,
        bool                  $wasCancellingLastSlot,
    ): VolunteerCapabilities {
        $updated = $original;

        if ($wasCancellingFirstSlot && $original->canHelpSetup()) {
            $updated = $updated->withoutSetup();
        }

        if ($wasCancellingLastSlot && $original->canHelpCleanup()) {
            $updated = $updated->withoutCleanup();
        }

        return $updated;
    }

    /**
     * 申込が本人のものであるか確認する
     *
     * @throws \DomainException 申込が本人のものでない場合
     */
    public function assertOwnedBy(int $applicationUserId, int $currentUserId): void
    {
        if ($applicationUserId !== $currentUserId) {
            throw new \DomainException('この申込を操作する権限がありません。');
        }
    }
}
