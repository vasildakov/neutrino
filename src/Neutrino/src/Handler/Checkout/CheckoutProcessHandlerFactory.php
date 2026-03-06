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
use Neutrino\Log\ApplicationLoggerInterface;
use Neutrino\Service\Payment\PaymentServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

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

        $paymentService = $container->get(PaymentServiceInterface::class);
        assert($paymentService instanceof PaymentServiceInterface);

        $form = $container->get(CheckoutForm::class);
        assert($form instanceof CheckoutForm);

        $logger = $container->get(ApplicationLoggerInterface::class);
        assert($logger instanceof LoggerInterface);

        $handler = new CheckoutProcessHandler($router, $em, $template, $paymentService, $form);
        $handler->setLogger($logger);

        return $handler;
    }
}
