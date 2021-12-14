<?php

declare(strict_types=1);

namespace Whirlwind\Queue\Adapter\Amqp;

use PhpAmqpLib\Channel\AMQPChannel;

abstract class AmqpWorker
{
    abstract function consume(AmqpMessage $message, AMQPChannel $channel, $deliveryTag);
}
