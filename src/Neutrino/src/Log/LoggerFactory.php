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

namespace Neutrino\Log;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class LoggerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName): Logger
    {
        $config = $container->get('config')['logger'] ?? [];

        $channel = $config['channels'][$requestedName]['name'] ?? $requestedName;
        $path    = $config['channels'][$requestedName]['path']
            ?? "var/log/{$requestedName}.log";
        $level   = $config['channels'][$requestedName]['level']
            ?? Level::Debug;

        $logger = new Logger($channel);

        $handler = new StreamHandler($path, $level);
        $handler->setFormatter(new JsonFormatter());

        $logger->pushHandler($handler);

        return $logger;
    }
}
