<?php

namespace Tests\Unit\Domain\Services;

use App\Domain\Event\Services\ApplicationPolicyService;
use App\Domain\Event\ValueObjects\VolunteerCapabilities;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ApplicationPolicyServiceTest extends TestCase
{
    private ApplicationPolicyService $service;

    protected function setUp(): void
    {
        $this->service = new ApplicationPolicyService();
    }

    // --- validateAndFilterSlots ---

    public function test_有効なスロットを返す(): void
    {
        $raw = [
            ['slot_id' => 1, 'availability' => 'available'],
            ['slot_id' => 2, 'availability' => 'unavailable'],
        ];

        $result = $this->service->validateAndFilterSlots($raw);

        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]['slot_id']);
        $this->assertSame('available', $result[0]['availability']);
    }

    public function test_無効なavailability値はフィルタリングされる(): void
    {
        $raw = [
            ['slot_id' => 1, 'availability' => 'maybe'],
            ['slot_id' => 2, 'availability' => 'available'],
        ];

        $result = $this->service->validateAndFilterSlots($raw);

        $this->assertCount(1, $result);
        $this->assertSame(2, $result[0]['slot_id']);
    }

    public function test_必須キーが欠けているスロットはフィルタリングされる(): void
    {
        $raw = [
            ['slot_id' => 1],
            ['slot_id' => 2, 'availability' => 'available'],
        ];

        $result = $this->service->validateAndFilterSlots($raw);

        $this->assertCount(1, $result);
    }

    public function test_有効なスロットが0件なら例外(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->validateAndFilterSlots([]);
    }

    public function test_すべて無効なら例外(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->validateAndFilterSlots([
            ['slot_id' => 1, 'availability' => 'maybe'],
        ]);
    }

    // --- capabilitiesAfterCancellation ---

    public function test_最初のスロットキャンセルでsetupがfalseになる(): void
    {
        $original = new VolunteerCapabilities(true, true, false);
        $updated  = $this->service->capabilitiesAfterCancellation($original, true, false);

        $this->assertFalse($updated->canHelpSetup());
        $this->assertTrue($updated->canHelpCleanup());
    }

    public function test_最後のスロットキャンセルでcleanupがfalseになる(): void
    {
        $original = new VolunteerCapabilities(true, true, false);
        $updated  = $this->service->capabilitiesAfterCancellation($original, false, true);

        $this->assertTrue($updated->canHelpSetup());
        $this->assertFalse($updated->canHelpCleanup());
    }

    public function test_中間スロットキャンセルはCapabilitiesを変更しない(): void
    {
        $original = new VolunteerCapabilities(true, true, true);
        $updated  = $this->service->capabilitiesAfterCancellation($original, false, false);

        $this->assertTrue($updated->canHelpSetup());
        $this->assertTrue($updated->canHelpCleanup());
    }

    public function test_setupがfalseなら最初キャンセルでも変わらない(): void
    {
        $original = new VolunteerCapabilities(false, true, false);
        $updated  = $this->service->capabilitiesAfterCancellation($original, true, false);

        $this->assertFalse($updated->canHelpSetup());
        $this->assertTrue($updated->canHelpCleanup());
    }

    // --- assertOwnedBy ---

    public function test_同じユーザーなら例外を投げない(): void
    {
        $this->expectNotToPerformAssertions();

        $this->service->assertOwnedBy(1, 1);
    }

    public function test_異なるユーザーなら例外を投げる(): void
    {
        $this->expectException(DomainException::class);

        $this->service->assertOwnedBy(1, 2);
    }
}
