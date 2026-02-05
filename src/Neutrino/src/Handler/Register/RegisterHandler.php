<?php

declare(strict_types=1);

namespace Neutrino\Handler\Register;

use Fig\Http\Message\StatusCodeInterface;
use InvalidArgumentException;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authentication\Session\PhpSession;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RegisterHandler implements RequestHandlerInterface
{
    // create an account in neutrino
    // and create a tenant database for the account

    public function __construct(
        private readonly RegisterService $service,
        private readonly PhpSession $auth
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'GET') {
            return new JsonResponse(
                ['message' => 'Register endpoint is working'],
                StatusCodeInterface::STATUS_OK
            );
        }

        $data = $request->getParsedBody();
        if (!is_array($data)) {
            return new JsonResponse(
                ['error' => 'Invalid request body'],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        try {
            $user = $this->service->register(
                new RegisterInput(
                    (string) ($data['email'] ?? ''),
                    (string) ($data['password'] ?? '')
                )
            );
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        }

        // Auto-login
        $this->auth->authenticate($request);

        return new JsonResponse(
            [
                'success'  => true,
                'message'   => 'Account created successfully.',
                'redirect' => '/dashboard',
            ],
            StatusCodeInterface::STATUS_OK
        );
    }
}
