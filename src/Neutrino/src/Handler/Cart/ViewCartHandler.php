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
use Mezzio\Session\SessionInterface;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Repository\UserRepository;
use Neutrino\Service\Cart\CartService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final readonly class ViewCartHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private CartService $cartService,
        private UserRepository $userRepository,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var SessionInterface|null $session */
        $session = $request->getAttribute(SessionInterface::class);

        if (! $session) {
            throw new RuntimeException('Session is required for cart.');
        }

        $locale    = $request->getAttribute('routeLocale') ?? 'en';
        $sessionId = $session->getId();
        $userId    = $session->get('identity');

        if ($userId) {
            // Find user by email (since session stores email as identity)
            $user = $this->userRepository->findOneByEmail($userId);

            if (! $user) {
                throw new RuntimeException('User not found.');
            }

            $cart = $this->cartService->getCartForUser($user);
        } else {
            $cart = $this->cartService->getCartForSession($sessionId);
        }

        $content = $this->template->render('sandbox::cart', [
            'cart'   => $cart,
            'items'  => $cart->getItems(),
            'locale' => $locale,
        ]);

        return new HtmlResponse(
            $this->template->render('sandbox::layout/sandbox', [
                'content' => $content,
            ])
        );
    }
}
