<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Handler\Twitter;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Smolblog\OAuth2\Client\Provider\Twitter;

use function base64_encode;
use function hash;
use function is_string;
use function random_bytes;
use function rtrim;
use function str_starts_with;
use function strtr;

final readonly class TwitterLoginHandler implements RequestHandlerInterface
{
    public function __construct(
        private Twitter $provider,
        private array $scopes,
        private string $successRedirectPath = '/auth/twitter/success',
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionInterface::class);
        if (! $session instanceof SessionInterface) {
            return new RedirectResponse('/login?error=session_missing', 303);
        }

        // returnTo: only allow local paths, avoid loops and open redirects
        $returnTo = $this->successRedirectPath;
        $qp = $request->getQueryParams();
        if (isset($qp['returnTo']) && is_string($qp['returnTo'])) {
            $candidate = $qp['returnTo'];
            if (str_starts_with($candidate, '/')
                && ! str_starts_with($candidate, '/auth/twitter')
                && ! str_starts_with($candidate, '//')
            ) {
                $returnTo = $candidate;
            }
        }
        $session->set('twitter_oauth_return_to', $returnTo);

        // PKCE
        $codeVerifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        $session->set('twitter_oauth_code_verifier', $codeVerifier);

        $authUrl = $this->provider->getAuthorizationUrl([
            'scope' => $this->scopes,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        $session->set('twitter_oauth_state', (string) $this->provider->getState());

        return new RedirectResponse($authUrl, 302);
    }
}