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

namespace Neutrino\Authentication\Handler\Google;

use Psr\Container\ContainerInterface;

class GoogleSuccessHandlerFactory
{
    public function __invoke(ContainerInterface $container): GoogleSuccessHandler
    {
        return new GoogleSuccessHandler();
    }
}
