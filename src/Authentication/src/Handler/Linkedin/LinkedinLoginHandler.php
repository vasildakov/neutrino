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
use Mezzio\Session\SessionInterface;
use Neutrino\Authentication\Provider\LinkedinProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final readonly class LinkedinLoginHandler implements RequestHandlerInterface
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        private LinkedinProvider $provider,
        private array $scopes,
        private string $successRedirectPath = '/auth/linkedin/success',
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionInterface::class);

        if (! $session instanceof SessionInterface) {
            throw new RuntimeException(
                'SessionInterface missing. Ensure SessionMiddleware runs before this handler.'
            );
        }

        $returnTo = $this->successRedirectPath;
        $qp = $request->getQueryParams();
        if (isset($qp['returnTo']) && is_string($qp['returnTo']) && $qp['returnTo'] !== '') {
            $returnTo = $qp['returnTo'];
        }

        $session->set('linkedin_oauth_return_to', $returnTo);

        $authUrl = $this->provider->getAuthorizationUrl([
            'scope' => $this->scopes,
        ]);

        $session->set('linkedin_oauth_state', (string) $this->provider->getState());

        return new RedirectResponse($authUrl, 302);
    }
}
