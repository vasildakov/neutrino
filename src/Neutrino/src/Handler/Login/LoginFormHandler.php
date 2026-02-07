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

use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\HtmlResponse;
class LoginFormHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template
    ){
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get Mezzio session from request attribute
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        // just testing Mezzio session usage
        $session->set('test', 'some-session-value');

        return new HtmlResponse(
            $this->template->render('neutrino::login')
        );
    }
}
