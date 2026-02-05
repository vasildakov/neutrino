<?php

declare(strict_types=1);

namespace Neutrino\Security\Authorization;

interface AuthorizationServiceInterface
{
    public function isAllowed(iterable $roles, string $resource, string $privilege): bool;
}