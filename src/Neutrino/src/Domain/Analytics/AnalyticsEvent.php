<?php

declare(strict_types=1);

namespace Neutrino\Domain\Analytics;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: AnalyticsRepository::class)]
#[ORM\Table(
    name: 'analytics_events',
    indexes: [
        new ORM\Index(name: 'idx_analytics_occurred_at', columns: ['occurred_at']),
        new ORM\Index(name: 'idx_analytics_path', columns: ['path']),
        new ORM\Index(name: 'idx_analytics_ip', columns: ['ip']),
        new ORM\Index(name: 'idx_analytics_visitor_id', columns: ['visitor_id']),
        new ORM\Index(name: 'idx_analytics_session_id', columns: ['session_id']),
        new ORM\Index(name: 'idx_analytics_session_occurred', columns: ['session_id', 'occurred_at']),
    ]
)]
class AnalyticsEvent
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $id;

    #[ORM\Column(name: 'occurred_at', type: Types::DATETIME_IMMUTABLE, precision: 6)]
    private DateTimeImmutable $occurredAt;

    #[ORM\Column(type: Types::STRING, length: 10)]
    private string $method;

    #[ORM\Column(type: Types::STRING, length: 768)]
    private string $path;

    #[ORM\Column(name: 'query_string', type: Types::TEXT, nullable: true)]
    private ?string $queryString = null;

    #[ORM\Column(type: Types::STRING, length: 45, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(name: 'user_agent', type: Types::TEXT, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(name: 'browser', type: Types::STRING, length: 32, nullable: true)]
    private ?string $browser = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $referer = null;

    #[ORM\Column(name: 'accept_language', type: Types::STRING, length: 255, nullable: true)]
    private ?string $acceptLanguage = null;

    #[ORM\Column(name: 'continent', type: Types::STRING, length: 255, nullable: true)]
    private ?string $continent = null;

    #[ORM\Column(name: 'country', type: Types::STRING, length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(name: 'city', type: Types::STRING, length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(name: 'latitude', type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(name: 'longitude', type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $status;

    #[ORM\Column(name: 'duration_ms', type: Types::INTEGER)]
    private int $durationMs;

    #[ORM\Column(name: 'visitor_id', type: 'string', length: 64, nullable: true)]
    private ?string $visitorId;

    #[ORM\Column(name: 'session_id', type: 'string', length: 64, nullable: true)]
    private ?string $sessionId;

    public function __construct(
        string $method,
        string $path,
        int $status,
        int $durationMs,
        ?string $queryString = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $browser = null,
        ?string $referer = null,
        ?string $acceptLanguage = null,
        ?string $continent = null,
        ?string $country = null,
        ?string $city = null,
        ?string $latitude = null,
        ?string $longitude = null,
        ?string $visitorId = null,
        ?string $sessionId = null,
        ?DateTimeImmutable $occurredAt = null,
    ) {
        $this->id             = Uuid::uuid4();
        $this->method         = $method;
        $this->path           = $path;
        $this->status         = $status;
        $this->durationMs     = $durationMs;
        $this->queryString    = $queryString;
        $this->ip             = $ip;
        $this->userAgent      = $userAgent;
        $this->browser        = $browser;
        $this->referer        = $referer;
        $this->acceptLanguage = $acceptLanguage;
        $this->continent      = $continent;
        $this->country        = $country;
        $this->city           = $city;
        $this->latitude       = $latitude;
        $this->longitude      = $longitude;
        $this->visitorId      = $visitorId;
        $this->sessionId      = $sessionId;
        $this->occurredAt     = $occurredAt ?? new DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQueryString(): ?string
    {
        return $this->queryString;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getReferer(): ?string
    {
        return $this->referer;
    }

    public function getAcceptLanguage(): ?string
    {
        return $this->acceptLanguage;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getDurationMs(): int
    {
        return $this->durationMs;
    }

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    public function setBrowser(?string $browser): void
    {
        $this->browser = $browser;
    }

    public function setContinent(?string $continent): void
    {
        $this->continent = $continent;
    }

    public function getContinent(): ?string
    {
        return $this->continent;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setLatitude(?string $latitude = null): void
    {
        $this->latitude = $latitude;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLongitude(?string $longitude = null): void
    {
        $this->longitude = $longitude;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    /**
     * @throws Exception
     * @param array<string, mixed> $array
     */
    public static function fromArray(array $array): self
    {
        return new self(
            method: (string) ($array['method'] ?? 'GET'),
            path: (string) ($array['path'] ?? '/'),
            status: (int) ($array['status'] ?? 200),
            durationMs: (int) ($array['durationMs'] ?? 0),
            queryString: self::nullableString($array, 'queryString'),
            ip: self::nullableString($array, 'ip'),
            userAgent: self::nullableString($array, 'userAgent'),
            browser: self::nullableString($array, 'browser'),
            referer: self::nullableString($array, 'referer'),
            acceptLanguage: self::nullableString($array, 'acceptLanguage'),
            visitorId: self::nullableString($array, 'visitorId'),
            sessionId: self::nullableString($array, 'sessionId'),
            occurredAt: self::nullableDate($array, 'occurredAt')
        );
    }

    /**
     * @param array<string, mixed>  $array
     */
    private static function nullableString(array $array, string $key): ?string
    {
        return isset($array[$key]) ? (string) $array[$key] : null;
    }

    /**
     * @param array<string, mixed>  $array
     * @throws Exception
     */
    private static function nullableDate(array $array, string $key): ?DateTimeImmutable
    {
        return isset($array[$key])
            ? new DateTimeImmutable((string) $array[$key])
            : null;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function getVisitorId(): ?string
    {
        return $this->visitorId;
    }
}
