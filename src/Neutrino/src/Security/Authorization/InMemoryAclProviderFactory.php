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

        $acl->addRole('manager');
        $acl->addRole('administrator', 'manager');
        $acl->addRole('owner', 'administrator');


        $acl->addResource('platform.home');
        $acl->addResource('platform.accounts');
        $acl->addResource('platform.databases');


        $acl->allow('manager', 'platform.home');


        $acl->allow('administrator', 'platform.home', null);
        $acl->allow('administrator', 'platform.accounts', null);
        $acl->allow('administrator', 'platform.databases', null);

        return new InMemoryAclProvider($acl);
    }
}
