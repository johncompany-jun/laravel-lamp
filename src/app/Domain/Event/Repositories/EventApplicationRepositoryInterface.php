<?php

namespace App\Domain\Event\Repositories;

use App\Models\Event;
use App\Models\EventApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface EventApplicationRepositoryInterface
{
    /**
     * イベント・ユーザーの申込が存在するか確認
     */
    public function existsByEventAndUser(int $eventId, int $userId): bool;

    /**
     * イベントの申込をユーザーIDでグループ化して取得（管理者用）
     *
     * @return \Illuminate\Support\Collection<int, \Illuminate\Support\Collection<int, EventApplication>>
     */
    public function getByEventGroupedByUser(Event $event): \Illuminate\Support\Collection;

    /**
     * ユーザーのイベント申込を取得（スロットIDをキーにした Collection）
     *
     * @return Collection<int, EventApplication>
     */
    public function getByUserAndEvent(User $user, Event $event): Collection;

    /**
     * ユーザーのイベント申込を時刻順で取得
     *
     * @return Collection<int, EventApplication>
     */
    public function getSortedByTimeForUserAndEvent(User $user, Event $event): Collection;

    /**
     * 申込を保存
     */
    public function save(EventApplication $application): EventApplication;

    /**
     * ユーザーのイベント申込をすべて削除
     */
    public function deleteByUserAndEvent(User $user, Event $event): void;

    /**
     * ユーザーのイベント申込の Capabilities をまとめて更新
     *
     * @param array<string, bool> $data  例: ['can_help_setup' => false]
     */
    public function updateCapabilitiesByUserAndEvent(int $eventId, int $userId, array $data): void;

    /**
     * ダッシュボード用：ユーザーの申込をイベントごとにグループ化して取得
     *
     * @return \Illuminate\Support\Collection<int, array{event: \App\Models\Event, applications: \Illuminate\Support\Collection, applied_at: \Carbon\Carbon}>
     */
    public function getDashboardApplicationsForUser(User $user, int $limit): \Illuminate\Support\Collection;
}
