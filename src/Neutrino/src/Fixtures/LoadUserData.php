<?php

declare(strict_types=1);

namespace Neutrino\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
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
                email: new Email($userData['email']),
                password: new Password($userData['password']),
                name: $userData['name'],
                surname: $userData['surname']
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

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getData(): array
    {
        return [
            [
                'email'    => 'vasildakov@gmail.com',
                'password' => '1',
                'name'     => 'Vasil',
                'surname'  => 'Dakov',
                'avatar'   => '/uploads/vasil.jpg',
                'roles'    => ['owner-platform', 'administrator-platform'],
            ],
            [
                'email'    => 'stanislava.dakova@gmail.com',
                'password' => '1',
                'name'     => 'Stanislava',
                'surname'  => 'Dakova',
                'avatar'   => '/uploads/stanislava.jpg',
                'roles'    => ['owner-platform', 'administrator-platform'],
            ],
            [
                'email'    => 'john.doe@neutrino.bg',
                'password' => '1',
                'name'     => 'John',
                'surname'  => 'Doe',
                'roles'    => ['manager-platform'],
            ],
            [
                'email'    => 'bill.gates@neutrino.bg',
                'name'     => 'Bill',
                'surname'  => 'Gates',
                'password' => '1',
                'roles'    => ['manager-platform'],
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [LoadRoleData::class];
    }
}
