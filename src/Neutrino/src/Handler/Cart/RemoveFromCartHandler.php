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

use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Session\SessionInterface;
use Neutrino\Service\Cart\CartService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * Remove from Cart Handler
 */
readonly class RemoveFromCartHandler implements RequestHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private CartService $cartService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user    = $request->getAttribute(UserInterface::class);
        $session = $request->getAttribute(SessionInterface::class);
        $data    = $request->getParsedBody();

        $itemId = $data['item_id'] ?? null;

        if (! $itemId) {
            return new JsonResponse(['error' => 'Item ID required'], 400);
        }

        $sessionId = $session ? $session->getId() : null;
        $cart      = $this->cartService->getCart($user, $sessionId);

        try {
            $this->cartService->removeItem($cart, Uuid::fromString($itemId));

            if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return new JsonResponse([
                    'success'   => true,
                    'cartCount' => $cart->getTotalQuantity(),
                    'message'   => 'Item removed from cart',
                ]);
            }

            return new RedirectResponse($this->router->generateUri('cart.view'));
        } catch (Exception $e) {
            if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return new JsonResponse(['error' => $e->getMessage()], 500);
            }

            return new RedirectResponse($this->router->generateUri('cart.view'));
        }
    }
}
