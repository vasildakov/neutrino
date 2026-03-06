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

namespace Neutrino\Authentication\Handler\Google;

use Laminas\Diactoros\Response\RedirectResponse;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use Mezzio\Session\SessionInterface;
use Neutrino\Authentication\Resolver\OAuthIdentity;
use Neutrino\Authentication\Resolver\RedirectResolver;
use Neutrino\Authentication\Resolver\UserResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

use function hash_equals;
use function is_string;

final class GoogleCallbackHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly Google                $provider,
        private readonly LoggerInterface       $logger,
        private readonly UserResolverInterface $userResolver,
        private readonly RedirectResolver      $redirectResolver,
        private readonly string                $startPath = '/auth/google',
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionInterface::class);

        if (! $session instanceof SessionInterface) {
            $this->logger->error('Session missing in GoogleCallbackHandler');
            return new RedirectResponse('/login?error=session', 303);
        }

        $query = $request->getQueryParams();

        // Provider error
        if (isset($query['error']) && is_string($query['error'])) {
            $this->logger->warning('Google OAuth error', $query);
            return new RedirectResponse('/login?error=oauth', 303);
        }

        if (!isset($query['code'], $query['state'])) {
            return new RedirectResponse($this->startPath, 303);
        }

        if (!is_string($query['code']) || !is_string($query['state'])) {
            return new RedirectResponse($this->startPath, 303);
        }

        $expectedState = (string) $session->get('google_oauth_state');

        if ($expectedState === '' || !hash_equals($expectedState, $query['state'])) {
            $this->logger->warning('OAuth state mismatch');
            return new RedirectResponse($this->startPath, 303);
        }

        try {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $query['code'],
            ]);

            /** @var GoogleUser $owner */
            $owner = $this->provider->getResourceOwner($token);

            $identity = OAuthIdentity::fromGoogleUser($owner);

            $user = $this->userResolver->resolve($identity);

            // Regenerate session for security (before setting auth data)
            $session->regenerate();

            // Set authentication data (this is what PhpSession reads)
            $session->set(\Mezzio\Authentication\UserInterface::class, [
                'username' => $user->getIdentity(),
                'roles'    => $user->getRolesNames(),
                'details'  => $user->getDetails(),
            ]);

            // Store identity separately (for consistency with regular login)
            $session->set('identity', $user->getIdentity());

            $this->logger->info('User authenticated via Google OAuth', [
                'email' => $user->getEmail(),
                'identity' => $user->getIdentity(),
            ]);

            // Resolve redirect (pass session to check for intended URL)
            $redirect = $this->redirectResolver->resolve($user, $session);

            return new RedirectResponse($redirect, 303);

        } catch (IdentityProviderException $e) {
            $this->logger->error('OAuth provider error', ['exception' => $e]);
            return new RedirectResponse('/login?error=oauth_provider', 303);
        } catch (\Throwable $e) {
            $this->logger->error('OAuth unexpected error', ['exception' => $e]);
            return new RedirectResponse('/login?error=oauth_unexpected', 303);
        } finally {
            $session->unset('google_oauth_state');
        }
    }
}
