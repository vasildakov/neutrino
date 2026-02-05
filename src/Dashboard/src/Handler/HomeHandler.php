<?php

declare(strict_types=1);

namespace Dashboard\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diagnostics\Check;
use Laminas\Diagnostics\Runner;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HomeHandler implements RequestHandlerInterface
{
    public function __construct(private TemplateRendererInterface $template)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $runner = new Runner\Runner();

        $runner->addCheck(new Check\PhpVersion('8.1', '>'));
        $runner->addCheck(new Check\ClassExists(\Redis::class));
        $runner->addCheck(new Check\ClassExists(\Pdo::class));
        $runner->addCheck(new Check\Redis('redis', 6379));
        $results = $runner->run();

        //dd($results);

        $data = [];

        $content = $this->template->render('dashboard::home', $data);

        return new HtmlResponse($this->template->render('layout::dashboard', [
            'content' => $content,
            'data'    => $data,
        ]));
    }
}
