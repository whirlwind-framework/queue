<?php

declare(strict_types=1);

namespace Whirlwind\Queue\Adapter\Amqp;

use InvalidArgumentException;
use Whirlwind\Queue\MessageInterface;
use Whirlwind\Queue\QueueInterface;

class Queue implements QueueInterface
{
    protected $amqpConnection;

    protected $exchange;

    protected $routingKey;

    protected $type;

    protected $channelId;

    public function __construct(AmqpConnection $amqpConnection, $exchange, $routingKey, $type, $channelId)
    {
        $this->amqpConnection = $amqpConnection;
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->type = $type;
        $this->channelId = $channelId;
    }

    public function push(MessageInterface $message)
    {
        if (!($message instanceof AmqpMessage)) {
            throw new InvalidArgumentException('Message must be of AmqpMessage type');
        }
        $this->amqpConnection->send(
            $this->exchange,
            $this->routingKey,
            $message,
            $this->type,
            $this->channelId
        );
    }
}
