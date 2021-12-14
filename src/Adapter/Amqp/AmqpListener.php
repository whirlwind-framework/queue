<?php

declare(strict_types=1);

namespace Whirlwind\Queue\Adapter\Amqp;

use PhpAmqpLib\Message\AMQPMessage as BaseAMQPMessage;

class AmqpListener
{
    protected $amqpConnection;

    protected $queues;

    protected $noAck;

    public function __construct(AmqpConnection $amqpConnection, array $queues, bool $noAck = false)
    {
        $this->amqpConnection = $amqpConnection;
        foreach ($queues as $name => $worker) {
            $this->addQueue($name, $worker);
        }
        $this->noAck = $noAck;
    }

    protected function addQueue($name, $worker)
    {
        if (!($worker instanceof AmqpWorker)) {
            throw new \InvalidArgumentException('Worker must be instance of AmqpWorker');
        }
        $this->queues[$name] = $worker;
    }

    public function run()
    {
        $queueNames = \array_keys($this->queues);
        $this->listen($queueNames, [$this, 'callback'], $this->noAck);
    }

    protected function listen(array $queueNames, callable $callback, $noAck = false)
    {
        \array_map(function($queue) use ($callback, $noAck) {
            $this->amqpConnection->getChannel()->basic_consume($queue, '', false, $noAck, false, false, $callback);
        }, $queueNames);

        while (\count($this->amqpConnection->getChannel()->callbacks)) {
            $this->amqpConnection->getChannel()->wait();
        }

        $this->amqpConnection->getChannel()->close();
        $this->amqpConnection->getChannel()->close();
    }

    public function callback(BaseAMQPMessage $msg)
    {
        $routingKey = $msg->delivery_info['routing_key'];
        if (!isset($this->queues[$routingKey])) {
            throw new \RuntimeException("Invalid routing key: $routingKey");
        }
        /** @var AmqpWorker $worker */
        $worker = $this->queues[$routingKey];
        $worker->consume(
            new AmqpMessage(\json_decode($msg->body, true)),
            $msg->delivery_info['channel'],
            $msg->delivery_info['delivery_tag']
        );
    }
}
