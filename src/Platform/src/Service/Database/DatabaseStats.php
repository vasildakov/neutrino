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

namespace Platform\Service\Database;

use function assert;
use function is_float;
use function is_string;

readonly class DatabaseStats
{
    /**
     * @param string $database  The database name.
     * @param float $size       The database size in megabytes.
     * @param int $latency      The average latency in milliseconds.
     * @param string|null $migrationVersion The current migration version.
     */
    public function __construct(
        public string $id,
        public string $database,
        public float $size = 0.0,
        public int $latency = 0,
        public ?string $migrationVersion = null
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function asArray(): array
    {
        return [
            'id'               => $this->id,
            'database'         => $this->database,
            'size'             => $this->size,
            'latency'          => $this->latency,
            'migrationVersion' => $this->migrationVersion,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        assert(isset($data['database'], $data['size']));
        assert(is_string($data['database']));
        assert(is_float($data['size']));

        return new self(
            $data['id'],
            $data['database'],
            $data['size'],
            $data['latency'] ?? 0,
            $data['migrationVersion'] ?? null
        );
    }
}
