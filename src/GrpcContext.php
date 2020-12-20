<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Grpc;

use MakiseCo\Grpc\Metadata\MdIncomingKey;
use MakiseCo\Grpc\Metadata\Metadata;

class GrpcContext
{
    private array $values = [];

    public static function newIncomingContext(GrpcContext $ctx, Metadata $md): self
    {
        return $ctx->withValue(MdIncomingKey::class, $md);
    }

    /**
     * @param string|int $key
     * @param mixed $value
     * @return $this
     */
    public function withValue($key, $value): self
    {
        if ($key === null) {
            throw new \InvalidArgumentException("Key cannot be null");
        }

        $newCtx = new self();
        $newCtx->values[$key] = $value;

        return $newCtx;
    }

    /**
     * @param string|int $key
     * @return mixed|null
     */
    public function value($key)
    {
        if ($key === null) {
            throw new \InvalidArgumentException("Key cannot be null");
        }

        return $this->values[$key] ?? null;
    }
}
