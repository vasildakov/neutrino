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

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Domain\Analytics\AnalyticsEvent;
use Platform\Handler\Analytics\AnalyticsDashboard;
use Platform\Handler\Analytics\DashboardOverview;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class HomeHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private EntityManagerInterface $em
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $dashboard = new AnalyticsDashboard(
            overview: new DashboardOverview(
                totalVisits: 1000,
                uniqueVisitors: 350,
                bounceRate: 0.2,
                averageDurationMs: 100,
                conversionRate: 0.5
            )
        );
        //dd($dashboard);


        $analytics = $this->em->getRepository(AnalyticsEvent::class);
        $since     = new DateTimeImmutable('-30 days');

        $data = $analytics->aggregateVisitsByCityForMap($since);

        //dd(json_encode($data));

        // Get the authenticated user
        $user = $request->getAttribute(UserInterface::class);

//        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
//        $user    = $session->get(UserInterface::class);
//
//        $runner = new Runner\Runner();
//
//        $runner->addCheck(new Check\PhpVersion('8.1', '>'));
//        $runner->addCheck(new Check\ClassExists(Redis::class));
//        $runner->addCheck(new Check\ClassExists(PDO::class));
//        $runner->addCheck(new Check\Redis('redis', 6379));
//        $results = $runner->run();
//
//        //dd($results);
//
//        $data = [];

        $content = $this->template->render('platform::home', [
            'data' => $data,
        ]);

        return new HtmlResponse($this->template->render('layout::platform', [
            'content' => $content,
            'data'    => $data,
            'user'    => $user,
        ]));
    }
}
