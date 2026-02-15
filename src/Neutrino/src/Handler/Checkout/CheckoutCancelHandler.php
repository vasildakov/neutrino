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

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function error_log;

final readonly class CheckoutCancelHandler implements RequestHandlerInterface
{
    public function __construct(
        private RouterInterface $router
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get query parameters
        $queryParams   = $request->getQueryParams();
        $planId        = $queryParams['plan'] ?? null;
        $billingPeriod = $queryParams['period'] ?? 'monthly';

        error_log('PayPal payment cancelled by user');

        // Redirect back to checkout with a cancel message
        return new RedirectResponse(
            $this->router->generateUri('checkout')
            . '?plan=' . $planId
            . '&period=' . $billingPeriod
            . '&error=payment_cancelled'
        );
    }
}
