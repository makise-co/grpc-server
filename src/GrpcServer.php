<?php
/**
 * This file is part of the Makise-Co Framework
 *
 * World line: 0.571024a
 * (c) Dmitry K. <coder1994@gmail.com>
 */

declare(strict_types=1);

namespace MakiseCo\Grpc;

use Amp\Http\Http2\Http2Parser;
use Closure;
use Google\Protobuf\Internal\Message;
use InvalidArgumentException;
use MakiseCo\Grpc\Metadata\Metadata;
use MakiseCo\Grpc\Service\MethodDesc;
use MakiseCo\Grpc\Service\ServiceDesc;
use MakiseCo\Grpc\Service\ServiceInfo;
use MakiseCo\Grpc\Service\StreamDesc;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Coroutine;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server as SwooleServer;
use Throwable;

use function get_class;
use function json_encode;
use function pack;
use function strlen;
use function strrpos;
use function substr;
use function swoole_set_process_name;

class GrpcServer
{
    public const MODE_MAIN = 'master';
    public const MODE_MANAGER = 'manager';
    public const MODE_WORKER = 'worker';

    protected string $mode = self::MODE_MAIN;

    protected SwooleServer $server;
    protected EventDispatcherInterface $eventDispatcher;

    protected GrpcServerConfig $config;

    protected string $appName;

