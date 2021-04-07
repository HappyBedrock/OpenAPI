<?php

declare(strict_types=1);

namespace happybe\openapi\waterdog\packets;

class ServerInfoRequest extends ProxyRequest {

    private string $serverName;

    public function getMessageId(): int {
        return ProtocolInfo::SERVER_INFO;
    }

    public function encodePayload(): void {
        $this->getBuffer()->putString($this->serverName);
    }

    public function setServerName(string $serverName): void {
        $this->serverName = $serverName;
    }
}