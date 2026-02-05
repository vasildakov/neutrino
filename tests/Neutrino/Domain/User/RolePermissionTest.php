<?php

declare(strict_types=1);

namespace NeutrinoTest\Domain\User;

use Neutrino\Domain\User\Privilege;
use Neutrino\Domain\User\Resource;
use Neutrino\Domain\User\Role;
use Neutrino\Domain\User\RolePermission;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RolePermission::class)]
class RolePermissionTest extends TestCase
{
    #[Test]
    #[Group('User')]
    #[Group('RolePermission')]
    public function itCanBeCreated(): void
    {
        $rolePermission = new RolePermission(
            role: new Role('administrator', Role::SCOPE_PLATFORM),
            resource: new Resource('blog.post'),
            privilege: new Privilege('delete'),
            allowed: true
        );

        $this->assertInstanceOf(RolePermission::class, $rolePermission);
    }
}