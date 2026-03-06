<?php

declare(strict_types=1);

namespace Neutrino\Handler;

use Laminas\Diactoros\Response\RedirectResponse;
use Locale;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function is_string;
use function str_starts_with;
use function strtolower;

final readonly class RedirectHandler implements RequestHandlerInterface
{
    public function __construct(private UrlHelper $urlHelper)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // If some route already provided a locale, honor it (avoid loops)
        $routeLocale = $request->getAttribute('locale');
        if (is_string($routeLocale) && ($routeLocale === 'en' || $routeLocale === 'bg')) {
            $url = $this->urlHelper->generate('home', ['locale' => $routeLocale]);
            return new RedirectResponse($url, 301);
        }

        // 1) Restore from cookie
        $cookies      = $request->getCookieParams();
        $cookieLocale = $cookies['locale'] ?? null;

        // 2) Fallback to Accept-Language
        $headerLocale = Locale::acceptFromHttp(
            $request->getServerParams()['HTTP_ACCEPT_LANGUAGE'] ?? 'bg-BG'
        );

        $resolved = $cookieLocale ?: $headerLocale;

        // 3) Normalize to route param (en|bg)
        $lang = match (true) {
            is_string($resolved) && str_starts_with(strtolower($resolved), 'en') => 'en',
            default => 'bg', // aligns with your route default
        };

        // 4) Redirect to localized home
        $url = $this->urlHelper->generate('home', ['locale' => $lang]);

        return new RedirectResponse($url, 301);
    }
}
