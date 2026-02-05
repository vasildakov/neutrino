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

use InvalidArgumentException;
use function password_hash;
use function password_needs_rehash;
use function password_verify;
use function str_starts_with;

readonly class Password
{
    private string $hash;

    public function __construct(string $password)
    {
        if (empty($password)) {
            throw new InvalidArgumentException('Password cannot be empty');
        }

        // Check if it's already hashed (for Doctrine hydration)
        if (str_starts_with($password, '$argon2i$') || str_starts_with($password, '$argon2id$')) {
            $this->hash = $password;
        } else {
            // Hash plain password
            $this->hash = password_hash($password, PASSWORD_ARGON2I);
        }
    }


    public function verify(string $password): bool
    {
        return password_verify($password, $this->hash);
    }


    public function getHash(): string
    {
        return $this->hash;
    }

    public function needsRehash(): bool
    {
        return password_needs_rehash($this->hash, PASSWORD_ARGON2I);
    }

    public function __toString(): string
    {
        return $this->hash;
    }
}