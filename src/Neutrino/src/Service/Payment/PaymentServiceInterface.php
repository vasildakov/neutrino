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

interface PaymentServiceInterface
{
    /**
     * Initiate a purchase/payment
     *
     * @param array<string, mixed> $parameters
     */
    public function purchase(array $parameters): ResponseInterface;

    /**
     * Complete the purchase after the user returns from the payment gateway
     *
     * @param array<string, mixed> $parameters
     */
    public function completePurchase(array $parameters): ResponseInterface;

    /**
     * Get the underlying gateway instance
     */
    public function getGateway(): GatewayInterface;
}
