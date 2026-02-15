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

namespace Neutrino\Service\Payment\Fake;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\RequestInterface;

/**
 * Fake Payment Gateway for Testing
 *
 * This gateway simulates payment processing without actually connecting to any payment service.
 * Useful for development and testing.
 */
class FakeGateway extends AbstractGateway
{
    public function getName(): string
    {
        return 'Fake';
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaultParameters(): array
    {
        return [
            'testMode' => true,
        ];
    }

    /**
     * @param array<string, mixed> $options
     */
    public function purchase(array $options = []): RequestInterface
    {
        return $this->createRequest(FakePurchaseRequest::class, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function completePurchase(array $options = []): RequestInterface
    {
        return $this->createRequest(FakeCompletePurchaseRequest::class, $options);
    }
}
