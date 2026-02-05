<?php

declare(strict_types=1);

namespace Neutrino\Domain\Account;

use InvalidArgumentException;

enum AccountRole: string
{
    case Owner = 'Owner';
    case Admin = 'Admin';
    case Member = 'Member';

    public static function fromString(string $value): self
    {
        return match($value)  {
            'owner' => self::Owner,
            'admin' => self::Admin,
            'member' => self::Member,
            default => throw new InvalidArgumentException("Invalid account role: {$value}"),
        };
    }
}
