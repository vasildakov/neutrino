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

use JsonSerializable;

final class AuthenticatedUser implements UserInterface, JsonSerializable
{
    /**
     * @psalm-param array<int|string, string> $roles
     * @psalm-param array<string, mixed> $details
     */
    public function __construct(
        private readonly string $identity,
        private readonly array $roles = [],
        private array $details = []
    ) {
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }

    /**
     * @psalm-return array<int|string, string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @psalm-return array<string, mixed>
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param string $name
     * @param $default
     * @return mixed
     */
    public function getDetail(string $name, $default = null): mixed
    {
        return $this->details[$name] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'identity' => $this->identity,
            'roles'    => $this->roles,
            'details'  => $this->details,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }
}