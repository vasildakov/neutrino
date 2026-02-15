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

use Mezzio\Authentication\Session\PhpSession;
use Neutrino\Queue\QueueInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class LoginHandlerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LoginHandler
    {
        /** @var PhpSession $auth */
        $auth = $container->get(PhpSession::class);
        assert($auth instanceof PhpSession);

        $queue = $container->get(QueueInterface::class);
        assert($queue instanceof QueueInterface);

        return new LoginHandler($auth, $queue);
    }
}