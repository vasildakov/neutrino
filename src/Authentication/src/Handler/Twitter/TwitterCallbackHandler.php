<?php

declare(strict_types = 1);

namespace Neutrino\Authentication\Handler\Twitter;

use Laminas\Diactoros\Response\RedirectResponse;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Smolblog\OAuth2\Client\Provider\Twitter;

use function hash_equals;
use function is_string;
use function str_starts_with;

final readonly class TwitterCallbackHandler implements RequestHandlerInterface
{
    public function __construct(
        private Twitter $provider,
        private LoggerInterface $logger,
        private string $startPath = '/auth/twitter',
        private string $defaultReturnTo = '/auth/twitter/success',
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionInterface::class);
        if (! $session instanceof SessionInterface) {
            return new RedirectResponse('/login?error=session_missing', 303);
        }

        $q = $request->getQueryParams();

        if (isset($q['error']) && is_string($q['error']) && $q['error'] !== '') {
            return new RedirectResponse('/login?error=twitter_' . $q['error'], 303);
        }

        if (
            ! isset($q['code'], $q['state'])
            || ! is_string($q['code']) || $q['code'] === ''
            || ! is_string($q['state']) || $q['state'] === ''
        ) {
            return new RedirectResponse($this->startPath, 303);
        }

        $expectedState = (string) ($session->get('twitter_oauth_state') ?? '');
        if ($expectedState === '' || ! hash_equals($expectedState, (string) $q['state'])) {
            $this->logger->warning('Twitter OAuth state mismatch.');
            return new RedirectResponse($this->startPath, 303);
        }

        $codeVerifier = (string) ($session->get('twitter_oauth_code_verifier') ?? '');
        if ($codeVerifier === '') {
            $this->logger->warning('Twitter OAuth missing PKCE code_verifier.');
            return new RedirectResponse($this->startPath, 303);
        }

        try {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $q['code'],
                'code_verifier' => $codeVerifier, // PKCE
            ]);

            // For debugging: store token bits
            $session->set('twitter_token', [
                'access_token' => $token->getToken(),
                'refresh_token' => $token->getRefreshToken(),
                'expires' => $token->getExpires(),
            ]);

            $returnTo = (string) ($session->get('twitter_oauth_return_to') ?? $this->defaultReturnTo);

            // sanitize returnTo
            if ($returnTo === '' || ! str_starts_with($returnTo, '/') || str_starts_with($returnTo, '//') || str_starts_with($returnTo, '/auth/twitter')) {
                $returnTo = $this->defaultReturnTo;
            }

            return new RedirectResponse($returnTo, 303);
        } catch (IdentityProviderException $e) {
            $this->logger->error('Twitter OAuth IdentityProviderException', ['exception' => $e]);
            return new RedirectResponse('/login?error=twitter_oauth', 303);
        } finally {
            $session->unset('twitter_oauth_state');
            $session->unset('twitter_oauth_code_verifier');
        }
    }
}