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
use Psr\Container\ContainerInterface;

class InMemoryAclProviderFactory
{
    public function __invoke(ContainerInterface $container): InMemoryAclProvider
    {
        $acl = new Acl();

        $acl->addRole('owner');
        $acl->addRole('administrator');

        $acl->addResource('platform.home');
        $acl->allow('owner', 'platform.home', null);

        return new InMemoryAclProvider($acl);
    }
}
