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

namespace Neutrino\Service\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

class CartServiceFactory
{
    public function __invoke(ContainerInterface $container): CartService
    {
        $em = $container->get(EntityManagerInterface::class);

        return new CartService($em);
    }
}

