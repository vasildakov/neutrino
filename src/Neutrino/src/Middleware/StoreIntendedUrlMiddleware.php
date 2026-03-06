<?php

declare(strict_types=1);

namespace Neutrino\Middleware;

use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class StoreIntendedUrlMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute(SessionInterface::class);

        if ($session instanceof SessionInterface) {
            $uri  = $request->getUri();
            $path = $uri->getPath();
            if ($uri->getQuery() !== '') {
                $path .= '?' . $uri->getQuery();
            }

            $session->set('intended_url', $path);
        }

        return $handler->handle($request);
    }
}
