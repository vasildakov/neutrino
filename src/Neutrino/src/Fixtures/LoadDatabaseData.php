<?php

declare(strict_types=1);

namespace Neutrino\Fixtures;

use Neutrino\Entity\Database;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadDatabaseData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getData() as $databaseData) {
            $database = new Database(
                $databaseData['name'],
                $databaseData['host'],
                $databaseData['port'],
                $databaseData['username'],
                $databaseData['password'],
            );

            $manager->persist($database);
        }
        $manager->flush();

    }

    public function getOrder(): int
    {
        return 2;
    }

    public function getData(): array
    {
        return [
            [
                'name' => 'customer1_db',
                'host' => 'localhost',
                'port' => 3306,
                'username' => 'customer1_user',
                'password' => 'customer1_pass',
                'database' => 'customer1_database',
                'driver' => 'pdo_mysql',
            ],
            [
                'name' => 'customer2_db',
                'host' => 'localhost',
                'port' => 3306,
                'username' => 'customer2_user',
                'password' => 'customer2_pass',
                'database' => 'customer2_database',
                'driver' => 'pdo_mysql',
            ],
            [
                'name' => 'customer3_db',
                'host' => 'localhost',
                'port' => 3306,
                'username' => 'customer3_user',
                'password' => 'customer3_pass',
                'database' => 'customer3_database',
                'driver' => 'pdo_mysql',
            ],
        ];
    }
}
