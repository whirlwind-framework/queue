<?php

declare(strict_types=1);

namespace Whirlwind\Queue\Adapter\Amqp;

use PhpAmqpLib\Message\AMQPMessage as BaseAMQPMessage;
use Whirlwind\Queue\MessageInterface;

class AmqpMessage extends BaseAMQPMessage implements MessageInterface
{
    public function __construct($body, $properties = array())
    {
        if (\is_array($body)) {
            $body = \json_encode($body);
        }
        if (!\is_string($body)) {
            throw new \InvalidArgumentException('Body can be array or string only');
        }
        parent::__construct($body, $properties);
    }

    public function getBody(): string
    {
        return (string)parent::getBody();
    }
}
