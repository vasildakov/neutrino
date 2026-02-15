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

namespace Neutrino\Handler\Register;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Neutrino\Domain\User\Email;
use Neutrino\Domain\User\Password;
use Neutrino\Domain\User\Role;
use Neutrino\Domain\User\User;
use Neutrino\Repository\UserRepository;

final readonly class RegisterService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function register(RegisterInput $input): User
    {
        $email    = new Email($input->email);
        $password = new Password($input->password);

        /** @var UserRepository $repository */
        $repository = $this->em->getRepository(User::class);

        if ($repository->findOneByEmail($email->getValue())) {
            throw new InvalidArgumentException('Email already registered');
        }

        $role = $this->em
            ->getRepository(Role::class)
            ->findOneBy([
                'name'  => 'user',
                'scope' => 'backoffice',
            ]);

        $user = new User(email: $email, password: $password);
        $user->addRole($role);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
