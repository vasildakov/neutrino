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
namespace Neutrino\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class HomePageHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly EntityManagerInterface $em,
        private readonly ?TemplateRendererInterface $template = null
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];

        $content = $this->template->render('sandbox::home', $data);

        return new HtmlResponse($this->template->render('layout::sandbox', [
            'content' => $content,
            'data'    => $data,
        ]));
    }
}
