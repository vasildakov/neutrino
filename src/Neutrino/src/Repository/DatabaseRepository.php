<?php

namespace Neutrino\Repository;

use Neutrino\Entity\Database;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<Database>
 */
class DatabaseRepository extends EntityRepository
{

    public function findByName(string $name): ?object
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function all(): array
    {
        return $this->findAll();
    }
}