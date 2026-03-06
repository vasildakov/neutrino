<?php

declare(strict_types=1);

namespace Neutrino\Middleware;

use Neutrino\Consent\CookieSigner;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function is_array;
use function is_string;

final class ConsentMiddleware implements MiddlewareInterface
{
    public function __construct(
        private CookieSigner $signer,
        private string $cookieName = 'neutrino_consent'
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cookies = $request->getCookieParams();
        $token   = $cookies[$this->cookieName] ?? null;

        $consent = [
            'v'        => 1,
            'ts'       => null,
            'purposes' => [],
            'vid'      => $cookies['neutrino_vid'] ?? null,
            'valid'    => false,
        ];

        if (is_string($token) && $token !== '') {
            $data = $this->signer->verify($token);
            if (is_array($data) && isset($data['purposes']) && is_array($data['purposes'])) {
                $consent = [
                    'v'        => (int) ($data['v'] ?? 1),
                    'ts'       => isset($data['ts']) ? (int) $data['ts'] : null,
                    'purposes' => $data['purposes'],
                    'vid'      => $data['vid'] ?? $consent['vid'],
                    'valid'    => true,
                ];
            }
        }

        return $handler->handle($request->withAttribute('consent', $consent));
    }
}
