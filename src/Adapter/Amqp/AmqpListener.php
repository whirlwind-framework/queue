<?php

declare(strict_types=1);

namespace Whirlwind\Queue\Adapter\Amqp;

use PhpAmqpLib\Message\AMQPMessage as BaseAMQPMessage;

class AmqpListener
{
    protected AmqpConnection $connection;

    protected array $queues = [];
    protected array $routingWorkers = [];

    protected bool $noAck;

    public function __construct(AmqpConnection $connection, array $queues, array $routingWorkers, bool $noAck = false)
    {
        $this->connection = $connection;
        $this->queues = $queues;
        foreach ($routingWorkers as $routingKey => $worker) {
            $this->addRouting($routingKey, $worker);
        }
        $this->noAck = $noAck;
    }

    protected function addRouting($routingKey, $worker)
    {
        if (!($worker instanceof AmqpWorker)) {
            throw new \InvalidArgumentException('Worker must be instance of AmqpWorker');
        }
        $this->routingWorkers[$routingKey] = $worker;
    }

    public function run()
    {
        $this->listen($this->queues, [$this, 'callback'], $this->noAck);
    }

    protected function listen(array $queueNames, callable $callback, $noAck)
    {
        \array_map(function ($queue) use ($callback, $noAck) {
            $this->connection->getChannel()->basic_consume(
                $queue,
                '',
                false,
                $noAck,
                false,
                false,
                $callback
            );
        }, $queueNames);

        while (\count($this->connection->getChannel()->callbacks)) {
            $this->connection->getChannel()->wait();
        }

        $this->connection->getChannel()->close();
        $this->connection->close();
    }

    public function callback(BaseAMQPMessage $msg)
    {
        $routingKey = $msg->getRoutingKey();
        if (!isset($this->routingWorkers[$routingKey])) {
            throw new \RuntimeException("Invalid routing key: $routingKey");
        }
        /** @var AmqpWorker $worker */
        $worker = $this->routingWorkers[$routingKey];
        $message = new AmqpMessage(\json_decode($msg->body, true));
        $message->setChannel($msg->getChannel());
        $message->setDeliveryTag($msg->getDeliveryTag());
        $worker->consume($message);
    }
}
