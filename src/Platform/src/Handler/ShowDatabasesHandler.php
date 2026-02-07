<?php

namespace Platform\Handler;

use Platform\Service\Database\DatabaseStatsServiceInterface;
use Doctrine\DBAL\Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ShowDatabasesHandler implements RequestHandlerInterface
{
    private int $connectTimeoutSeconds = 2;

    public function __construct(
        private TemplateRendererInterface $template,
        private DatabaseStatsServiceInterface $service
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserInterface::class);

        $results = $this->service->execute();

        $content = $this->template->render('platform::databases', [
            'results' => $results->getElements(),
        ]);

        return new HtmlResponse($this->template->render('layout::platform', [
            'content' => $content,
            'data'    => $results,
            'user'    => $user,
        ]));
    }
}
