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

use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\GenericResource;
use Laminas\Permissions\Acl\Role\GenericRole;

use function is_array;
use function is_int;

class ArrayAclProvider implements AclProviderInterface
{
    private ?Acl $acl = null;

    public function __construct(private array $config)
    {
        // normalize
        $this->config = $config['acl'] ?? $config;
    }

    public function getAcl(): Acl
    {
        return $this->acl ??= $this->fromArray($this->config);
    }

    private function fromArray(array $cfg): Acl
    {
        $acl = new Acl();

        // Roles
        foreach (($cfg['roles'] ?? []) as $role => $parent) {
            $acl->addRole(new GenericRole((string)$role), $parent ?: null);
        }

        // Resources (supports nesting)
        $this->addResourcesRecursive($acl, $cfg['resources'] ?? []);

        // Permissions
        $allow = $cfg['permissions']['allow'] ?? [];
        foreach ($allow as $role => $rules) {
            foreach ($rules as $resource => $privileges) {
                foreach ((array)$privileges as $privilege) {
                    $acl->allow((string)$role, (string)$resource, (string)$privilege);
                }
            }
        }

        $deny = $cfg['permissions']['deny'] ?? [];
        foreach ($deny as $role => $rules) {
            foreach ($rules as $resource => $privileges) {
                foreach ((array)$privileges as $privilege) {
                    $acl->deny((string)$role, (string)$resource, (string)$privilege);
                }
            }
        }

        return $acl;
    }

    private function addResourcesRecursive(Acl $acl, array $resources, ?string $parent = null): void
    {
        foreach ($resources as $key => $value) {
            if (is_int($key)) {
                // leaf
                $name = (string)$value;
                if (! $acl->hasResource($name)) {
                    $acl->addResource(new GenericResource($name), $parent);
                }
                continue;
            }

            // parent => children
            $name = (string)$key;
            if (! $acl->hasResource($name)) {
                $acl->addResource(new GenericResource($name), $parent);
            }

            $children = is_array($value) ? $value : [];
            $this->addResourcesRecursive($acl, $children, $name);
        }
    }
}