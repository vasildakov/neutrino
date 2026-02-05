<?php

declare(strict_types=1);

namespace NeutrinoTest\Domain\User;

use Neutrino\Domain\User\RoleType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoleType::class)]
class RoleTypeTest extends TestCase
{
    #[Test]
    #[Group('User')]
    public function itCanBeCreatedFromString(): void
    {
        $roleType = RoleType::fromString('administrator');
        $this->assertInstanceOf(RoleType::class, $roleType);
    }

    #[Test]
    #[Group('User')]
    public function itCanGetAllValues(): void
    {
        $this->assertContains('administrator', RoleType::getAllValues());
    }

    #[Test]
    #[Group('User')]
    public function itCanIdentifyAdminRoles(): void
    {
        $this->assertTrue(RoleType::ADMINISTRATOR->isAdmin());
        $this->assertTrue(RoleType::OWNER->isAdmin());
        $this->assertFalse(RoleType::USER->isAdmin());
    }
}
