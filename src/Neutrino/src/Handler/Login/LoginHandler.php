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
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class LoginHandler implements RequestHandlerInterface
{
    public function __construct(private PhpSession $auth)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get Mezzio session from request attribute
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        $user = $this->auth->authenticate($request);
        if (! $user) {
            return new JsonResponse(
                [
                    'error' => 'Invalid Credentials'
                ],
                StatusCodeInterface::STATUS_UNAUTHORIZED
            );
        }

        $scope = $user->getDetail('scope');
        $uri = match ($scope) {
            'platform'  => '/platform',
            'dashboard' => '/dashboard',
            default => '/login',
        };

        return new RedirectResponse($uri);
    }
}
