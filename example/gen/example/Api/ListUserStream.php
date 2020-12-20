<?php

declare(strict_types=1);

namespace Example\Api;

use Google\Protobuf\Internal\Message;
use MakiseCo\Grpc\GrpcStream;

class ListUserStream extends GrpcStream
{
    protected function validateMessage(Message $message): void
    {
        if (!$message instanceof User) {
            throw new \InvalidArgumentException("Message must be an instance of User");
        }
    }
}
