<?php

declare(strict_types=1);

use Example\Api\GetUserRequest;
use Example\Api\ListUserRequest;
use Example\Api\ListUserStream;
use Example\Api\User;
use Example\Api\UsersServer;
use Example\Api\UsersServerInterface;
use MakiseCo\Grpc\GrpcContext;
use MakiseCo\Grpc\GrpcServer;
use MakiseCo\Grpc\GrpcServerConfig;
use MakiseCo\Grpc\Service\MethodDesc;
use MakiseCo\Grpc\Service\ServiceDesc;
use MakiseCo\Grpc\Service\ServiceInfo;
use MakiseCo\Grpc\Service\StreamDesc;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Symfony\Component\EventDispatcher\EventDispatcher;

require_once __DIR__ . '/../vendor/autoload.php';

$ed = new EventDispatcher();
$server = new GrpcServer(
    $ed,
    GrpcServerConfig::fromArray(
        [
            'options' => [
                'worker_num' => 2,
            ]
        ]
    ),
    'makise test'
);

// This code should be replaced by Protoc gen (currently it does not support server side code generation)
$usersServerSrvDesc = new ServiceDesc(
    // GRPC Service Name
    'api.Users',
    UsersServerInterface::class,
    // GRPC Service Unary Methods
    [
        new MethodDesc(
            // GRPC Method Name (unary)
            'Get',
            // boilerplate code, that should be auto generated
            static function (GrpcServer $server, ServiceInfo $info, Request $request, GrpcContext $ctx): User {
                /** @var UsersServer $srv */
                $srv = $info->getServiceImpl();

                $payload = substr($request->rawContent(), 5);

                $req = new GetUserRequest();
                $req->mergeFromString($payload);

                return $srv->Get($ctx, $req);
            }
        )
    ],
    // GRPC Service Streaming Methods
    [
        new StreamDesc(
            // GRPC Method Name (streaming)
            'List',
            // boilerplate code, that should be auto generated
            function (GrpcServer $server, ServiceInfo $info, Request $request, Response $response, GrpcContext $ctx) {
                /** @var UsersServer $srv */
                $srv = $info->getServiceImpl();

                $payload = substr($request->rawContent(), 5);

                $req = new ListUserRequest();
                $req->mergeFromString($payload);

                $stream = new ListUserStream(
                    $server,
                    $ctx,
                    $request,
                    $response
                );

                $srv->List($req, $stream);
            },
            // server side streaming
            true,
            // client side streaming
            false
        )
    ],
    __DIR__ . '/../api/example/users.proto'
);

$server->RegisterService($usersServerSrvDesc, new UsersServer());

$server->start('127.0.0.1', 9090);
