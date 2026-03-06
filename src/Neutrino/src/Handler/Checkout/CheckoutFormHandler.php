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
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Router\RouterInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Repository\UserRepository;
use Neutrino\Service\Cart\CartService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final readonly class CheckoutFormHandler implements RequestHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private TemplateRendererInterface $template,
        private CartService $cartService,
        private UserRepository $userRepository,
        private CheckoutForm $form,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var SessionInterface|null $session */
        $session = $request->getAttribute(SessionInterface::class);
        $guard   = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);

        if (! $session) {
            throw new RuntimeException('Session is required.');
        }

        $locale = $request->getAttribute('routeLocale', 'en');

        $userId = $session->get('identity');

        // Not logged in, redirect to the login
        if (! $userId) {
            $session->set('intended_url', $request->getUri()->getPath());
            return new RedirectResponse('/login');
        }

        // Find user by email (since session stores email as identity)
        $user = $this->userRepository->findOneByEmail($userId);

        if (! $user) {
            // Invalid session state
            $session->unset('identity');
            return new RedirectResponse('/login');
        }

        // Checkout MUST use user cart
        $cart = $this->cartService->getCartForUser($user);

        if ($cart->isEmpty()) {
            return new RedirectResponse(
                $this->router->generateUri('home', ['locale' => $locale])
            );
        }

        $planItem = $cart->getPlanItem();

        if (! $planItem) {
            return new RedirectResponse(
                $this->router->generateUri('home', ['locale' => $locale])
            );
        }

        $token = $guard->generateToken();

        $data = [
            'user'     => $user,
            'form'     => $this->form,
            'cart'     => $cart,
            'items'    => $cart->getItems(),
            'planItem' => $planItem,
            'csrf'     => $token,
            'locale'   => $locale,
        ];

        $content = $this->template->render('checkout::checkout', $data);

        return new HtmlResponse(
            $this->template->render('layout::sandbox', [
                'content' => $content,
                'data'    => $data,
            ])
        );
    }
}
