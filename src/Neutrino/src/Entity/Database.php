<?php

declare(strict_types=1);

namespace Neutrino\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users_databases')]
class Database
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(name: 'createdAt', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\Column(name: 'name', type: Types::STRING, length: 191, unique: true, nullable: false)]
        private string $name,

        #[ORM\Column(name: 'host', type: Types::STRING, length: 191, nullable: false)]
        private string $host,

        #[ORM\Column(name: 'port', type: Types::INTEGER, nullable: false)]
        private int $port,

        #[ORM\Column(name: 'username', type: Types::STRING, length: 191, nullable: false)]
        private string $username,

        #[ORM\Column(name: 'password', type: Types::STRING, length: 191, nullable: false)]
        private string $password,

        #[ORM\Column(name: 'charset', type: Types::STRING, length: 32, nullable: false)]
        private string $charset = 'utf8mb4',
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function host(): string
    {
        return $this->host;
    }

    public function port(): int
    {
        return $this->port;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function charset(): string
    {
        return $this->charset;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
