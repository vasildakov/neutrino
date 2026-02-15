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

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;

use function session_start;
use function session_status;
use function uniqid;
use function urlencode;

use const PHP_SESSION_NONE;

class FakePurchaseRequest extends AbstractRequest
{
    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->getParameters();
    }

    /**
     * @param mixed $data
     * @throws InvalidRequestException
     */
    public function sendData($data): ResponseInterface
    {
        // Simulate a redirect to a fake payment page
        $returnUrl = $this->getReturnUrl();
        $cancelUrl = $this->getCancelUrl();

        // Generate a fake token for the session
        $token = 'FAKE-' . uniqid('', true);

        // Store transaction data in session for later completion
        // NOTE: We do NOT store card data - GDPR/PCI DSS compliance
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['fake_payment'] = [
            'token'       => $token,
            'amount'      => $this->getAmount(),
            'currency'    => $this->getCurrency(),
            'description' => $this->getDescription(),
            // DO NOT store card data (PAN, CVV, expiry) - PCI DSS violation
            // Card data should only be transmitted to payment gateway, never stored
        ];

        // Build redirect URL to our fake payment page
        $redirectUrl = '/checkout/fake-payment?token=' . $token
                       . '&amount=' . $this->getAmount()
                       . '&currency=' . $this->getCurrency()
                       . '&description=' . urlencode($this->getDescription());

        return new FakePurchaseResponse($this, [
            'success'     => false,
            'redirect'    => true,
            'redirectUrl' => $redirectUrl,
            'token'       => $token,
        ]);
    }
}
