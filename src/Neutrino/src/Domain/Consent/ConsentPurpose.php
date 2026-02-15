<?php

declare(strict_types=1);

namespace Neutrino\Domain\Consent;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'consent_purposes')]
#[ORM\UniqueConstraint(name: 'uniq_consent_purpose_code', columns: ['code'])]
class ConsentPurpose
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: Types::GUID)]
        private string $id,

        #[ORM\Column(type: Types::STRING, length: 64)]
        private string $code, // essential, functional, analytics, marketing

        #[ORM\Column(type: Types::STRING, length: 190)]
        private string $title,

        #[ORM\Column(type: Types::BOOLEAN)]
        private bool $required,

        #[ORM\Column(type: Types::INTEGER)]
        private int $version,

        #[ORM\Column(type: Types::TEXT)]
        private string $description
    ) {}

    public function id(): string { return $this->id; }
    public function code(): string { return $this->code; }
    public function title(): string { return $this->title; }
    public function required(): bool { return $this->required; }
    public function version(): int { return $this->version; }
    public function description(): string { return $this->description; }
}
