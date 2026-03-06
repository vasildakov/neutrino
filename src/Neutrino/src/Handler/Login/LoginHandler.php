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

use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\InputFilter\InputFilterInterface;
use Mezzio\Authentication\Session\PhpSession;
use Mezzio\Authentication\UserInterface;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Session\SessionInterface;
use Neutrino\Repository\UserRepository;
use Neutrino\Service\Cart\CartService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function is_string;
use function str_starts_with;

final class LoginHandler implements RequestHandlerInterface, LoggerAwareInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly PhpSession $authentication,
        private readonly InputFilterInterface $inputFilter,
        private readonly CartService $cartService,
        private readonly UserRepository $userRepository,
    ) {
        $this->logger = new NullLogger();
    }

    private function redirect(string $url, int $status = 303): ResponseInterface
    {
        return new RedirectResponse($url, $status);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $error = null;

        $session = $request->getAttribute(SessionInterface::class);
        $guard   = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $flash   = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);

        $data  = $request->getParsedBody();
        $token = $data['csrf'] ?? '';
        if (! $guard->validateToken($token)) {
            $error = 'Error: Invalid CSRF token.';
        }

        $this->inputFilter->setData($data);

        if (! $this->inputFilter->isValid()) {
//            $messages = $this->inputFilter->getMessages();
//
//            if (isset($messages['email'])) {
//                $flash?->flash('field.email', reset($messages['email']));
//            }
//
//            if (isset($messages['password'])) {
//                $flash?->flash('field.password', reset($messages['password']));
//            }

            $error = 'Error: Invalid username or password.';
        }

        /** @var UserInterface|null $authUser */
        $authUser = $this->authentication->authenticate($request);

        if (! $authUser) {
            $error = 'Error: Invalid username or password.';
        }

        if (! $session) {
            $error = 'Error: Session is required for login.';
        }

        if ($error) {
            $flash?->flash('error', $error);
            $this->logger->error($error);
            return $this->redirect('/login', 303);
        }

        // Save old session ID (for cart migration)
        $oldSessionId = $session->getId();

        // Regenerate session (security best practice)
        $session->regenerate();

        // Store user identity in session
        $session->set('identity', $authUser->getIdentity());

        // Load actual User entity
        $user = $this->userRepository->find($authUser->getIdentity());

        if ($user) {
            $this->logger->info('User logged in: ' . $user->getEmail());
            // Merge guest cart into user cart
            $this->cartService->mergeSessionCartIntoUser($oldSessionId, $user);
        }

        // Default redirect based on user role scope
        $scope    = $authUser->getDetail('scope');
        $redirect = match ($scope) {
            'platform'  => '/platform',
            'dashboard' => '/dashboard',
            default     => '/',
        };

        // If a user tried to access the protected page (like checkout)
        $intended = $session->get('intended_url');
        $session->unset('intended_url');

        if (is_string($intended) && str_starts_with($intended, '/')) {
            $redirect = $intended;
        }

        return $this->redirect($redirect, 303);
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
