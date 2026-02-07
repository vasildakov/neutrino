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

    public function isAllowed(iterable $roles, string $resource, ?string $privilege = null): bool
    {
        $acl = $this->provider->getAcl();

//        $resourceId  = is_string($resource)  ? $resource  : $resource->name();
//        $privilegeId = is_string($privilege) ? $privilege : $privilege->name();

        foreach ($roles as $role) {
            $roleId = is_string($role) ? $role : $role->name();

            if ($acl->isAllowed($roleId, $resource, $privilege)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param iterable<Role>|iterable<string> $roles
     */
    public function assertAllowed(iterable $roles, string $resource, ?string $privilege = null): void
    {
        if (! $this->isAllowed($roles, $resource, $privilege)) {
            throw new \RuntimeException('Forbidden'); // replace it with your ForbiddenException
        }
    }
}
