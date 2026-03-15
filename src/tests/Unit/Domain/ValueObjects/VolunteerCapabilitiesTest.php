<?php

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\Event\ValueObjects\VolunteerCapabilities;
use PHPUnit\Framework\TestCase;

class VolunteerCapabilitiesTest extends TestCase
{
    public function test_none_ですべてfalseを生成できる(): void
    {
        $cap = VolunteerCapabilities::none();

        $this->assertFalse($cap->canHelpSetup());
        $this->assertFalse($cap->canHelpCleanup());
        $this->assertFalse($cap->canTransportByCar());
    }

    public function test_fromArray_で正しく生成できる(): void
    {
        $cap = VolunteerCapabilities::fromArray([
            'can_help_setup'       => true,
            'can_help_cleanup'     => false,
            'can_transport_by_car' => true,
        ]);

        $this->assertTrue($cap->canHelpSetup());
        $this->assertFalse($cap->canHelpCleanup());
        $this->assertTrue($cap->canTransportByCar());
    }

    public function test_withoutSetup_でセットアップフラグのみfalseになる(): void
    {
        $cap     = new VolunteerCapabilities(true, true, true);
        $updated = $cap->withoutSetup();

        $this->assertFalse($updated->canHelpSetup());
        $this->assertTrue($updated->canHelpCleanup());
        $this->assertTrue($updated->canTransportByCar());
    }

    public function test_withoutCleanup_で片付けフラグのみfalseになる(): void
    {
        $cap     = new VolunteerCapabilities(true, true, true);
        $updated = $cap->withoutCleanup();

        $this->assertTrue($updated->canHelpSetup());
        $this->assertFalse($updated->canHelpCleanup());
        $this->assertTrue($updated->canTransportByCar());
    }

    public function test_イミュータブルである(): void
    {
        $original = new VolunteerCapabilities(true, true, true);
        $updated  = $original->withoutSetup();

        // 元のオブジェクトは変更されない
        $this->assertTrue($original->canHelpSetup());
        $this->assertFalse($updated->canHelpSetup());
    }

    public function test_toArray_で配列に変換できる(): void
    {
        $cap   = new VolunteerCapabilities(true, false, true);
        $array = $cap->toArray();

        $this->assertSame([
            'can_help_setup'       => true,
            'can_help_cleanup'     => false,
            'can_transport_by_car' => true,
        ], $array);
    }
}
