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
use Mezzio\Session\SessionMiddleware;
use Neutrino\Domain\User\AuthenticatedUser;
use Neutrino\Queue\QueueInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class LoginHandler implements RequestHandlerInterface
{
    public function __construct(
        private PhpSession $auth,
        private QueueInterface $queue
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get Mezzio session from request attribute
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        /** @var UserInterface|null $user */
        $user = $this->auth->authenticate($request);
        if (! $user) {
            return new JsonResponse(
                [
                    'error' => 'Invalid Credentials',
                ],
                StatusCodeInterface::STATUS_UNAUTHORIZED
            );
        }

        $scope = $user->getDetail('scope');
        $uri   = match ($scope) {
            'platform'  => '/platform',
            'dashboard' => '/dashboard',
            default => '/login',
        };

        // Prepare user data for queue
        $userData = $user instanceof AuthenticatedUser
            ? $user->toArray()
            : [
                'identity' => $user->getIdentity(),
                'roles'    => $user->getRoles(),
                'details'  => $user->getDetails(),
            ];

        $this->queue->push('Neutrino.Queue.Test', [
            'user' => $userData,
        ]);

        return new RedirectResponse($uri);
    }
}
