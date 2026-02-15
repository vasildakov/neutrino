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

namespace Neutrino\Service\Payment;

use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;

readonly class FakeService implements PaymentServiceInterface
{
    public function __construct(private GatewayInterface $gateway)
    {
    }

    /**
     * Purchase with Fake Gateway
     *
     * @param array<string, mixed> $parameters
     */
    public function purchase(array $parameters): ResponseInterface
    {
        return $this->gateway->purchase($parameters)->send();
    }

    /**
     * Complete the purchase after the user returns from the fake payment page
     *
     * @param array<string, mixed> $parameters
     */
    public function completePurchase(array $parameters): ResponseInterface
    {
        return $this->gateway->completePurchase($parameters)->send();
    }

    /**
     * Get the gateway instance
     */
    public function getGateway(): GatewayInterface
    {
        return $this->gateway;
    }
}
