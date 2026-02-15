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

namespace Neutrino\Handler\Consent;

use Laminas\Diactoros\Response\JsonResponse;
use Neutrino\Service\ConsentService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Random\RandomException;

use function bin2hex;
use function random_bytes;
use function sprintf;

final class ConsentRevokeHandler implements RequestHandlerInterface
{
    public function __construct(private ConsentService $service)
    {
    }

    /**
     * @throws RandomException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $cookies   = $request->getCookieParams();
        $visitorId = $cookies['neutrino_vid'] ?? bin2hex(random_bytes(16));

        $purposes = [
            'essential'  => true,
            'functional' => false,
            'analytics'  => false,
            'marketing'  => false,
        ];

        $user        = $request->getAttribute('user');
        $subjectType = $user ? 'user' : 'visitor';
        $subjectId   = $user ? (string) ($user->getIdentity() ?? $visitorId) : $visitorId;

        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? null;
        $ua = $request->getHeaderLine('User-Agent') ?: null;

        $this->service->recordEvents($subjectType, $subjectId, $purposes, 'settings', $ip, $ua);
        $signed = $this->service->buildAndSignPayload($visitorId, $purposes);

        $response = new JsonResponse(['status' => 'ok', 'purposes' => $purposes]);

        $response = $response->withAddedHeader(
            'Set-Cookie',
            sprintf(
                '%s=%s; Path=/; Max-Age=%d; SameSite=Lax; Secure; HttpOnly',
                $this->service->cookieName(),
                $signed,
                31536000
            )
        );

        // Add consent flag cookie (NOT HttpOnly, so JS can check if consent was given)
        $response = $response->withAddedHeader(
            'Set-Cookie',
            sprintf(
                'neutrino_consent_given=1; Path=/; Max-Age=%d; SameSite=Lax; Secure',
                31536000
            )
        );

        return $response->withAddedHeader(
            'Set-Cookie',
            sprintf('neutrino_vid=%s; Path=/; Max-Age=%d; SameSite=Lax; Secure', $visitorId, 31536000)
        );
    }
}
