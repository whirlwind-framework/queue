<?php

declare(strict_types=1);

namespace Whirlwind\Queue\Adapter\Amqp;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class AmqpConnection
{
    public const TYPE_TOPIC = 'topic';
    public const TYPE_DIRECT = 'direct';
    public const TYPE_HEADERS = 'headers';
    public const TYPE_FANOUT = 'fanout';

    protected ?AMQPStreamConnection $connection = null;

    /**
     * @var AMQPChannel[]
     */
    protected array $channels = [];

    protected string $host;

    protected int $port;

    protected string $user;

    protected string $password;

    protected string $vHost;

    public function __construct(
        string $host = '127.0.0.1',
        int $port = 5672,
        string $user = '',
        string $password = '',
        string $vHost = '/'
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->vHost = $vHost;
    }

    public function getConnection(): AMQPStreamConnection
    {
        if (null === $this->connection) {
            $this->connection = new AMQPStreamConnection(
                $this->host,
                $this->port,
                $this->user,
                $this->password,
                $this->vHost
            );
        }
        return $this->connection;
    }

    public function getChannel(?int $channelId = null): AMQPChannel
    {
        $index = $channelId ?: 'default';
        if (!\array_key_exists($index, $this->channels)) {
            $this->channels[$index] = $this->getConnection()->channel($channelId);
        }
        return $this->channels[$index];
    }

    public function send(
        string $exchange,
        string $routingKey,
        AmqpMessage $message,
        string $type = self::TYPE_TOPIC,
        ?int $channelId = null
    ) {
        if ($type == self::TYPE_TOPIC) {
            $this->getChannel($channelId)->exchange_declare($exchange, $type, false, true, false);
        }
        $this->getChannel($channelId)->basic_publish($message, $exchange, $routingKey);
    }

    public function close(): void
    {
        if (null !== $this->connection) {
            $this->connection->close(...\func_get_args());
        }
    }
}
