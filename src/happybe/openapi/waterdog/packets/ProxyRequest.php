<?php

declare(strict_types=1);

namespace happybe\openapi\waterdog\packets;

abstract class ProxyRequest extends CustomMessagePacket {

    final protected function encode(): void {
        $this->encodeHeader();
        $this->encodePayload();
    }

    final protected function encodeHeader(): void {
        $this->getBuffer()->putUnsignedVarInt($this->getMessageId());
    }

    abstract protected function encodePayload(): void;
}