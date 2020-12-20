<?php

declare(strict_types=1);

namespace Example\Api;

use MakiseCo\Grpc\GrpcContext;

interface UsersServerInterface
{
    public function Get(GrpcContext $ctx, GetUserRequest $req): User;

    public function List(ListUserRequest $req, ListUserStream $stream): void;
}
