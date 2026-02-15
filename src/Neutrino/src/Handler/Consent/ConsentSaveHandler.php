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
use function is_array;
use function json_decode;
use function random_bytes;
use function sprintf;

final class ConsentSaveHandler implements RequestHandlerInterface
{
    public function __construct(private ConsentService $service)
    {
    }

    /**
     * @throws RandomException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode((string) $request->getBody(), true);
        if (! is_array($data) || ! isset($data['purposes']) || ! is_array($data['purposes'])) {
            return new JsonResponse(['error' => 'Invalid payload.'], 400);
        }

        // Ensure essential is always true
        $purposes              = $data['purposes'];
        $purposes['essential'] = true;

        $cookies   = $request->getCookieParams();
        $visitorId = $cookies['neutrino_vid'] ?? bin2hex(random_bytes(16)); // 32 hex chars

        // subject: user or visitor
        $user        = $request->getAttribute('user'); // your auth user, if present
        $subjectType = $user ? 'user' : 'visitor';
        $subjectId   = $user ? (string) ($user->getIdentity() ?? $user->getId() ?? $visitorId) : $visitorId;

        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? null;
        $ua = $request->getHeaderLine('User-Agent') ?: null;

        $this->service->recordEvents($subjectType, $subjectId, $purposes, 'banner', $ip, $ua);

        $signed = $this->service->buildAndSignPayload($visitorId, $purposes);

        // Return cookie headers
        $response = new JsonResponse(['status' => 'ok', 'purposes' => $purposes]);

        // consent cookie (NOT HttpOnly, so JS can check if consent was given)
        $response = $response->withAddedHeader(
            'Set-Cookie',
            sprintf(
                '%s=%s; Path=/; Max-Age=%d; SameSite=Lax; Secure',
                $this->service->cookieName(),
                $signed,
                31536000
            )
        );

        // visitor id cookie (not HttpOnly; UI may need it)
        return $response->withAddedHeader(
            'Set-Cookie',
            sprintf('neutrino_vid=%s; Path=/; Max-Age=%d; SameSite=Lax; Secure', $visitorId, 31536000)
        );
    }
}
