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

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Router\RouterInterface;
use Neutrino\Service\Payment\PaymentServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function error_log;

final readonly class CheckoutReturnHandler implements RequestHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private EntityManagerInterface $em,
        private PaymentServiceInterface $paymentService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get query parameters
        $queryParams   = $request->getQueryParams();
        $planId        = $queryParams['plan'] ?? null;
        $billingPeriod = $queryParams['period'] ?? 'monthly';

        try {
            // Complete the purchase
            $response = $this->paymentService->completePurchase([
                'amount'   => $queryParams['amount'] ?? null,
                'currency' => 'USD',
            ]);

            if ($response->isSuccessful()) {
                $transactionId = $response->getTransactionReference();
                $payerId       = $queryParams['PayerID'] ?? null;

                error_log("PayPal payment completed successfully. Transaction: $transactionId, Payer: $payerId");

                // TODO: Save transaction to database
                // TODO: Create subscription record
                // TODO: Send confirmation email

                // Redirect to success page
                return new RedirectResponse(
                    $this->router->generateUri('checkout.success')
                    . '?plan=' . $planId
                    . '&period=' . $billingPeriod
                    . '&transaction=' . $transactionId
                );
            }

            // Payment failed
            error_log('PayPal payment completion failed: ' . $response->getMessage());
            return new RedirectResponse(
                $this->router->generateUri('checkout')
                . '?plan=' . $planId
                . '&period=' . $billingPeriod
                . '&error=payment_failed'
            );
        } catch (Exception $e) {
            error_log('PayPal return handler exception: ' . $e->getMessage());
            return new RedirectResponse(
                $this->router->generateUri('checkout')
                . '?plan=' . $planId
                . '&period=' . $billingPeriod
                . '&error=payment_error'
            );
        }
    }
}
