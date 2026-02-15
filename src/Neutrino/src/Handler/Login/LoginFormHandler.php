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
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Mail\SendTestEmail;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginFormHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private SendTestEmail $sendTestEmail
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->sendTestEmail->send('vasildakov@gmail.com');

        // Get Mezzio session from request attribute
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        // just testing Mezzio session usage
        $session->set('test', 'some-session-value');

        $content = $this->template->render('sandbox::login', []);

        return new HtmlResponse($this->template->render('layout::sandbox', [
            'content' => $content,
            'data'    => [],
        ]));
    }
}
