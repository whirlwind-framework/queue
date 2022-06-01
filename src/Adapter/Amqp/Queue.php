<?php

declare(strict_types=1);

namespace Whirlwind\Queue\Adapter\Amqp;

use InvalidArgumentException;
use Whirlwind\Queue\MessageInterface;
use Whirlwind\Queue\QueueInterface;

class Queue implements QueueInterface
{
    protected AmqpConnection $connection;

    protected string $name;

    protected string $exchange;

    protected string $routingKey;

    protected string $type;

    protected ?int $channelId;

    public function __construct(
        AmqpConnection $connection,
        string $name,
        string $exchange,
        string $routingKey,
        string $type,
        ?int $channelId = null
    ) {
        $this->connection = $connection;
        $this->name = $name;
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->type = $type;
        $this->channelId = $channelId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function push(MessageInterface $message)
    {
        if (!($message instanceof AmqpMessage)) {
            throw new InvalidArgumentException('Message must be of AmqpMessage type');
        }
        $this->connection->send(
            $this->exchange,
            $this->routingKey,
            $message,
            $this->type,
            $this->channelId
        );
    }
}
