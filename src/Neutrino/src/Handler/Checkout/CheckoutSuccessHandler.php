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
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Domain\Billing\Plan;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class CheckoutSuccessHandler implements RequestHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private EntityManagerInterface $em,
        private TemplateRendererInterface $template
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get plan ID from query parameters
        $queryParams   = $request->getQueryParams();
        $planId        = $queryParams['plan'] ?? null;
        $billingPeriod = $queryParams['period'] ?? 'monthly';

        $plan = null;
        if ($planId) {
            $plan = $this->em->getRepository(Plan::class)->find($planId);
        }

        $data = [
            'plan'          => $plan,
            'billingPeriod' => $billingPeriod,
        ];

        $content = $this->template->render('checkout::success', $data);

        return new HtmlResponse($this->template->render('layout::sandbox', [
            'content' => $content,
            'data'    => $data,
        ]));
    }
}
