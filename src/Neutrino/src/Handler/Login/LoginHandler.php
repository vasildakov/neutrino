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

namespace Neutrino\Handler\Login;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\Session\PhpSession;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionInterface;
use Neutrino\Repository\UserRepository;
use Neutrino\Service\Cart\CartService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

use function is_string;
use function str_starts_with;

final class LoginHandler implements RequestHandlerInterface, LoggerAwareInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly PhpSession $authentication,
        private readonly CartService $cartService,
        private readonly UserRepository $userRepository,
    ) {
        $this->logger = new NullLogger();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var SessionInterface|null $session */
        $session = $request->getAttribute(SessionInterface::class);

        /** @var UserInterface|null $authUser */
        $authUser = $this->authentication->authenticate($request);

        if (! $authUser) {
            $this->logger->error('Invalid credentials');
            return new JsonResponse(
                ['error' => 'Invalid credentials'],
                StatusCodeInterface::STATUS_UNAUTHORIZED
            );
        }

        if (! $session) {
            $this->logger->error('Session is required for login.');
            throw new RuntimeException('Session is required for login.');
        }

        // Save old session ID (for cart migration)
        $oldSessionId = $session->getId();

        // Regenerate session (security best practice)
        $session->regenerate();

        // Store user identity in session
        $session->set('user_id', $authUser->getIdentity());

        // Load actual User entity
        $user = $this->userRepository->find($authUser->getIdentity());

        if ($user) {
            $this->logger->info('User logged in: ' . $user->getEmail());
            // Merge guest cart into user cart
            $this->cartService->mergeSessionCartIntoUser($oldSessionId, $user);
        }

        // Default redirect
        $redirect = '/';

        // If a user tried to access the protected page (like checkout)
        $intended = $session->get('intended_url');
        $session->unset('intended_url');

        if (is_string($intended) && str_starts_with($intended, '/')) {
            $redirect = $intended;
        }

        return new RedirectResponse($redirect, 303);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
