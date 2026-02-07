<?php

declare(strict_types=1);

namespace Platform\Service\Database;

/**
 * @psalm-immutable
 */
class DatabaseStatsCollection
{
    /**
     * @var list<DatabaseStats>
     */
    private array $elements = [];

    public function add(DatabaseStats $stats): void
    {
        $this->elements[] = $stats;
    }


    public function getElements(): array
    {
        return $this->elements;
    }
}