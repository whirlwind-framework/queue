<?php

declare(strict_types=1);

namespace Whirlwind\Queue\Adapter\Amqp;

use Whirlwind\Queue\QueueManagerInterface;

class QueueManager implements QueueManagerInterface
{
    protected AmqpConnection $connection;

    public function __construct(AmqpConnection $connection)
    {
        $this->connection = $connection;
    }

    public function create(
        string $queueName,
        ?int $channelId = null,
        bool $passive = false,
        bool $durable = false,
        bool $exclusive = false,
        bool $autoDelete = true,
        bool $nowait = false,
        array $arguments = array(),
        ?int $ticket = null
    ) {
        $this->connection->getChannel($channelId)->queue_declare(
            $queueName,
            $passive,
            $durable,
            $exclusive,
            $autoDelete,
            $nowait,
            $arguments,
            $ticket
        );
    }

    public function bind(
        string $queueName,
        string $exchange,
        string $routingKey,
        ?int $channelId = null,
        bool $nowait = false,
        array $arguments = array(),
        ?int $ticket = null
    ) {
        $this->connection->getChannel($channelId)->queue_bind(
            $queueName,
            $exchange,
            $routingKey,
            $nowait,
            $arguments,
            $ticket
        );
    }

    public function delete(
        string $queueName,
        ?int $channelId = null,
        bool $ifUnused = false,
        bool $ifEmpty = false,
        bool $nowait = false,
        ?int $ticket = null
    ) {
        $this->connection->getChannel($channelId)->queue_delete($queueName, $ifUnused, $ifEmpty, $nowait, $ticket);
    }
}
