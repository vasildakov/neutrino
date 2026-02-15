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

namespace Neutrino\Handler\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Router\RouterInterface;
use Neutrino\Service\Cart\CartService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function assert;

final class AddToCartHandlerFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $router = $container->get(RouterInterface::class);
        assert($router instanceof RouterInterface);

        $em = $container->get(EntityManagerInterface::class);
        assert($em instanceof EntityManagerInterface);

        $cartService = $container->get(CartService::class);
        assert($cartService instanceof CartService);

        return new AddToCartHandler($router, $em, $cartService);
    }
}
