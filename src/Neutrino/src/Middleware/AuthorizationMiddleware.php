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
namespace Neutrino\Middleware;

use Mezzio\Authentication\UserInterface;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Neutrino\Security\Authorization\AuthorizationServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final readonly class AuthorizationMiddleware implements MiddlewareInterface
{
    public function __construct(private AuthorizationServiceInterface $authorization)
    {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $user = $request->getAttribute(UserInterface::class);
        assert($user instanceof UserInterface);

        $routeResult = $request->getAttribute(RouteResult::class);
        assert($routeResult instanceof RouteResult);

        $route = $routeResult->getMatchedRoute();
        assert($route instanceof Route);

        $roles     = $user->getRoles();
        $resource  = $route->getName();
        $privilege = null;

        if(!$this->authorization->isAllowed($roles, $resource, $privilege)) {
            throw new RuntimeException('Forbidden');
        }

        return $handler->handle($request);
    }
}
