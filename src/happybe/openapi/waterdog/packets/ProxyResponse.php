<?php

declare(strict_types=1);

namespace happybe\openapi\waterdog\packets;

abstract class ProxyResponse extends CustomMessagePacket {

    abstract public function decodePayload(): void;
}