    /**
     * Key is name
     *
     * @var array<string, ServiceInfo>
     */
    protected array $services = [];

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GrpcServerConfig $config,
        string $appName
    ) {
        $this->eventDispatcher = $eventDispatcher;

        $this->config = $config;
        $this->appName = $appName;
    }

    public function start(string $host, int $port): void
    {
        $this->server = new SwooleServer($host, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $this->server->set(
            array_merge(
                [
                    'daemonize' => false,
                    'worker_num' => 1,
                    'send_yield' => true,
                    'open_http2_protocol' => true,
                    'open_http_protocol' => false,
                ],
                $this->config->getOptions()
            )
        );

        $this->server->on(
            'Start',
            function (SwooleServer $server) {
                $this->setProcessName('master process');

                $this->eventDispatcher->dispatch(new Events\ServerStarted());
            }
        );

        $this->server->on(
            'ManagerStart',
            function (SwooleServer $server) {
                $this->mode = self::MODE_MANAGER;

                $this->setProcessName('manager process');

                $this->eventDispatcher->dispatch(new Events\ManagerStarted());
            }
        );

        $this->server->on(
            'WorkerStart',
            function (SwooleServer $server, int $workerId) {
                $this->mode = self::MODE_WORKER;

                $this->setProcessName('worker process');

                try {
                    // dispatch before worker started event for early services initialization (before routes resolved)
                    $this->eventDispatcher->dispatch(new Events\BeforeWorkerStarted($workerId, $this));

                    // routes and their dependencies should be resolved before worker will start requests processing
//                    $this->requestHandler = $this->requestHandlerFactory->create();

                    // dispatch application level WorkerStarted event
                    $this->eventDispatcher->dispatch(new Events\WorkerStarted($workerId));
                } catch (Throwable $e) {
                    // stop server if worker cannot be started (to prevent infinite loop)
                    Coroutine::defer(fn() => $server->shutdown());

                    throw $e;
                }
            }
        );

        $this->server->on(
            'WorkerStop',
            function (SwooleServer $server, int $workerId) {
                $this->mode = self::MODE_WORKER;

                // dispatch before worker exit event to stop services
                $this->eventDispatcher->dispatch(new Events\BeforeWorkerExit($workerId, $this));

                $this->eventDispatcher->dispatch(new Events\WorkerStopped($workerId));
            }
        );

        $this->server->on(
            'WorkerExit',
            function (SwooleServer $server, int $workerId) {
                $this->mode = self::MODE_WORKER;

                $this->eventDispatcher->dispatch(new Events\WorkerExit($workerId));
            }
        );

        $this->server->on(
            'Shutdown',
            function (SwooleServer $server) {
                $this->eventDispatcher->dispatch(new Events\ServerShutdown());
            }
        );

        $this->server->on('Request', Closure::fromCallable([$this, 'onRequest']));

        $this->server->start();
    }

    public function stop(): void
    {
        $this->server->shutdown();
    }

    protected function onRequest(Request $request, Response $response): void
    {
        $uri = $request->server['request_uri'];

        if ($uri !== '' && $uri[0] === '/') {
            $uri = substr($uri, 1);
        }

        $pos = strrpos($uri, '/');
        if ($pos === false) {
            $error = "malformed method name: {$request->server['request_uri']}";

            $response->header('content-type', 'application/grpc');
            $response->header('grpc-trailer', 'trailer, grpc-status, grpc-message');

            $response->trailer('grpc-status', (string)GrpcCodes::RESOURCE_EXHAUSTED);
            $response->trailer('grpc-message', $error);

            $response->end();

            return;
        }

        $service = substr($uri, 0, $pos);
        $method = substr($uri, $pos + 1);

        // write GRPC headers
        $response->header('content-type', 'application/grpc');
        $response->header('grpc-trailer', 'trailer, grpc-status, grpc-message');

        $knownService = $this->services[$service] ?? null;
        if ($knownService !== null) {
            if (null !== ($knownMethod = $knownService->getMethods()[$method] ?? null)) {
                $this->processUnaryRPC($request, $response, $knownService, $knownMethod);
                return;
            }

            if (null !== ($knownStream = $knownService->getStreams()[$method] ?? null)) {
                $this->processStreamingRPC($request, $response, $knownService, $knownStream);
                return;
            }
        }

        $error = "method {$method} not found in service {$service}";

        $response->trailer('grpc-status', (string)GrpcCodes::UNIMPLEMENTED);
        $response->trailer('grpc-message', $error);

        $response->end();
    }

    protected function processUnaryRPC(
        Request $request,
        Response $response,
        ServiceInfo $info,
        MethodDesc $method
    ): void {
        $handler = $method->getHandler();
        $context = $this->createIncomingContext($request);

        try {
            $reply = $handler($this, $info, $request, $context);
        } catch (GrpcErrorException $e) {
            $this->handleGrpcError($e, $response);

            $response->end();

            return;
        } catch (Throwable $e) {
            $this->handleThrowableError($e, $response);

            $response->end();

            return;
        }

        $packedMsg = $this->packMessage($reply);

        $response->trailer('grpc-status', '0');
        $response->trailer('grpc-message', '');

        $response->end($packedMsg);
    }

    protected function processStreamingRPC(
        Request $request,
        Response $response,
        ServiceInfo $info,
        StreamDesc $method
    ): void {
        $handler = $method->getHandler();
        $context = $this->createIncomingContext($request);

        try {
            $handler($this, $info, $request, $response, $context);
        } catch (GrpcErrorException $e) {
            $this->handleGrpcError($e, $response);

            $response->end();

            return;
        } catch (Throwable $e) {
            $this->handleThrowableError($e, $response);

            $response->end();

            return;
        }

        $response->trailer('grpc-status', '0');
        $response->trailer('grpc-message', '');

        $response->end();
    }

    protected function createIncomingContext(Request $request): GrpcContext
    {
        return GrpcContext::newIncomingContext(new GrpcContext(), new Metadata($request->header));
    }

    public function RegisterService(ServiceDesc $serviceDesc, object $service): void
    {
        $classString = $serviceDesc->getHandlerType();
        if (!$service instanceof $classString) {
            $class = get_class($service);

            throw new InvalidArgumentException(
                "GrpcServer.RegisterService found the handler of type {$class}" .
                " that does not satisfy {$classString}"
            );
        }

        $methods = $serviceDesc->getMethods();
        $mappedMethods = [];

        foreach ($methods as $method) {
            $mappedMethods[$method->getName()] = $method;
        }

        $streams = $serviceDesc->getStreams();
        $mappedStreams = [];

        foreach ($streams as $stream) {
            $mappedStreams[$stream->getName()] = $stream;
        }

        $info = new ServiceInfo(
            $service,
            $mappedMethods,
            $mappedStreams,
            $serviceDesc->getMetadata()
        );

        $this->services[$serviceDesc->getServiceName()] = $info;
    }

    protected function handleGrpcError(GrpcErrorException $e, Response $response): void
    {
        $data = $e->getData();
        if (!empty($data)) {
            $response->header('err-metadata', json_encode($data));
        }

        $response->trailer('grpc-status', (string)$e->getCode());
        $response->trailer('grpc-message', $e->getMessage());
    }

    protected function handleThrowableError(Throwable $e, Response $response): void
    {
        $response->trailer('grpc-status', (string)GrpcCodes::INTERNAL);
        $response->trailer('grpc-message', $e->getMessage());
    }

    protected function packMessage(Message $message): string
    {
        $data = $message->serializeToString();

        return pack('CN', 0, strlen($data)) . $data;
    }

    public function sendMessage(int $fd, int $streamId, Message $message)
    {
        $serializedData = $this->packMessage($message);

        // Bypassing swoole restriction to write streamed data into HTTP2 response
        // TODO: https://github.com/swoole/swoole-src/issues/3901
        $frameData = $this->writeFrame($serializedData, Http2Parser::DATA, Http2Parser::NO_FLAG, $streamId);
        $this->server->send($fd, $frameData);
    }

    private function writeFrame(string $data, int $type, int $flags, int $stream = 0): string
    {
        // Thanks to AMPHP authors
        return (substr(pack("NccN", strlen($data), $type, $flags, $stream), 1) . $data);
    }

    protected function setProcessName(string $name): void
    {
        if (!empty($this->appName)) {
            swoole_set_process_name("{$this->appName} {$name}");

            return;
        }

        swoole_set_process_name($name);
    }
}
