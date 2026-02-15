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

namespace Neutrino\Handler\Register;

use Fig\Http\Message\StatusCodeInterface;
use InvalidArgumentException;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Authentication\Session\PhpSession;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function is_array;

readonly class RegisterHandler implements RequestHandlerInterface
{
    // create an account in neutrino
    // and create a tenant database for the account

    public function __construct(
        private RegisterService $service,
        private PhpSession $auth
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
        if (! is_array($data)) {
            return new JsonResponse(
                ['error' => 'Invalid request body'],
                StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }

        try {
            $user = $this->service->register(
                new RegisterInput(
                    (string) ($data['email'] ?? ''),
                    (string) ($data['password'] ?? ''),
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

        // Redirect to the platform/dashboard
        return new JsonResponse(
            [
                'success'  => true,
                'message'  => 'Account created successfully.',
                'redirect' => '/backoffice',
            ],
            StatusCodeInterface::STATUS_OK
        );
    }
}
