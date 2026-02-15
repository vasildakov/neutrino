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

namespace Neutrino\Handler\Home;

use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Domain\Billing\Plan;
use Neutrino\Queue\Redis\RedisQueue;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function date;
use function time;

final readonly class HomePageHandler implements RequestHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private EntityManagerInterface $em,
        private TemplateRendererInterface $template,
        private RedisQueue $queue,
        private string $queueName
    ) {
    }

    /**
     * @throws JsonException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queuedAt = time();
        $this->queue->push($this->queueName, [
            'type'      => 'test',
            'timestamp' => $queuedAt,
        ]);

        // Fetch all plans from the database
        $plans = $this->em->getRepository(Plan::class)->findBy(
            ['isActive' => true],
            ['priceAmount' => 'ASC']
        );

        $data = [
            'plans'                 => $plans,
            'queueTimestamp'        => $queuedAt,
            'queueTimestampDisplay' => date('Y-m-d H:i:s', $queuedAt),
        ];

        $content = $this->template->render('sandbox::home', $data);

        return new HtmlResponse($this->template->render('layout::sandbox', [
            'content' => $content,
            'data'    => $data,
        ]));
    }
}
