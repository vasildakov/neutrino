<?php

declare(strict_types=1);

namespace Neutrino\Authentication\Handler\Twitter;

use Psr\Http\Message\ResponseInterface;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class TwitterSuccessHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionInterface::class);
        dd($session?->get('twitter_token'));
    }
}
