<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Eloquent Event Repository Implementation
 *
 * EventRepositoryInterfaceのEloquent実装
 * インフラストラクチャ層（Eloquentに依存）
 */
class EloquentEventRepository implements EventRepositoryInterface
{
    /**
     * IDでイベントを検索
     */
    public function findById(int $id): ?Event
    {
        return Event::find($id);
    }

    /**
     * イベントを保存
     */
    public function save(Event $event): Event
    {
        $event->save();
        return $event;
    }

    /**
     * イベントを削除
     */
    public function delete(Event $event): void
    {
        $event->delete();
    }

    /**
     * 募集中のイベントを取得（ページネーション）
     * 子イベント（繰り返しイベント）も含む、テンプレートは除外
     */
    public function getOpenEvents(int $perPage = 10): LengthAwarePaginator
    {
        return Event::where('status', EventStatus::OPEN)
            ->where('is_template', false)
            ->where('event_date', '>=', today())
            ->orderBy('event_date')
            ->paginate($perPage);
    }

    /**
     * イベントをリレーション付きで取得
     */
    public function findWithApplicationSlots(Event $event): Event
    {
        return $event->load(['applicationSlots.applications']);
    }

    /**
     * イベントを完全な詳細情報付きで取得
     */
    public function findWithFullDetails(Event $event): Event
    {
        return $event->load([
            'applicationSlots.applications.user',
            'slots.assignments.user',
        ]);
    }

    /**
     * イベントをスロットと申込情報付きで取得
     */
    public function findWithSlotsAndApplications(Event $event): Event
    {
        return $event->load([
            'slots',
            'applicationSlots.applications.user',
        ]);
    }

    /**
     * テンプレートイベントを取得
     */
    public function getTemplateEvents(?int $excludeId = null): Collection
    {
        $query = Event::where('is_template', true);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->orderBy('title')->get();
    }
}
