<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Resolver;

use Neutrino\Domain\User\User;

interface UserResolverInterface
{
    public function resolve(OauthIdentity $identity): User;
}
