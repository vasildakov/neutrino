<?php

declare(strict_types=1);

namespace NeutrinoTest\Domain\User;

use Neutrino\Domain\User\Role;
use Neutrino\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
#[CoversClass(Role::class)]
class RoleTest extends TestCase
{
    #[Test]
    #[Group('User')]
    #[Group('Role')]
    public function itCanBeCreatedWithValidArguments(): void
    {
        $role = new Role(Role::ROLE_ADMINISTRATOR, Role::SCOPE_PLATFORM);
        $this->assertEquals('administrator', $role->name());
    }

    #[Test]
    #[Group('User')]
    #[Group('Role')]
    public function itCanAddChild(): void
    {
        $role = new Role(Role::ROLE_MANAGER, Role::SCOPE_PLATFORM);
        $role->addChild(new Role('manager', Role::SCOPE_PLATFORM));
        $this->assertCount(1, $role->getChildren());
    }
}
