<?php

namespace App\UseCases;

use App\Domain\Event\Repositories\EventApplicationRepositoryInterface;
use App\Domain\Event\Repositories\EventAssignmentRepositoryInterface;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;

/**
 * イベント詳細ページの閲覧権限を確認するユースケース
 *
 * 閲覧できる条件:
 *   - イベントが OPEN 状態、または
 *   - ユーザーがすでに申し込み済み、または
 *   - ユーザーがアサイン済み
 *
 * 閲覧できない条件:
 *   - イベントが COMPLETED 状態
 */
final class CheckEventViewPermissionUseCase
{
    public function __construct(
        private readonly EventApplicationRepositoryInterface $applicationRepository,
        private readonly EventAssignmentRepositoryInterface  $assignmentRepository,
    ) {}

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException ステータス 404
     */
    public function execute(Event $event, User $user): void
    {
        if ($event->status === EventStatus::COMPLETED) {
            abort(404);
        }

        if ($event->status === EventStatus::OPEN) {
            return;
        }

        if ($this->applicationRepository->existsByEventAndUser($event->id, $user->id)) {
            return;
        }

        if (!$this->assignmentRepository->existsByEventAndUser($event->id, $user->id)) {
            abort(404);
        }
    }
}
