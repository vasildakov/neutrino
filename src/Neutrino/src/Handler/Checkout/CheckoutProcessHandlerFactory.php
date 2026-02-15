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

namespace Neutrino\Handler\Checkout;

use Doctrine\ORM\EntityManagerInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Service\Payment\FakeService;
use Neutrino\Service\Payment\PaymentServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function assert;

final class CheckoutProcessHandlerFactory
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

        $template = $container->get(TemplateRendererInterface::class);
        assert($template instanceof TemplateRendererInterface);

        // Get FakeService (implements PaymentServiceInterface)
        $paymentService = $container->get(FakeService::class);
        assert($paymentService instanceof PaymentServiceInterface);

        return new CheckoutProcessHandler($router, $em, $template, $paymentService);
    }
}
