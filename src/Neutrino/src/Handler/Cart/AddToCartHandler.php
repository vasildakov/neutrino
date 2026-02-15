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
use Mezzio\Authentication\UserInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Session\SessionInterface;
use Neutrino\Domain\Billing\Plan;
use Neutrino\Service\Cart\CartService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

/**
 * Add to Cart Handler - Add items to a shopping cart
 */
readonly class AddToCartHandler implements RequestHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private EntityManagerInterface $em,
        private CartService $cartService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user    = $request->getAttribute(UserInterface::class);
        $session = $request->getAttribute(SessionInterface::class);

        // Create DTO from request
        try {
            $cartRequest = AddToCartRequest::fromRequest($request);
            $cartRequest->validate();
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        // Get or create cart
        try {
            $sessionId = $session ? $session->getId() : null;
            $cart      = $this->cartService->getCart($user, $sessionId);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Failed to get cart: ' . $e->getMessage()], 500);
        }

        // Add item to cart
        try {
            $replaced = false;

            if ($cartRequest->isPlan()) {
                $plan = $this->em->getRepository(Plan::class)->find($cartRequest->itemId);

                if (! $plan) {
                    return new JsonResponse(['error' => 'Plan not found'], 404);
                }

                // Check if cart already has a plan
                $hadPlan = $cart->hasPlan();

                $this->cartService->addPlan($cart, $plan, $cartRequest->billingPeriod);

                // If cart had a plan before, it was replaced
                $replaced = $hadPlan;
            } elseif ($cartRequest->isAddon()) {
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

            // Check if this is an AJAX request
            if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                // Get current plan info for frontend tracking
                $currentPlan = $cart->getPlanItem();
                $planInfo    = null;
                if ($currentPlan) {
                    $planInfo = [
                        'name'           => $currentPlan->getName(),
                        'billing_period' => $currentPlan->getBillingPeriod(),
                    ];
                }

                return new JsonResponse([
                    'success'     => true,
                    'cartCount'   => $cart->getTotalQuantity(),
                    'message'     => 'Item added to cart',
                    'replaced'    => $replaced,
                    'currentPlan' => $planInfo,
                ]);
            }

            // Regular request - redirect to cart
            return new RedirectResponse($this->router->generateUri('cart.view'));
        } catch (Exception $e) {
            if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return new JsonResponse([
                    'error' => $e->getMessage(),
                    'type'  => $e::class,
                ], 500);
            }

            return new RedirectResponse($this->router->generateUri('home'));
        }
    }
}
