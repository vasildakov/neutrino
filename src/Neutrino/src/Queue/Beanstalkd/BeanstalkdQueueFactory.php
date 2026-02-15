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

namespace Neutrino\Queue\Beanstalkd;

use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;

class BeanstalkdQueueFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): BeanstalkdQueue
    {
        $pheanstalk = $container->get(Pheanstalk::class);
        assert($pheanstalk instanceof Pheanstalk);

        return new BeanstalkdQueue($pheanstalk, 'Queue.Neutrino.Test.Email');
    }
}
