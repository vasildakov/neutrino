<?php

declare(strict_types=1);

namespace Neutrino\Middleware;

use Laminas\I18n\Translator\Translator;
use Locale;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LocalizationMiddleware implements MiddlewareInterface
{
    public const LOCALIZATION_ATTRIBUTE = 'locale';

    public const ROUTE_LOCALE_ATTRIBUTE = 'routeLocale';

    public function __construct(private readonly Translator $translator)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteResult|null $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        $params      = $routeResult?->getMatchedParams() ?? [];
        $routeLocale = $params['locale'] ?? null; // 'en'|'bg'|null

        // Route param wins
        if ($routeLocale === 'en') {
            $locale = 'en_US';
        } elseif ($routeLocale === 'bg') {
            $locale = 'bg_BG';
        } else {
            // fallback (first-time visitors)
            $locale = Locale::acceptFromHttp(
                $request->getServerParams()['HTTP_ACCEPT_LANGUAGE'] ?? 'bg-BG'
            ) ?: 'bg_BG';
        }

        Locale::setDefault($locale);
        $this->translator->setLocale($locale);

        return $handler->handle(
            $request
                ->withAttribute(self::LOCALIZATION_ATTRIBUTE, $locale)
                ->withAttribute(self::ROUTE_LOCALE_ATTRIBUTE, $routeLocale)
        );
    }
}
