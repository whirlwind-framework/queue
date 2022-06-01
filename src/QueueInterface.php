<?php

declare(strict_types=1);

namespace Whirlwind\Queue;

interface QueueInterface
{
    public function push(MessageInterface $message);

    public function getName(): string;
}
