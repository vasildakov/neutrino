<?php

declare(strict_types=1);

namespace Neutrino\Middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Translator\TranslatorInterface;
use Locale;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class SetLocaleMiddleware implements MiddlewareInterface
{
    private const REGEX_SET = '#^/lang/(?P<short>bg|en)(?:/|$)#';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly string $fallback = 'en_US'
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        // 1) If a user hits /lang/bg or /lang/en, set cookie + redirect back
        if (preg_match(self::REGEX_SET, $path, $m)) {
            $short = $m['short'];
            $full = $short === 'bg' ? 'bg_BG' : 'en_US';

            $back = $request->getHeaderLine('Referer') ?: '/';
            $response = new RedirectResponse($back);

            return $response->withAddedHeader(
                'Set-Cookie',
                sprintf('locale=%s; Path=/; Max-Age=%d; SameSite=Lax', $full, 60 * 60 * 24 * 365)
            );
        }

        // 2) Normal requests: read locale from cookie or Accept-Language
        $cookieLocale = $request->getCookieParams()['locale'] ?? null;

        $browser = Locale::acceptFromHttp(
            $request->getServerParams()['HTTP_ACCEPT_LANGUAGE'] ?? ''
        ) ?: $this->fallback;

        $locale = $cookieLocale ?: $browser;

        // Normalize short->full (in case the cookie stores "bg"/"en")
        $locale = match ($locale) {
            'bg' => 'bg_BG',
            'en' => 'en_US',
            default => $locale,
        };

        Locale::setDefault($locale);
        $this->translator->setLocale($locale);

        return $handler->handle($request->withAttribute('locale', $locale));
    }
}
