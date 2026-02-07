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
namespace Platform\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diagnostics\Check;
use Laminas\Diagnostics\Runner;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class HomeHandler implements RequestHandlerInterface
{
    public function __construct(private TemplateRendererInterface $template)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get the authenticated user
        //$user = $request->getAttribute(UserInterface::class);

        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $user = $session->get(UserInterface::class);

        $runner = new Runner\Runner();

        $runner->addCheck(new Check\PhpVersion('8.1', '>'));
        $runner->addCheck(new Check\ClassExists(\Redis::class));
        $runner->addCheck(new Check\ClassExists(\Pdo::class));
        $runner->addCheck(new Check\Redis('redis', 6379));
        $results = $runner->run();

        //dd($results);

        $data = [];

        $content = $this->template->render('platform::home', $data);

        return new HtmlResponse($this->template->render('layout::platform', [
            'content' => $content,
            'data'    => $data,
            'user'    => $user,
        ]));
    }
}
