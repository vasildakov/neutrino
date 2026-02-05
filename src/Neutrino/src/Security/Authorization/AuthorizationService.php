<?php

declare(strict_types=1);

namespace Neutrino\Security\Authorization;

use Neutrino\Domain\User\Privilege;
use Neutrino\Domain\User\Resource;
use Neutrino\Domain\User\Role;

use function is_string;

readonly class AuthorizationService implements AuthorizationServiceInterface
{

    public function __construct(
        private AclProviderInterface $provider,
    ) {}

    public function isAllowed(iterable $roles, Resource|string $resource, Privilege|string $privilege): bool
    {
        $acl = $this->provider->getAcl();

        $resourceId  = is_string($resource)  ? $resource  : $resource->name();
        $privilegeId = is_string($privilege) ? $privilege : $privilege->name();

        foreach ($roles as $role) {
            $roleId = is_string($role) ? $role : $role->name();

            if ($acl->isAllowed($roleId, $resourceId, $privilegeId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param iterable<Role>|iterable<string> $roles
     */
    public function assertAllowed(iterable $roles, Resource|string $resource, Privilege|string $privilege): void
    {
        if (! $this->isAllowed($roles, $resource, $privilege)) {
            throw new \RuntimeException('Forbidden'); // replace it with your ForbiddenException
        }
    }
}
