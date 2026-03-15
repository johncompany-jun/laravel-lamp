<?php

namespace App\Domain\Event\ValueObjects;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * 繰り返しスケジュールを表すValueObject
 *
 * 「繰り返しあり → recurrence_type と recurrence_end_date が必須」
 * という不変条件を保証する
 */
final class RecurrenceSchedule
{
    public const TYPE_WEEKLY = 'weekly';

    private function __construct(
        private readonly bool    $isRecurring,
        private readonly ?string $recurrenceType,
        private readonly ?Carbon $recurrenceEndDate,
    ) {}

    /** 繰り返しなしのスケジュールを生成 */
    public static function none(): self
    {
        return new self(false, null, null);
    }

    /** 毎週繰り返しのスケジュールを生成 */
    public static function weekly(string $endDate): self
    {
        $end = Carbon::parse($endDate)->startOfDay();

        return new self(true, self::TYPE_WEEKLY, $end);
    }

    /** 配列データから生成（フォームリクエスト等のデータ変換用） */
    public static function fromArray(array $data): self
    {
        $isRecurring = (bool) ($data['is_recurring'] ?? false);

        if (!$isRecurring) {
            return self::none();
        }

        $type    = $data['recurrence_type'] ?? null;
        $endDate = $data['recurrence_end_date'] ?? null;

        if ($type === null) {
            throw new InvalidArgumentException('繰り返しタイプが指定されていません。');
        }

        if ($endDate === null) {
            throw new InvalidArgumentException('繰り返し終了日が指定されていません。');
        }

        if ($type === self::TYPE_WEEKLY) {
            return self::weekly($endDate);
        }

        throw new InvalidArgumentException("サポートされていない繰り返しタイプです: {$type}");
    }

    public function isRecurring(): bool
    {
        return $this->isRecurring;
    }

    public function isWeekly(): bool
    {
        return $this->isRecurring && $this->recurrenceType === self::TYPE_WEEKLY;
    }

    public function recurrenceType(): ?string
    {
        return $this->recurrenceType;
    }

    public function recurrenceEndDate(): ?Carbon
    {
        return $this->recurrenceEndDate?->copy();
    }

    /**
     * 基準日から繰り返しイベントの日付リストを生成（毎週）
     *
     * @param Carbon $baseDate 最初のイベント日
     * @return Carbon[]
     */
    public function generateWeeklyDates(Carbon $baseDate): array
    {
        if (!$this->isWeekly() || $this->recurrenceEndDate === null) {
            return [];
        }

        $dates   = [];
        $current = $baseDate->copy()->addWeek();

        while ($current->lte($this->recurrenceEndDate)) {
            $dates[] = $current->copy();
            $current->addWeek();
        }

        return $dates;
    }
}
