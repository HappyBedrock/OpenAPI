<?php

declare(strict_types=1);

namespace happybe\openapi\waterdog\packets;

use pocketmine\network\mcpe\NetworkBinaryStream;

abstract class CustomMessagePacket {

    private NetworkBinaryStream $buffer;

    public function __construct() {
        $this->buffer = new NetworkBinaryStream();
    }

    abstract public function getMessageId(): int;

    public function getBuffer(): NetworkBinaryStream {
        return $this->buffer;
    }

    public function setBuffer(NetworkBinaryStream $buffer): void {
        $this->buffer = $buffer;
    }
}