<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Grpc\Service;

class StreamDesc
{
    private string $name;
    private \Closure $handler;

    private bool $serverStreams;
    private bool $clientStreams;

    public function __construct(string $name, \Closure $handler, bool $serverStreams, bool $clientStreams)
    {
        $this->name = $name;
        $this->handler = $handler;
        $this->serverStreams = $serverStreams;
        $this->clientStreams = $clientStreams;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHandler(): \Closure
    {
        return $this->handler;
    }

    public function isServerStreams(): bool
    {
        return $this->serverStreams;
    }

    public function isClientStreams(): bool
    {
        return $this->clientStreams;
    }
}
