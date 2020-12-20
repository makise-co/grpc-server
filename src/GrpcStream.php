<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Grpc;

use Google\Protobuf\Internal\Message;
use Swoole\Http\Request;
use Swoole\Http\Response;

abstract class GrpcStream
{
    private GrpcServer $server;
    private Request $request;
    private Response $response;
    private GrpcContext $ctx;

    public function __construct(GrpcServer $server, GrpcContext $ctx, Request $request, Response $response)
    {
        $this->server = $server;
        $this->ctx = $ctx;
        $this->request = $request;
        $this->response = $response;
    }

    public function getContext(): GrpcContext
    {
        return $this->ctx;
    }

    abstract protected function validateMessage(Message $message): void;

    final public function send(Message $message)
    {
        $this->validateMessage($message);

        $this->server->sendMessage($this->response->fd, $this->request->streamId, $message);
    }
}
