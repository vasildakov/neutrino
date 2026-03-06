<?php

declare(strict_types=1);

namespace Platform\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\TubeName;
use Pheanstalk\Values\TubeStats;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function assert;

final readonly class QueueHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private Pheanstalk $pheanstalk,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $stats = $this->pheanstalk->stats();

        /** @var array<string, TubeStats> $queues */
        $queues = [];

        /** @var list<string> $tubeList */
        $tubeList = $this->pheanstalk->listTubes();

        foreach ($tubeList as $tubeName) {
            assert($tubeName instanceof TubeName);

            $tubeStats = $this->pheanstalk->statsTube($tubeName);
            assert($tubeStats instanceof TubeStats);

            $queues[$tubeName->value] = $tubeStats;
        }

        $data = [
            'beanstalkd' => [
                'id'       => $stats->id,
                'hostname' => $stats->hostname,
                'version'  => $stats->version,
                'uptime'   => $stats->uptime,
            ],
            'queues'     => $queues,
        ];

        $content = $this->template->render('platform::queues', $data);

        return new HtmlResponse($this->template->render(
            name: 'layout::platform',
            params:[
                'content' => $content,
                'data'    => $data,
            ]
        ));
    }
}
