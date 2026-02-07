<?php

namespace Neutrino\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Neutrino\Domain\User\Role;
use Neutrino\Domain\User\RoleScope;

class LoadRoleData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        $repo = $manager->getRepository(Role::class);

        foreach ($this->getData() as $row) {
            $reference = "{$row['name']}-{$row['scope']}";

            $role = $repo->findOneBy([
                'name'  => $row['name'],
                'scope' => $row['scope'],
            ]);

            if (! $role) {
                $role = new Role($row['name'], $row['scope']);
                $manager->persist($role);
            }

            $this->setReference($reference, $role);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1;
    }

    public function getData():array
    {
        return [
            ['name' => 'user', 'scope' => 'platform'],
            ['name' => 'manager', 'scope' => 'platform'],
            ['name' => 'administrator', 'scope' => 'platform'],
            ['name' => 'owner', 'scope' => 'platform'],
            ['name' => 'user', 'scope' => 'dashboard'],
        ];
    }
}