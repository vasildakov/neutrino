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

use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Domain\User\User;
use Neutrino\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ListAccounts implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private EntityManagerInterface $em
    )
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->em->getRepository(User::class)->findAll();
        $data = [];

        $content = $this->template->render('platform::account/list', [
            'users' => $users,
        ]);

        return new HtmlResponse(
            $this->template->render('layout::platform', [
            'content' => $content,
        ]));
    }
}
