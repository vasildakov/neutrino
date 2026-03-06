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

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class FakePaymentHandler implements RequestHandlerInterface
{
    public function __construct(
        private ?TemplateRendererInterface $template = null
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $session     = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        $token       = $queryParams['token'] ?? null;
        $amount      = $queryParams['amount'] ?? 0;
        $currency    = $queryParams['currency'] ?? 'USD';
        $description = $queryParams['description'] ?? 'Payment';

        $paymentData = $session?->get('fake_payment');

        $data = [
            'token'       => $token,
            'amount'      => $amount,
            'currency'    => $currency,
            'description' => $description,
            'valid'       => $paymentData && $paymentData['token'] === $token,
        ];

        $content = $this->template->render('checkout::fake-payment', $data);

        return new HtmlResponse($this->template->render('layout::sandbox', [
            'content' => $content,
            'data'    => $data,
        ]));
    }
}
