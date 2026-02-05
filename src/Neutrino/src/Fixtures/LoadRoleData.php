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
        foreach ($this->getData() as $name) {
            $role = new Role($name, 'platform');
            $manager->persist($role);
            $this->addReference($name, $role);

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
            'user',
            'manager',
            'administrator',
            'owner',
        ];
    }
}