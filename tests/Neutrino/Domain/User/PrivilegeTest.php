<?php

declare(strict_types=1);

namespace NeutrinoTest\Domain\User;

use Neutrino\Domain\User\Privilege;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PrivilegeTest extends TestCase
{
    #[Test]
    #[Group('User')]
    public function itCanBeCreatedWithValidArguments()
    {
        $privilege = new Privilege('delete');
        $this->assertEquals('delete', $privilege->name());
    }
}
