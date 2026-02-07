<?php

declare(strict_types=1);

namespace Neutrino\Handler;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class LogoutHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionInterface::class);
        $user = $request->getAttribute(UserInterface::class);

        // Remove the authentication session key that PhpSession uses
        $session->unset(UserInterface::class);

        // Clear all session data
        $session->clear();

        // Regenerate session ID to prevent session fixation
        $session->regenerate();


        return new RedirectResponse('/login');
    }
}
