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
namespace Neutrino\Queue;

use InvalidArgumentException;

final class CreateDatabasePayload
{
    public function __construct(
        public readonly string $dbName,
        public readonly string $dbUser,
        public readonly string $dbPassword
    ) {
        if ($dbName === '' || $dbUser === '' || $dbPassword === '') {
            throw new InvalidArgumentException('Database name, user, and password are required.');
        }
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'dbName' => $this->dbName,
            'dbUser' => $this->dbUser,
            'dbPassword' => $this->dbPassword,
        ];
    }
}
