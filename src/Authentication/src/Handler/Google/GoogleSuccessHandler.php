<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Handler\Google;

use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GoogleSuccessHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionInterface::class);

        $googleUser = $session->get('google_user');

        dd($googleUser);
    }
}
