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
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Router\RouterInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Service\Payment\OmnipayCardMapper;
use Neutrino\Service\Payment\PaymentServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class CheckoutProcessHandler implements RequestHandlerInterface, LoggerAwareInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly RouterInterface $router,
        private readonly EntityManagerInterface $em,
        private readonly TemplateRendererInterface $template,
        private readonly PaymentServiceInterface $paymentService,
        private readonly CheckoutForm $form
    ) {
        $this->logger = new NullLogger();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionInterface::class);
        $guard   = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);

        // Get the authenticated user (optional - may be null for guests)
        $userInterface = $request->getAttribute(UserInterface::class);

        $data = $request->getParsedBody() ?? [];

        $token = $data['csrf'] ?? '';
        if (! $guard->validateToken($token)) {
            return new EmptyResponse(412); // Precondition failed
        }

        $this->logger->info('CSRF token validated');

        $this->form->setData($data);
        if (! $this->form->isValid()) {
            $this->logger->info('Form data is invalid:');
        }
        $this->logger->info('Form data is valid');

        $inputFilter = $this->form->getInputFilter();
        $card        = OmnipayCardMapper::fromCheckout($inputFilter->getValues());

        $response = $this->paymentService->purchase([
            'amount'   => '10.00', // @todo: get from cart
            'currency' => 'EUR', // @todo: get from cart
            'card'     => $card,
        ]);

        if ($response->isSuccessful()) {
            // Payment was successful
            $this->logger->info('Payment was successful');
            $redirect = $this->router->generateUri('checkout.success')
                . '?plan=' . $inputFilter->getValue('planId')
                . '&period=' . $inputFilter->getValue('billingPeriod')
                . '&transaction=' . $response->getTransactionReference();

            // Redirect directly to the success page
            return new RedirectResponse($redirect);
        } elseif ($response->isRedirect()) {
            // Redirect to offsite payment gateway
            return new JsonResponse([
                'success' => false,
                'message' => 'Redirecting to payment gateway...',
            ]);
        } else {
            $this->logger->info('Payment failed: ' . $response->getMessage());
            return new JsonResponse([
                'success' => false,
                'message' => $response->getMessage(),
            ]);
        }
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
