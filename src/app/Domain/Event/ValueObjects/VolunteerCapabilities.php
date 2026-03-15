<?php

namespace App\Domain\Event\ValueObjects;

/**
 * ボランティアの能力・協力意思を表すValueObject
 *
 * 申込時にユーザーが申告する3つのフラグを一体として管理する
 */
final class VolunteerCapabilities
{
    public function __construct(
        private readonly bool $canHelpSetup,
        private readonly bool $canHelpCleanup,
        private readonly bool $canTransportByCar,
    ) {}

    public static function none(): self
    {
        return new self(false, false, false);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            canHelpSetup:       (bool) ($data['can_help_setup'] ?? false),
            canHelpCleanup:     (bool) ($data['can_help_cleanup'] ?? false),
            canTransportByCar:  (bool) ($data['can_transport_by_car'] ?? false),
        );
    }

    public function canHelpSetup(): bool
    {
        return $this->canHelpSetup;
    }

    public function canHelpCleanup(): bool
    {
        return $this->canHelpCleanup;
    }

    public function canTransportByCar(): bool
    {
        return $this->canTransportByCar;
    }

    /** セットアップのみ協力を外した新しいインスタンスを返す */
    public function withoutSetup(): self
    {
        return new self(false, $this->canHelpCleanup, $this->canTransportByCar);
    }

    /** 片付けのみ協力を外した新しいインスタンスを返す */
    public function withoutCleanup(): self
    {
        return new self($this->canHelpSetup, false, $this->canTransportByCar);
    }

    public function toArray(): array
    {
        return [
            'can_help_setup'      => $this->canHelpSetup,
            'can_help_cleanup'    => $this->canHelpCleanup,
            'can_transport_by_car' => $this->canTransportByCar,
        ];
    }
}
