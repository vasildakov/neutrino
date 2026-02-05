<?php

declare(strict_types=1);

namespace Dashboard\Service\Database;

readonly class DatabaseStats
{
    /**
     * @param string $database  The database name.
     * @param float $size       The database size in megabytes.
     * @param int $latency      The average latency in milliseconds.
     */
    public function __construct(
        public string $database,
        public float $size = 0.0,
        public int $latency = 0,
    ) {}

    public function asArray(): array
    {
        return [
            'database' => $this->database,
            'size'     => $this->size,
            'latency'  => $this->latency,
        ];
    }

    public static function fromArray(array $data): self
    {
        assert(isset($data['database'], $data['size']));
        assert(is_string($data['database']));
        assert(is_float($data['size']));

        return new self(
            $data['database'],
            $data['size']
        );
    }
}
