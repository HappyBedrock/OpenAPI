<?php

declare(strict_types=1);

namespace happybe\openapi\waterdog\packets;

class ServerInfoResponse extends ProxyResponse {

    private string $serverName;

    public function decodePayload(): void {
        $this->serverName = $this->getBuffer()->getString();
    }

    public function getServerName(): string {
        return $this->serverName;
    }

    public function getMessageId(): int {
        return ProtocolInfo::SERVER_INFO;
    }
}