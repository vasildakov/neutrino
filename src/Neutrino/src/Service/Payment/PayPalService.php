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
use Omnipay\Omnipay;

class PayPalService implements PaymentServiceInterface
{
    private GatewayInterface $gateway;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        // Use real PayPal gateway
        $this->gateway = Omnipay::create('PayPal_Express');

        $this->gateway->setUsername($config['username']);
        $this->gateway->setPassword($config['password']);
        $this->gateway->setSignature($config['signature']);
        $this->gateway->setTestMode($config['sandbox']);
    }

    /**
     * Purchase with PayPal Express Checkout
     */
    public function purchase(array $parameters): ResponseInterface
    {
        return $this->gateway->purchase($parameters)->send();
    }

    /**
     * Complete the purchase after the user returns from PayPal
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
