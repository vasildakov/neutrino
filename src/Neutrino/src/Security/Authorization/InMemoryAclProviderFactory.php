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

        // Define roles (inheritance: owner > administrator > manager > user > guest)
        $acl->addRole('guest');
        $acl->addRole('user');
        $acl->addRole('manager', 'user');
        $acl->addRole('administrator', 'manager');
        $acl->addRole('owner', 'administrator');

        // Public resources (accessible to all)
        $acl->addResource('home');
        $acl->addResource('login');
        $acl->addResource('register');
        $acl->addResource('cart.view');
        $acl->addResource('cart.add');
        $acl->addResource('cart.remove');
        $acl->addResource('checkout');
        $acl->addResource('checkout.process');
        $acl->addResource('checkout.return');
        $acl->addResource('checkout.cancel');
        $acl->addResource('checkout.success');
        $acl->addResource('checkout.fake-payment');

        // Platform resources (admin/management only)
        $acl->addResource('platform.home');
        $acl->addResource('platform.accounts');
        $acl->addResource('platform.databases');
        $acl->addResource('platform.queues');
        $acl->addResource('platform.accounts.view');
        $acl->addResource('platform.analytics.browser');
        $acl->addResource('platform.analytics.visits');


        // Public access (guest and all users)
        $acl->allow(null, 'home');
        $acl->allow(null, 'login');
        $acl->allow(null, 'register');
        $acl->allow(null, 'cart.view');
        $acl->allow(null, 'cart.add');
        $acl->allow(null, 'cart.remove');
        $acl->allow(null, 'checkout');
        $acl->allow(null, 'checkout.process');
        $acl->allow(null, 'checkout.return');
        $acl->allow(null, 'checkout.cancel');
        $acl->allow(null, 'checkout.success');
        $acl->allow(null, 'checkout.fake-payment');

        // Manager access
        $acl->allow('manager', 'platform.home');

        // Administrator access (inherits manager permissions)
        $acl->allow('administrator', 'platform.home');
        $acl->allow('administrator', 'platform.accounts');
        $acl->allow('administrator', 'platform.databases');
        $acl->allow('administrator', 'platform.queues');
        $acl->allow('administrator', 'platform.accounts.view');
        $acl->allow('administrator', 'platform.analytics.browser');
        $acl->allow('administrator', 'platform.analytics.visits');

        // Owner inherits all administrator permissions

        return new InMemoryAclProvider($acl);
    }
}
