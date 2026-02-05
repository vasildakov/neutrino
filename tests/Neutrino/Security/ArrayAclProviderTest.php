<?php

declare(strict_types=1);

namespace NeutrinoTest\Security;

use Neutrino\Security\Authorization\ArrayAclProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayAclProvider::class)]
class ArrayAclProviderTest extends TestCase
{
    #[Test]
    #[Group('Security')]
    public function itCanBeCreatedFromArray(): void
    {
        $config['acl'] = [
            'roles' => [
                'guest' => null,
                'user' => 'guest'
            ],
            'resources' => [
                'dashboard',
                'store' => ['store.order']
            ],
            'permissions' => [
                'allow' => [
                    'user' => [
                        'dashboard' => ['view']
                    ]
                ],
                'deny'  => [],
            ],
        ];

        $provider = new ArrayAclProvider($config['acl']);
        $this->assertInstanceOf(ArrayAclProvider::class, $provider);
    }
}