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

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;

use function session_start;
use function session_status;
use function uniqid;

use const PHP_SESSION_NONE;

class FakeCompletePurchaseRequest extends AbstractRequest
{
    public function getData(): array
    {
        return $this->getParameters();
    }

    public function sendData($data): ResponseInterface
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token  = $_GET['token'] ?? null;
        $status = $_GET['status'] ?? 'success';

        // Check if this is a valid token
        $paymentData = $_SESSION['fake_payment'] ?? null;

        if (! $paymentData || $paymentData['token'] !== $token) {
            return new FakeCompletePurchaseResponse($this, [
                'success' => false,
                'message' => 'Invalid payment token',
            ]);
        }

        // Simulate payment completion based on status
        if ($status === 'success') {
            $transactionId = 'FAKE-TXN-' . uniqid('', true);

            // Clear session data
            unset($_SESSION['fake_payment']);

            return new FakeCompletePurchaseResponse($this, [
                'success'              => true,
                'transactionReference' => $transactionId,
                'amount'               => $paymentData['amount'],
                'currency'             => $paymentData['currency'],
                'message'              => 'Payment completed successfully',
            ]);
        }

        // Payment was cancelled or failed
        unset($_SESSION['fake_payment']);

        return new FakeCompletePurchaseResponse($this, [
            'success' => false,
            'message' => 'Payment was cancelled or failed',
        ]);
    }
}
