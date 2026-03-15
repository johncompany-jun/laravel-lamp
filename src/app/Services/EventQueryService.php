<?php

namespace App\Services;

use App\Domain\Event\Repositories\EventApplicationRepositoryInterface;
use App\Domain\Event\Repositories\EventAssignmentRepositoryInterface;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * 読み取り専用クエリの調整役
 *
 * すべての読み取りを Repository 経由で行い、
 * 自身は DB に直接アクセスしない。
 */
class EventQueryService
{
    public function __construct(
        private readonly EventRepositoryInterface            $eventRepository,
        private readonly EventApplicationRepositoryInterface $applicationRepository,
        private readonly EventAssignmentRepositoryInterface  $assignmentRepository,
    ) {}

    public function getOpenEvents(int $perPage = 10): LengthAwarePaginator
    {
        return $this->eventRepository->getOpenEvents($perPage);
    }

    public function getParentEvents(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        return $this->eventRepository->getAdminEventsList($filters, $perPage);
    }

    public function getEventWithApplicationSlots(Event $event): Event
    {
        return $this->eventRepository->findWithApplicationSlots($event);
    }

    public function getEventWithFullDetails(Event $event): Event
    {
        return $this->eventRepository->findWithFullDetails($event)->load('childEvents');
    }

    public function getEventWithSlotsAndApplications(Event $event): Event
    {
        return $this->eventRepository->findWithSlotsAndApplications($event);
    }

    public function getTemplateEvents(?int $excludeId = null): Collection
    {
        return $this->eventRepository->getTemplateEvents($excludeId);
    }

    public function getApplicationsGroupedByUser(Event $event): \Illuminate\Support\Collection
    {
        return $this->applicationRepository->getByEventGroupedByUser($event);
    }

    public function getExistingAssignments(Event $event): Collection
    {
        return $this->assignmentRepository->getByEvent($event);
    }

    public function getExistingSpecialAssignments(Event $event): Collection
    {
        return $this->assignmentRepository->getSpecialByEvent($event);
    }
}
