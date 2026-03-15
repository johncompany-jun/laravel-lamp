<?php

namespace App\Domain\Event\Repositories;

use App\Models\Event;
use App\Models\EventAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface EventAssignmentRepositoryInterface
{
    /**
     * イベント・ユーザーのアサインメントが存在するか確認
     */
    public function existsByEventAndUser(int $eventId, int $userId): bool;

    /**
     * イベントのアサインメントをすべて取得（user・slot リレーション付き）
     *
     * @return Collection<int, EventAssignment>
     */
    public function getByEvent(Event $event): Collection;

    /**
     * イベントの特殊ロールアサインメントを取得（user リレーション付き）
     *
     * @return Collection<int, EventAssignment>
     */
    public function getSpecialByEvent(Event $event): Collection;

    /**
     * アサインメントを作成
     */
    public function create(array $data): EventAssignment;

    /**
     * イベントのアサインメントをすべて削除
     */
    public function deleteByEvent(Event $event): void;

    /**
     * ダッシュボード用：ユーザーの今後のアサインメントを取得
     *
     * @param EventStatus[] $statuses
     * @return Collection<int, \App\Models\EventAssignment>
     */
    public function getUpcomingForUser(User $user, array $statuses, int $limit): Collection;
}
