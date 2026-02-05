<?php

declare(strict_types=1);

namespace NeutrinoTest\Security;

use Laminas\Permissions\Acl\Acl;
use Neutrino\Security\Authorization\AclProviderInterface;
use Neutrino\Security\Authorization\ArrayAclProvider;
use Neutrino\Security\Authorization\AuthorizationService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthorizationService::class)]
class AuthorizationServiceTest extends TestCase
{
    #[Test]
    #[Group('Security')]
    public function itCanBeCreated(): void
    {
        $provider = new class implements AclProviderInterface {
            public function getAcl(): Acl
            {
                return new Acl();
            }
        };

        $service = new AuthorizationService($provider);
        $this->assertInstanceOf(AuthorizationService::class, $service);
    }

    #[Test]
    #[Group('Security')]
    public function itCanAuthorize(): void
    {
        $config = [
            'acl' => [
                'roles' => [
                    'Guest' => null,
                    'User'  => 'Guest',
                    'Admin' => 'User',
                ],
                'resources' => [
                    'dashboard',
                    'store' => ['store.order'],
                ],
                'permissions' => [
                    'allow' => [
                        'User' => [
                            'dashboard' => ['view'],
                        ],
                    ],
                    'deny' => [],
                ],
            ],
        ];

        $provider = new ArrayAclProvider($config);

        $service = new AuthorizationService($provider);

        $this->assertTrue($service->isAllowed(['User'], 'dashboard', 'view'));
        $this->assertFalse($service->isAllowed(['Guest'], 'dashboard', 'view'));
    }
}
