<?php

declare(strict_types=1);

namespace Neutrino\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Neutrino\Domain\User\Email;
use Neutrino\Domain\User\Password;
use Neutrino\Domain\User\Role;
use Neutrino\Domain\User\User;

class LoadUserData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getData() as $userData) {
            $user = new User(
                new Email($userData['email']),
                new Password($userData['password'])
            );
            if (isset($userData['avatar'])) {
                $user->setAvatar($userData['avatar']);
            }

            $manager->persist($user);
            foreach ($userData['roles'] as $role) {
                $user->addRole($this->getReference($role, Role::class));
            }

            $this->setReference($userData['email'], $user);
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
                'email' => 'vasildakov@gmail.com',
                'password' => '1',
                'avatar' => '/uploads/vasil.jpg',
                'roles' => ['owner-platform', 'administrator-platform'],
            ],
            [
                'email' => 'stanislava.dakova@gmail.com',
                'password' => '1',
                'avatar' => '/uploads/stanislava.jpg',
                'roles' => ['owner-platform', 'administrator-platform'],
            ],
            [
                'email' => 'manager1@neutrino.bg',
                'password' => '1',
                'roles' => ['manager-platform'],
            ],
            [
                'email' => 'manager2@neutrino.bg',
                'password' => '1',
                'roles' => ['manager-platform'],
            ]
        ];
    }

    public function getDependencies(): array
    {
        return [LoadRoleData::class];
    }
}