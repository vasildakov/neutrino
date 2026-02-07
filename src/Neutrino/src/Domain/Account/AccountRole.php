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
