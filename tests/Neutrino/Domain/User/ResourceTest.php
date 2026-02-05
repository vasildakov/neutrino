<?php

declare(strict_types=1);

namespace NeutrinoTest\Domain\User;

use Neutrino\Domain\User\Resource;
use Neutrino\Domain\User\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
class ResourceTest extends TestCase
{
    #[Test]
    #[Group('User')]
    #[Group('Resource')]
    public function itCanBeCreated(): void
    {
        $resource = new Resource('test');
        $this->assertEquals('test', $resource->name());
    }

    #[Test]
    #[Group('User')]
    #[Group('Resource')]
    public function itCanHaveParent(): void
    {
        $parent = new Resource('test');
        $child = new Resource('child', $parent);

        $this->assertEquals($parent, $child->parent());
    }
}
