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

namespace Neutrino\Authentication\Resolver;

use Neutrino\Domain\User\Email;
use Neutrino\Domain\User\Password;
use Neutrino\Domain\User\User;
use Neutrino\Domain\User\UserRepositoryInterface;
use RuntimeException;

final class OAuthUserResolver implements UserResolverInterface
{
    public function __construct(
        private UserRepositoryInterface $users
    ) {}

    public function resolve(OAuthIdentity $identity): User
    {
        if (!$identity->email) {
            throw new RuntimeException('OAuth provider did not return email');
        }

        $email = strtolower($identity->email);

        $user = $this->users->findOneByEmail($email);
        if ($user) {
            return $user;
        }

        $user = new User(
            email: new Email($email),
            password: Password::random(),
            name: $identity->name,
            surname: $identity->surname,
        );

        //$user->addRole();

        $this->users->create($user);

        return $user;
    }
}