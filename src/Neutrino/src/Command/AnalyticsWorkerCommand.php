<?php

declare(strict_types=1);

namespace Neutrino\Command;

use Doctrine\ORM\EntityManagerInterface;
use GeoIp2\Database\Reader;
use Neutrino\Domain\Analytics\AnalyticsEvent;
use Neutrino\Queue\Contract\ConsumerOptions;
use Neutrino\Queue\Driver\Redis\RedisStreamsConsumer;
use Neutrino\Queue\Driver\Redis\RedisStreamsMessage;
use Neutrino\Queue\Envelope\StreamName;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

use function gethostname;
use function getmypid;
use function usleep;

final class AnalyticsWorkerCommand extends Command
{
    protected static string $defaultName = 'neutrino:worker:analytics';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly RedisStreamsConsumer $consumer,
        private readonly EntityManagerInterface $em,
        private readonly StreamName $stream = new StreamName('neutrino.analytics'),
        private readonly string $group = 'neutrino',
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Consumes analytics jobs from Redis Streams and stores them in DB.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Analytics worker started...</info>');

        $consumerName = gethostname() . '-' . getmypid();

        $options = new ConsumerOptions(
            blockMs: 5000,
            count: 10,
            createGroup: true,
            startId: '0',
            claimStale: true,
            minIdleMs: 60_000,
            maxClaim: 50,
        );

        $this->consumer->consume(
            stream: $this->stream,
            group: $this->group,
            consumer: $consumerName,
            handler: function (RedisStreamsMessage $msg) use ($output): bool {
                // Only handle analytics messages on this stream
                // (optional guard if multiple message types share the stream)
                if ($msg->envelope->name !== 'analytics.event') {
                    return true; // ACK unknown messages to avoid poison loops / or return false
                }

                try {
                    $event = AnalyticsEvent::fromArray($msg->envelope->payload);

                    $reader = new Reader('./resources/geoip/GeoLite2-City.mmdb');
                    $ip     = '46.10.150.179';
                    $record = $reader->city($ip);

                    $event->setContinent($record->continent->name ?? null);
                    $event->setCountry($record->country->name ?? null);
                    $event->setCity($record->city->name ?? null);
                    $event->setLatitude((string) $record->location->latitude ?? null);
                    $event->setLongitude((string) $record->location->longitude ?? null);

                    $this->em->persist($event);
                    $this->em->flush();

                    // optional: keep memory stable for long-running workers
                    $this->em->clear();

                    $output->writeln('<info>Message Id: ' . $msg->envelope->id . '</info>');

                    $this->logger->info('Analytics worker success', [
                        'message_id' => $msg->envelope->id,
                        'status'     => 'success',
                    ]);

                    return true; // ACK
                } catch (Throwable $e) {
                    $this->logger->error('Analytics worker failed', [
                        'message_id' => $msg->envelope->id,
                        'error'      => $e->getMessage(),
                    ]);

                    // optional: avoid hot-looping on poison messages
                    usleep(200_000);

                    return false; // no ACK
                }
            },
            options: $options
        );
        /* unreachable (consume loops) */
        /* @noinspection PhpUnreachableStatementInspection */
        return Command::SUCCESS; // unreachable (consume loops), but keeps signature correct
    }
}
