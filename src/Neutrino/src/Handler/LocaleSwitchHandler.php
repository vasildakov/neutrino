<?php

declare(strict_types=1);

namespace Neutrino\Handler;

use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function in_array;
use function sprintf;

final readonly class LocaleSwitchHandler implements RequestHandlerInterface
{
    public function __construct(private UrlHelper $urlHelper)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var RouteResult|null $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        $lang = $routeResult?->getMatchedParams()['locale'] ?? 'bg';
        if (! in_array($lang, ['en', 'bg'], true)) {
            $lang = 'bg';
        }

        // redirect to /en or /bg (your home route is /:locale[/])
        $url = $this->urlHelper->generate('home', ['locale' => $lang]);

        $response = new RedirectResponse($url, 302);

        // persist preference (optional)
        return $response->withAddedHeader(
            'Set-Cookie',
            sprintf('locale=%s; Path=/; Max-Age=%d; SameSite=Lax', $lang, 60 * 60 * 24 * 365)
        );
    }
}
