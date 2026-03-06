<?php

declare(strict_types=1);

namespace Neutrino\Middleware;

use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Store the current URL in session before authentication check
 * so we can redirect back after login
 */
class StorePostAuthRedirectMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute(UserInterface::class);

        error_log("StorePostAuthRedirectMiddleware: User is " . ($user ? 'authenticated' : 'null'));

        // Only store redirect if user is NOT authenticated
        if ($user === null) {
            $session = $request->getAttribute(SessionInterface::class);
            if ($session) {
                // Store the current request URI
                $uri  = $request->getUri();
                $path = $uri->getPath();

                error_log("StorePostAuthRedirectMiddleware: Storing redirect path: " . $path);
                // Store in session for post-login redirect
                $session->set('post_auth_redirect', $path);
            }
        }

        return $handler->handle($request);
    }
}
