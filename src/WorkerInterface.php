<?php

declare(strict_types=1);

namespace Whirlwind\Queue;

interface WorkerInterface
{
    public function consume(MessageInterface $message);
}
