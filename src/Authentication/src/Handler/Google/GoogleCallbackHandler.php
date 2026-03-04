<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Handler\Google;

use Laminas\Diactoros\Response\RedirectResponse;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

use function hash_equals;
use function is_string;
use function str_starts_with;

final class GoogleCallbackHandler implements RequestHandlerInterface
{
    public function __construct(
        private Google $provider,
        private LoggerInterface $logger,
        private GoogleUserResolverInterface $userResolver,
        private string $startPath = '/auth/google',
        private string $defaultReturnTo = '/auth/google/success',
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionInterface::class);

        if (! $session instanceof SessionInterface) {
            $this->logger->error('SessionInterface attribute missing in GoogleCallbackHandler.');
            return new RedirectResponse('/login?error=session_missing', 303);
        }

        $query = $request->getQueryParams();

        // Google error response
        if (isset($query['error']) && is_string($query['error']) && $query['error'] !== '') {
            $this->logger->warning('Google OAuth error response', ['error' => $query['error'], 'query' => $query]);
            return new RedirectResponse('/login?error=oauth_' . $query['error'], 303);
        }

        // Direct hit to callback (or missing params) -> restart OAuth flow
        if (
            ! isset($query['code'], $query['state'])
            || ! is_string($query['code']) || $query['code'] === ''
            || ! is_string($query['state']) || $query['state'] === ''
        ) {
            return new RedirectResponse($this->startPath, 303);
        }

        // Validate state AFTER ensuring state exists
        $expectedState = (string) ($session->get('google_oauth_state') ?? '');
        $givenState    = (string) $query['state'];

        if ($expectedState === '' || ! hash_equals($expectedState, $givenState)) {
            $this->logger->warning('Google OAuth state mismatch.', [
                'expected' => $expectedState,
                'given' => $givenState,
                'host' => $request->getUri()->getHost(),
            ]);

            return new RedirectResponse($this->startPath, 303);
        }

        try {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $query['code'],
            ]);

            $owner = $this->provider->getResourceOwner($token);

            $googleId = (string) $owner->getId();
            $email    = (string) ($owner->getEmail() ?? '');
            $name     = (string) ($owner->getName() ?? '');

            $session->set('google_user', $owner->toArray());

            $result = $this->userResolver->resolve(
                googleId: $googleId,
                email: $email,
                name: $name,
                accessToken: $token->getToken(),
                refreshToken: $token->getRefreshToken(),
                expires: $token->getExpires(),
            );

            if (! $result->isSuccess()) {
                return new RedirectResponse('/login?error=oauth_denied', 303);
            }

            $session->set('user_id', $result->userId());

            // ---- FIX: sanitize returnTo so it can never be callback / auth route / absolute URL ----
            $returnTo = (string) ($session->get('google_oauth_return_to') ?? $this->defaultReturnTo);

            // Allow only local paths
            if ($returnTo === '' || ! str_starts_with($returnTo, '/') || str_starts_with($returnTo, '//')) {
                $returnTo = $this->defaultReturnTo;
            }

            // Prevent loops: never redirect back into the OAuth endpoints
            if (str_starts_with($returnTo, '/auth/google')) {
                $returnTo = $this->defaultReturnTo;
            }
            // --------------------------------------------------------------------------------------

            return new RedirectResponse($returnTo, 303);
        } catch (IdentityProviderException $e) {
            $this->logger->error('Google OAuth IdentityProviderException', ['exception' => $e]);
            return new RedirectResponse('/login?error=oauth_provider', 303);
        } catch (\Throwable $e) {
            $this->logger->error('Google OAuth unexpected error', ['exception' => $e]);
            return new RedirectResponse('/login?error=oauth_unexpected', 303);
        } finally {
            $session->unset('google_oauth_state');
        }
    }
}