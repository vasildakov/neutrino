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
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Domain\Billing\Plan;
use Neutrino\Service\Payment\PaymentServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function error_log;
use function print_r;
use function sprintf;
use function uniqid;

final class CheckoutProcessHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly EntityManagerInterface $em,
        private readonly TemplateRendererInterface $template,
        private readonly PaymentServiceInterface $paymentService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // DEBUGGING - Remove this later
        error_log('============================================');
        error_log('CheckoutProcessHandler::handle() CALLED');
        error_log('Method: ' . $request->getMethod());
        error_log('URI: ' . $request->getUri()->getPath());
        error_log('============================================');

        // Get the authenticated user (optional - may be null for guests)
        $user = $request->getAttribute(UserInterface::class);

        // Get POST data
        $data = $request->getParsedBody();

        // DEBUGGING
        error_log('POST Data received: ' . print_r($data, true));

        // Extract form fields
        $planId        = $data['plan_id'] ?? null;
        $billingPeriod = $data['billing_period'] ?? 'monthly';

        // DEBUGGING
        error_log('Plan ID: ' . ($planId ?? 'NULL'));
        error_log('Billing Period: ' . $billingPeriod);

        // Billing details
        $firstName = $data['first_name'] ?? null;
        $lastName  = $data['last_name'] ?? null;
        $email     = $data['email'] ?? null;
        $company   = $data['company'] ?? null;
        $address   = $data['address'] ?? null;
        $address2  = $data['address2'] ?? null;
        $city      = $data['city'] ?? null;
        $state     = $data['state'] ?? null;
        $zip       = $data['zip'] ?? null;
        $country   = $data['country'] ?? null;
        $phone     = $data['phone'] ?? null;

        // Payment information
        $cardName   = $data['card_name'] ?? null;
        $cardNumber = $data['card_number'] ?? null;
        $expiryDate = $data['expiry_date'] ?? null;
        $cvv        = $data['cvv'] ?? null;

        $termsAccepted = isset($data['terms']);

        // DEBUGGING: Log all field values
        error_log('Validation check:');
        error_log('  planId: ' . ($planId ? 'OK' : 'MISSING'));
        error_log('  firstName: ' . ($firstName ? 'OK' : 'MISSING'));
        error_log('  lastName: ' . ($lastName ? 'OK' : 'MISSING'));
        error_log('  email: ' . ($email ? 'OK' : 'MISSING'));
        error_log('  address: ' . ($address ? 'OK' : 'MISSING'));
        error_log('  city: ' . ($city ? 'OK' : 'MISSING'));
        error_log('  zip: ' . ($zip ? 'OK' : 'MISSING'));
        error_log('  country: ' . ($country ? 'OK' : 'MISSING'));
        error_log('  cardName: ' . ($cardName ? 'OK' : 'MISSING'));
        error_log('  cardNumber: ' . ($cardNumber ? 'OK' : 'MISSING'));
        error_log('  expiryDate: ' . ($expiryDate ? 'OK' : 'MISSING'));
        error_log('  cvv: ' . ($cvv ? 'OK' : 'MISSING'));
        error_log('  termsAccepted: ' . ($termsAccepted ? 'OK' : 'MISSING'));

        // Basic validation - required fields
        if (
            ! $planId || ! $firstName || ! $lastName || ! $email || ! $address ||
            ! $city || ! $zip || ! $country || ! $cardName || ! $cardNumber ||
            ! $expiryDate || ! $cvv || ! $termsAccepted
        ) {
            error_log('VALIDATION FAILED - redirecting back to checkout');
            // Redirect back with error
            return new RedirectResponse(
                $this->router->generateUri('checkout') . '?plan=' . $planId . '&period=' . $billingPeriod . '&error=missing_fields'
            );
        }

        // Get the plan
        $plan = $this->em->getRepository(Plan::class)->find($planId);

        if (! $plan) {
            return new RedirectResponse($this->router->generateUri('home'));
        }

        // For testing: Skip payment processing and go directly to success
        // Generate a fake transaction ID
        $transactionId = 'FAKE-TXN-' . uniqid('', true);

        // Calculate total for logging
        $subtotal = $billingPeriod === 'yearly' ? $plan->getPriceYearly() : $plan->getPriceMonthly();
        $tax      = $subtotal * 0.20;
        $total    = $subtotal + $tax;

        // Log the simulated payment
        error_log(sprintf(
            'Simulated payment: Plan=%s, Period=%s, Amount=$%.2f, Transaction=%s',
            $plan->getName(),
            $billingPeriod,
            $total,
            $transactionId
        ));

        // Build redirect URL
        $redirectUrl = $this->router->generateUri('checkout.success')
            . '?plan=' . $planId
            . '&period=' . $billingPeriod
            . '&transaction=' . $transactionId;

        // DEBUGGING
        error_log('Redirecting to: ' . $redirectUrl);
        error_log('============================================');

        // Redirect directly to the success page
        return new RedirectResponse($redirectUrl);
    }
}
