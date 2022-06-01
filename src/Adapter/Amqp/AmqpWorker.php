<?php

declare(strict_types=1);

namespace Whirlwind\Queue\Adapter\Amqp;

use InvalidArgumentException;
use Whirlwind\Queue\MessageInterface;
use Whirlwind\Queue\WorkerInterface;

abstract class AmqpWorker implements WorkerInterface
{
    public function consume(MessageInterface $message)
    {
        if (!($message instanceof AmqpMessage)) {
            throw new InvalidArgumentException('Message must be of AmqpMessage type');
        }
        $this->consumeInternal($message);
    }

    abstract protected function consumeInternal(AmqpMessage $message);
}
