<?php

declare(strict_types=1);

namespace Example\Api;

use MakiseCo\Grpc\GrpcContext;

class UsersServer implements UsersServerInterface
{
    public function Get(GrpcContext $ctx, GetUserRequest $req): User
    {
        $user = new User();
        $user->setId($req->getId());
        $user->setEmail('dmitry.k@test.tld');
        $user->setName('Dmitry K.');
        $user->setRating(228.53);

        return $user;
    }

    public function List(ListUserRequest $req, ListUserStream $stream): void
    {
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setId($i);
            $user->setEmail("user{$i}@test");
            $user->setName("User {$i}");
            $user->setRating($i * 1.53);

            $stream->send($user);
        }
    }
}
