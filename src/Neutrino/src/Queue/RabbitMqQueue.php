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
namespace Neutrino\Queue;

use JsonException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

use function json_decode;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final readonly class RabbitMqQueue implements QueueInterface
{
    public function __construct(
        private AMQPChannel $channel
    ) {}

    /**
     * @throws JsonException
     */
    public function push(string $queue, array $payload): void
    {
        $this->channel->queue_declare($queue, false, true, false, false);

        $msg = new AMQPMessage(
            json_encode($payload, JSON_THROW_ON_ERROR),
            ['delivery_mode' => 2]
        );

        $this->channel->basic_publish($msg, '', $queue);
    }

    /**
     * @throws JsonException
     */
    public function pop(string $queue): ?JobInterface
    {
        $message = $this->channel->basic_get($queue);

        if ($message === null) {
            return null;
        }

        return new RabbitMqJob(
            $message,
            json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    public function ack(JobInterface $job): void
    {
        /** @var RabbitMqJob $job */
        $this->channel->basic_ack($job->getMessage()->getDeliveryTag());
    }

    public function reject(JobInterface $job, bool $requeue = false): void
    {
        /** @var RabbitMqJob $job */
        $this->channel->basic_reject(
            $job->getMessage()->getDeliveryTag(),
            $requeue
        );
    }
}