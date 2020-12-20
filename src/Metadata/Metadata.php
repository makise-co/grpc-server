<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Grpc\Metadata;

class Metadata
{
    /**
     * @var array<string, string>
     */
    public array $metadata;

    public function __construct(array $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function get(string $key): ?string
    {
        $key = strtolower($key);

        return $this->metadata[$key] ?? null;
    }

    public function set(string $key, string $value): void
    {
        $key = strtolower($key);

        $this->metadata[$key] = $value;
    }
}
