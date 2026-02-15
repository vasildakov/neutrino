<?php

declare(strict_types=1);
/*
 * This file is part of Neutrino.
 *
 * (c) Vasil Dakov <vasildakov@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Neutrino\Domain\User;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Neutrino\Repository\UserActivityRepository;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

use function str_contains;
use function preg_match;

#[ORM\Entity(repositoryClass: UserActivityRepository::class)]
#[ORM\Table(name: "user_activities")]
#[ORM\Index(name: "idx_user_activities_user_id", columns: ["user_id"])]
#[ORM\Index(name: "idx_user_activities_created_at", columns: ["created_at"])]
class UserActivity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface|string $id;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'activities')]
        #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
        private readonly User $user,

        #[ORM\Column(name: 'activity_type', type: Types::STRING, length: 50, nullable: false)]
        private readonly string $activityType,

        #[ORM\Column(name: 'description', type: Types::STRING, length: 255, nullable: false)]
        private readonly string $description,

        #[ORM\Column(name: 'ip_address', type: Types::STRING, length: 45, nullable: true)]
        private readonly ?string $ipAddress = null,

        #[ORM\Column(name: 'user_agent', type: Types::STRING, length: 255, nullable: true)]
        private readonly ?string $userAgent = null,

        #[ORM\Column(name: 'city', type: Types::STRING, length: 100, nullable: true)]
        private readonly ?string $city = null,
    ) {
        $this->id = Uuid::uuid4();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): UuidInterface|string
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getActivityType(): string
    {
        return $this->activityType;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getBrowser(): string
    {
        if ($this->userAgent === null) {
            return 'Unknown';
        }

        // Simple browser detection from user agent
        if (str_contains($this->userAgent, 'Chrome')) {
            preg_match('/Chrome\/(\d+)/', $this->userAgent, $matches);
            return 'Chrome ' . ($matches[1] ?? '');
        }
        if (str_contains($this->userAgent, 'Firefox')) {
            preg_match('/Firefox\/(\d+)/', $this->userAgent, $matches);
            return 'Firefox ' . ($matches[1] ?? '');
        }
        if (str_contains($this->userAgent, 'Safari') && !str_contains($this->userAgent, 'Chrome')) {
            preg_match('/Version\/(\d+\.\d+)/', $this->userAgent, $matches);
            return 'Safari ' . ($matches[1] ?? '');
        }
        if (str_contains($this->userAgent, 'Edge')) {
            preg_match('/Edge\/(\d+)/', $this->userAgent, $matches);
            return 'Edge ' . ($matches[1] ?? '');
        }

        return 'Unknown';
    }

    public function getBadgeClass(): string
    {
        return match ($this->activityType) {
            'login' => 'bg-success',
            'logout' => 'bg-danger',
            'update' => 'bg-info',
            'view' => 'bg-primary',
            'password' => 'bg-warning',
            'create' => 'bg-success',
            'delete' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getActivityLabel(): string
    {
        return ucfirst($this->activityType);
    }
}

