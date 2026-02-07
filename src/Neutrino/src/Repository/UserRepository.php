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
namespace Neutrino\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Neutrino\Domain\User\Email;
use Neutrino\Domain\User\User;

/**
 * @extends EntityRepository<User>
 */
class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    public function findOneByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = $this->findOneBy(['email' => mb_strtolower(trim($email))]);
        return $user;
    }


    /**
     * @throws NonUniqueResultException
     */
    public function authenticate(string $credential, ?string $password = null): ?UserInterface
    {
        /** @var User|null $user */
        $user = $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', new Email($credential))
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (! $user) {
            return null;
        }

        if (! $user->getPassword()->verify($password)) {
            return null;
        }

        // You can also create your own class implementing UserInterface
        return new DefaultUser(
            identity: (string) $user->getId(), // identity
            roles: (array) $user->getRolesNames(),  // roles
            details: (array) $user->getDetails()
        );
    }
}
