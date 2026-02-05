<?php

declare(strict_types=1);

namespace Neutrino\Handler\Login;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authentication\Session\PhpSession;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginHandler implements RequestHandlerInterface
{

    public function __construct(
        private readonly PhpSession $auth
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        //dd($request->getParsedBody());

        $user = $this->auth->authenticate($request);
        if (! $user) {
            return new JsonResponse(
                [
                    'error' => 'Invalid Credentials'
                ],
                StatusCodeInterface::STATUS_UNAUTHORIZED
            );
        }

        dd($user);

        return new JsonResponse(
            [
                'success'  => true,
                'redirect' => '/dashboard',
            ],
            StatusCodeInterface::STATUS_OK
        );
    }
}