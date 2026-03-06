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

namespace Neutrino\Handler\Login;

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\Session\PhpSession;
use Neutrino\Log\ApplicationLoggerInterface;
use Neutrino\Queue\Contract\QueueInterface;
use Neutrino\Repository\UserRepository;
use Neutrino\Service\Cart\CartService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;

final class LoginHandlerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LoginHandler
    {
        $auth = $container->get(PhpSession::class);
        assert($auth instanceof AuthenticationInterface);

        $queue = $container->get(QueueInterface::class);
        assert($queue instanceof QueueInterface);

        $cartService = $container->get(CartService::class);
        assert($cartService instanceof CartService);

        $userRepository = $container->get(UserRepository::class);
        assert($userRepository instanceof UserRepository);

        $inputFilter = new LoginInputFilter();

        $logger = $container->get(ApplicationLoggerInterface::class);

        $handler = new LoginHandler($auth, $inputFilter, $cartService, $userRepository);
        $handler->setLogger($logger);

        return $handler;
    }
}
