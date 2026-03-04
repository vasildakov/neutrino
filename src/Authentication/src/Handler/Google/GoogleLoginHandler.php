<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Handler\Google;

use Laminas\Diactoros\Response\RedirectResponse;
use League\OAuth2\Client\Provider\Google;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final readonly class GoogleLoginHandler implements RequestHandlerInterface
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        private Google $provider,
        private array $scopes,
        private string $successRedirectPath = '/auth/google/success',
    ) {
    }


    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionInterface::class);
        if (! $session instanceof SessionInterface) {
            throw new RuntimeException('SessionInterface missing. Ensure SessionMiddleware runs before this handler.');
        }

        $returnTo = $this->successRedirectPath;
        $qp = $request->getQueryParams();
        if (isset($qp['returnTo']) && is_string($qp['returnTo']) && $qp['returnTo'] !== '') {
            $returnTo = $qp['returnTo'];
        }

        $session->set('google_oauth_return_to', $returnTo);

        $authUrl = $this->provider->getAuthorizationUrl([
            'scope' => $this->scopes, // provider will encode this correctly
        ]);


        // Save CSRF state
        $session->set('google_oauth_state', (string) $this->provider->getState());

        // IMPORTANT: redirect with provider-generated URL as-is
        return new RedirectResponse($authUrl, 302);
    }
}
