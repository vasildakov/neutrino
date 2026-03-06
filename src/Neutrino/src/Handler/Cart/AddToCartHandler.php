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

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Router\RouterInterface;
use Mezzio\Session\SessionInterface;
use Neutrino\Domain\Billing\Plan;
use Neutrino\Repository\UserRepository;
use Neutrino\Service\Cart\CartService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

final readonly class AddToCartHandler implements RequestHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private EntityManagerInterface $em,
        private CartService $cartService,
        private UserRepository $userRepository,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionInterface::class);

        if (! $session) {
            return new JsonResponse(['error' => 'Session is required'], 400);
        }

        // Validate DTO
        try {
            $cartRequest = AddToCartRequest::fromRequest($request);
            $cartRequest->validate();
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        // Resolve cart ownership
        try {
            $sessionId = $session->getId();
            $userId    = $session->get('identity');

            if ($userId) {
                // Find user by email (since session stores email as identity)
                $user = $this->userRepository->findOneByEmail($userId);

                if (! $user) {
                    return new JsonResponse(['error' => 'User not found'], 404);
                }

                $cart = $this->cartService->getCartForUser($user);
            } else {
                $cart = $this->cartService->getCartForSession($sessionId);
            }
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Failed to resolve cart'], 500);
        }

        // Add item
        try {
            $replaced = false;

            if ($cartRequest->isPlan()) {
                $plan = $this->em->getRepository(Plan::class)->find($cartRequest->itemId);

                if (! $plan) {
                    return new JsonResponse(['error' => 'Plan not found'], 404);
                }

                $hadPlan = $cart->hasPlan();
                $this->cartService->addPlan($cart, $plan, $cartRequest->billingPeriod);
                $replaced = $hadPlan;
            }

            if ($cartRequest->isAddon()) {
                $data  = $request->getParsedBody();
                $name  = $data['name'] ?? 'Add-on';
                $price = (int) ($data['price'] ?? 0);

                $this->cartService->addAddon(
                    cart: $cart,
                    addonId: Uuid::fromString($cartRequest->itemId),
                    name: $name,
                    price: $price,
                    quantity: $cartRequest->quantity
                );
            }

            if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return new JsonResponse([
                    'success'   => true,
                    'cartCount' => $cart->getTotalQuantity(),
                    'replaced'  => $replaced,
                ]);
            }

            return new RedirectResponse($this->router->generateUri('cart.view'));
        } catch (Exception $e) {
            if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return new JsonResponse(['error' => $e->getMessage()], 500);
            }

            return new RedirectResponse($this->router->generateUri('home'));
        }
    }
}
