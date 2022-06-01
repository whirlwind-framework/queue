<?php

declare(strict_types=1);

namespace Whirlwind\Queue;

interface MessageInterface
{
    public function getBody(): string;
}
