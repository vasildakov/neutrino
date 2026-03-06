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

namespace Neutrino\Authentication\Handler\Linkedin;

use Laminas\Diactoros\Response\RedirectResponse;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionInterface;
use Neutrino\Authentication\Provider\LinkedinProvider;
use Neutrino\Authentication\Resolver\OAuthIdentity;
use Neutrino\Authentication\Resolver\RedirectResolver;
use Neutrino\Authentication\Resolver\UserResolverInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

use function hash_equals;
use function is_string;

final class LinkedinCallbackHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly LinkedinProvider $provider,
        private readonly LoggerInterface $logger,
        private readonly UserResolverInterface $userResolver,
        private readonly RedirectResolver $redirectResolver,
        private readonly string $startPath = '/auth/linkedin',
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionInterface::class);

        if (! $session instanceof SessionInterface) {
            $this->logger->error('Session missing in LinkedinCallbackHandler');

            return new RedirectResponse('/login?error=session', 303);
        }

        $query = $request->getQueryParams();

        if (isset($query['error']) && is_string($query['error'])) {
            $this->logger->warning('LinkedIn OAuth error', $query);

            return new RedirectResponse('/login?error=oauth', 303);
        }

        if (! isset($query['code'], $query['state'])) {
            return new RedirectResponse($this->startPath, 303);
        }

        if (! is_string($query['code']) || ! is_string($query['state'])) {
            return new RedirectResponse($this->startPath, 303);
        }

        $expectedState = (string) $session->get('linkedin_oauth_state');

        if ($expectedState === '' || ! hash_equals($expectedState, $query['state'])) {
            $this->logger->warning('LinkedIn OAuth state mismatch');

            return new RedirectResponse($this->startPath, 303);
        }

        try {
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $query['code'],
            ]);

            $owner = $this->provider->getResourceOwner($token);

            if (! $owner instanceof GenericResourceOwner) {
                throw new RuntimeException('Unexpected LinkedIn resource owner instance.');
            }

            $identity = OAuthIdentity::fromGenericResourceOwner($owner);

            $user = $this->userResolver->resolve($identity);

            $session->regenerate();

            $session->set(UserInterface::class, [
                'username' => $user->getIdentity(),
                'roles'    => $user->getRolesNames(),
                'details'  => $user->getDetails(),
            ]);

            $session->set('identity', (string) $user->getEmail());

            $redirect = $this->redirectResolver->resolve($user, $session);

            return new RedirectResponse($redirect, 303);
        } catch (IdentityProviderException $e) {
            $this->logger->error('LinkedIn OAuth provider error', [
                'exception' => $e,
            ]);

            return new RedirectResponse('/login?error=oauth_provider', 303);
        } catch (Throwable $e) {
            $this->logger->error('LinkedIn OAuth unexpected error', [
                'exception' => $e,
            ]);

            return new RedirectResponse('/login?error=oauth_unexpected', 303);
        } finally {
            $session->unset('linkedin_oauth_state');
        }
    }
}
