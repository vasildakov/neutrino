<?php

declare(strict_types=1);

namespace Neutrino\Domain\Consent;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'consent_events')]
#[ORM\Index(name: 'idx_consent_events_subject', columns: ['subject_type', 'subject_id'])]
#[ORM\Index(name: 'idx_consent_events_occurred', columns: ['occurred_at'])]
class ConsentEvent
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: Types::GUID)]
        private string $id,
        #[ORM\Column(name: 'subject_type', type: Types::STRING, length: 32)]
        private string $subjectType, // "user" or "visitor"
        #[ORM\Column(name: 'subject_id', type: Types::STRING, length: 128)]
        private string $subjectId, // user UUID OR visitorId cookie
        #[ORM\Column(name: 'purpose_code', type: Types::STRING, length: 64)]
        private string $purposeCode,
        #[ORM\Column(type: Types::BOOLEAN)]
        private bool $granted,
        #[ORM\Column(name: 'purpose_version', type: Types::INTEGER)]
        private int $purposeVersion,
        #[ORM\Column(type: Types::STRING, length: 32)]
        private string $source, // "banner", "settings", "api"
        #[ORM\Column(name: 'occurred_at', type: Types::DATETIME_IMMUTABLE)]
        private DateTimeImmutable $occurredAt,
        #[ORM\Column(name: 'ip_hash', type: Types::STRING, length: 64, nullable: true)]
        private ?string $ipHash,
        #[ORM\Column(name: 'user_agent', type: Types::STRING, length: 255, nullable: true)]
        private ?string $userAgent,
        #[ORM\Column(type: Types::JSON)]
        private array $meta = []
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSubjectType(): string
    {
        return $this->subjectType;
    }

    public function getSubjectId(): string
    {
        return $this->subjectId;
    }

    public function getPurposeCode(): string
    {
        return $this->purposeCode;
    }

    public function isGranted(): bool
    {
        return $this->granted;
    }

    public function getPurposeVersion(): int
    {
        return $this->purposeVersion;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getIpHash(): ?string
    {
        return $this->ipHash;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }
}
