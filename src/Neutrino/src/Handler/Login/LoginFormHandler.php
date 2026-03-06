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

namespace Neutrino\Handler\Login;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Csrf\CsrfMiddleware;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Mail\SendTestEmail;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class LoginFormHandler implements RequestHandlerInterface, LoggerAwareInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly TemplateRendererInterface $template,
        private readonly SendTestEmail $sendTestEmail
    ) {
        $this->logger = new NullLogger();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $guard   = $request->getAttribute(CsrfMiddleware::GUARD_ATTRIBUTE);
        $flash   = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);

        //$this->sendTestEmail->send('vasildakov@gmail.com');

        $locale = $request->getAttribute('routeLocale', 'en');

        // just testing Mezzio session usage
        //$session->set('test', 'some-session-value');


        $this->logger->info('Login form shown.');

        $token = $guard->generateToken();
        $error = $flash?->getFlash('error') ?? null;

        return new HtmlResponse($this->template->render(
            'sandbox::login',
            [
                'layout' => 'layout::sandbox',
                'title'  => 'Sign In',
                'error'  => $error,
                'csrf'   => $token,
            ],
        ));
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
