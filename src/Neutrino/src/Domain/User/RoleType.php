<?php

declare(strict_types=1);

namespace Neutrino\Domain\User;

enum RoleType: string
{
    case GUEST = 'guest';
    case USER = 'user';
    case MANAGER = 'manager';
    case ADMINISTRATOR = 'administrator';
    case OWNER = 'owner';

    public static function fromString(string $role): self
    {
        return self::from($role);
    }

    public static function tryFromString(string $role): ?self
    {
        return self::tryFrom($role);
    }

    public static function getAllValues(): array
    {
        return array_map(fn(self $role) => $role->value, self::cases());
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMINISTRATOR || $this === self::OWNER;
    }
}


