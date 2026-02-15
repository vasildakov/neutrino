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

namespace Neutrino\Handler\Cart;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Service\Cart\CartService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * View Cart Handler - Display cart contents
 */
readonly class ViewCartHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private CartService $cartService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user    = $request->getAttribute(UserInterface::class);
        $session = $request->getAttribute(SessionInterface::class);

        $sessionId = $session ? $session->getId() : null;
        $cart      = $this->cartService->getCart($user, $sessionId);

        $content = $this->template->render('sandbox::cart', [
            'cart'  => $cart,
            'items' => $cart->getItems(),
        ]);

        return new HtmlResponse(
            $this->template->render('sandbox::layout/sandbox', [
                'content' => $content,
            ])
        );
    }
}
