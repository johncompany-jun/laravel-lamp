<?php

namespace App\Services;

use App\Domain\Event\Repositories\EventApplicationRepositoryInterface;
use App\Domain\Event\Services\ApplicationPolicyService;
use App\Domain\Event\ValueObjects\VolunteerCapabilities;
use App\Models\Event;
use App\Models\EventApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * イベント申込のインフラサービス
 *
 * ビジネスルールは ApplicationPolicyService に委譲し、
 * DB 操作はすべて EventApplicationRepository 経由で行う。
 */
class EventApplicationService
{
    public function __construct(
        private readonly ApplicationPolicyService            $policy,
        private readonly EventApplicationRepositoryInterface $applicationRepository,
    ) {}

    /**
     * リクエストのスロットデータを検証・フィルタリングして返す
     *
     * @throws \InvalidArgumentException
     */
    public function validateSlots(array $slots): array
    {
        return $this->policy->validateAndFilterSlots($slots);
    }

    /**
     * イベントへの申込を登録する（既存申込は上書き）
     */
    public function submitApplication(
        User    $user,
        Event   $event,
        array   $validSlots,
        bool    $canHelpSetup      = false,
        bool    $canHelpCleanup    = false,
        bool    $canTransportByCar = false,
        ?string $comment           = null,
    ): void {
        $this->applicationRepository->deleteByUserAndEvent($user, $event);

        foreach ($validSlots as $slotData) {
            $application = new EventApplication([
                'event_id'                  => $event->id,
                'event_application_slot_id' => $slotData['slot_id'],
                'user_id'                   => $user->id,
                'availability'              => $slotData['availability'],
                'can_help_setup'            => $canHelpSetup,
                'can_help_cleanup'          => $canHelpCleanup,
                'can_transport_by_car'      => $canTransportByCar,
                'comment'                   => $comment,
            ]);
            $this->applicationRepository->save($application);
        }
    }

    /**
     * ユーザーのイベント申込をすべて削除する
     */
    public function deleteUserApplications(User $user, Event $event): void
    {
        $this->applicationRepository->deleteByUserAndEvent($user, $event);
    }

    /**
     * ユーザーのイベント申込を取得する（スロットIDをキーにした Collection）
     */
    public function getUserApplications(User $user, Event $event): Collection
    {
        return $this->applicationRepository
            ->getByUserAndEvent($user, $event)
            ->keyBy('event_application_slot_id');
    }

    /**
     * ユーザーのイベント申込を時刻順で取得する
     */
    public function getUserApplicationsSortedByTime(User $user, Event $event): Collection
    {
        return $this->applicationRepository->getSortedByTimeForUserAndEvent($user, $event);
    }

    /**
     * 申込をキャンセルし、残りの申込のCapabilitiesを更新する
     *
     * @throws \DomainException 申込が本人のものでない場合
     */
    public function cancelApplication(EventApplication $application, User $user): bool
    {
        $this->policy->assertOwnedBy($application->user_id, $user->id);

        $allApplications = $this->getUserApplicationsSortedByTime($user, $application->event);
        $isFirstSlot     = $allApplications->first()->id === $application->id;
        $isLastSlot      = $allApplications->last()->id === $application->id;

        $application->delete();

        if ($allApplications->count() > 1) {
            $this->applyCapabilitiesAfterCancellation($application, $user, $isFirstSlot, $isLastSlot);
        }

        return true;
    }

    private function applyCapabilitiesAfterCancellation(
        EventApplication $cancelled,
        User             $user,
        bool             $wasFirst,
        bool             $wasLast,
    ): void {
        $original = VolunteerCapabilities::fromArray([
            'can_help_setup'       => $cancelled->can_help_setup,
            'can_help_cleanup'     => $cancelled->can_help_cleanup,
            'can_transport_by_car' => $cancelled->can_transport_by_car,
        ]);

        $updated = $this->policy->capabilitiesAfterCancellation($original, $wasFirst, $wasLast);

        if ($updated->canHelpSetup() !== $original->canHelpSetup()) {
            $this->applicationRepository->updateCapabilitiesByUserAndEvent(
                $cancelled->event_id, $user->id, ['can_help_setup' => false]
            );
        }

        if ($updated->canHelpCleanup() !== $original->canHelpCleanup()) {
            $this->applicationRepository->updateCapabilitiesByUserAndEvent(
                $cancelled->event_id, $user->id, ['can_help_cleanup' => false]
            );
        }
    }
}
