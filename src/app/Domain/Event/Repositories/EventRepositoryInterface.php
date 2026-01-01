<?php

namespace App\Domain\Event\Repositories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Event Repository Interface
 *
 * ドメイン層のリポジトリインターフェース
 * インフラストラクチャ層の実装詳細に依存しない
 */
interface EventRepositoryInterface
{
    /**
     * IDでイベントを検索
     */
    public function findById(int $id): ?Event;

    /**
     * イベントを保存
     */
    public function save(Event $event): Event;

    /**
     * イベントを削除
     */
    public function delete(Event $event): void;

    /**
     * 募集中のイベントを取得（ページネーション）
     */
    public function getOpenEvents(int $perPage = 10): LengthAwarePaginator;

    /**
     * イベントをリレーション付きで取得
     */
    public function findWithApplicationSlots(Event $event): Event;

    /**
     * イベントを完全な詳細情報付きで取得
     */
    public function findWithFullDetails(Event $event): Event;

    /**
     * イベントをスロットと申込情報付きで取得
     */
    public function findWithSlotsAndApplications(Event $event): Event;

    /**
     * テンプレートイベントを取得
     */
    public function getTemplateEvents(?int $excludeId = null): Collection;
}
