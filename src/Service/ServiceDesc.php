<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Grpc\Service;

class ServiceDesc
{
    private string $serviceName;

    /**
     * @var class-string
     */
    private string $handlerType;

    /**
     * @var MethodDesc[]
     */
    private array $methods;

    /**
     * @var StreamDesc[]
     */
    private array $streams;

    /**
     * @var mixed
     */
    private $metadata;

    /**
     * @param string $serviceName
     * @param string $handlerType
     * @param MethodDesc[] $methods
     * @param StreamDesc[] $streams
     * @param mixed $metadata
     */
    public function __construct(
        string $serviceName,
        string $handlerType,
        array $methods,
        array $streams,
        $metadata
    ) {
        $this->serviceName = $serviceName;
        $this->handlerType = $handlerType;
        $this->methods = $methods;
        $this->streams = $streams;
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    /**
     * @return class-string
     */
    public function getHandlerType(): string
    {
        return $this->handlerType;
    }

    /**
     * @return MethodDesc[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return StreamDesc[]
     */
    public function getStreams(): array
    {
        return $this->streams;
    }

    /**
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
