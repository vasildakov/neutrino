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
        private readonly TemplateRendererInterface $template,
        private readonly DatabaseStatsServiceInterface $service
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
