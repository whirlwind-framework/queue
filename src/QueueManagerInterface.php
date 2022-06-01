<?php

declare(strict_types=1);

namespace Whirlwind\Queue;

interface QueueManagerInterface
{
    public function create(string $queueName);

    public function delete(string $queueName);
}
