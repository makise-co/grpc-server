<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Grpc\Service;

class MethodDesc
{
    private string $name;
    private \Closure $handler;

    public function __construct(string $name, \Closure $handler)
    {
        $this->name = $name;
        $this->handler = $handler;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHandler(): \Closure
    {
        return $this->handler;
    }
}
