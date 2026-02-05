<?php

namespace Neutrino\Repository;

use Neutrino\Entity\Database;

interface DatabaseRepositoryInterface
{
    public function findByName(string $name): ?Database;

    /** @return Database[] */
    public function all(): array;
}
