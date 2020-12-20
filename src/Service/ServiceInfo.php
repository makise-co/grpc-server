<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Grpc\Service;

class ServiceInfo
{
    private object $serviceImpl;

    /**
     * @var array<string, MethodDesc>
     */
    private array $methods;

    /**
     * @var array<string, StreamDesc>
     */
    private array $streams;

    /**
     * @var mixed
     */
    private $mdata;

    /**
     * ServiceInfo constructor.
     * @param object $serviceImpl
     * @param array<string, MethodDesc> $methods
     * @param array<string, StreamDesc> $streams
     * @param mixed $mdata
     */
    public function __construct(object $serviceImpl, array $methods, array $streams, $mdata)
    {
        $this->serviceImpl = $serviceImpl;
        $this->methods = $methods;
        $this->streams = $streams;
        $this->mdata = $mdata;
    }

    /**
     * @return object
     */
    public function getServiceImpl(): object
    {
        return $this->serviceImpl;
    }

    /**
     * @return array<string, MethodDesc>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return array<string, StreamDesc>
     */
    public function getStreams(): array
    {
        return $this->streams;
    }

    /**
     * @return mixed
     */
    public function getMdata()
    {
        return $this->mdata;
    }
}
