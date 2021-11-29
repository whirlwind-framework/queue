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

    /**
     * @var AMQPStreamConnection|null
     */
    protected $connection;

    /**
     * @var AMQPChannel[]
     */
    protected $channels = [];

    protected $host;

    protected $port;

    protected $user;

    protected $password;

    protected $vhost;

    /**
     * AmqpConnection constructor.
     * @param string $host
     * @param int $port
     * @param $user
     * @param $password
     * @param string $vhost
     */
    public function __construct(
        string $host = '127.0.0.1',
        int $port = 5672,
        string $user = '',
        string $password = '',
        string $vhost = '/'
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->vhost = $vhost;
    }

    public function getConnection(): AMQPStreamConnection
    {
        if (null === $this->connection) {
            $this->connection = new AMQPStreamConnection(
                $this->host,
                $this->port,
                $this->user,
                $this->password,
                $this->vhost
            );
        }
        return $this->connection;
    }

    public function getChannel($channelId = null): AMQPChannel
    {
        $index = $channelId ?: 'default';
        if (!\array_key_exists($index, $this->channels)) {
            $this->channels[$index] = $this->getConnection()->channel($channelId);
        }
        return $this->channels[$index];
    }

    public function send($exchange, $routingKey, AmqpMessage $message, $type = self::TYPE_TOPIC, $channelId = null)
    {
        if ($type == self::TYPE_TOPIC) {
            $this->getChannel($channelId)->exchange_declare($exchange, $type, false, true, false);
        }
        $this->getChannel($channelId)->basic_publish($message, $exchange, $routingKey);
    }
}
