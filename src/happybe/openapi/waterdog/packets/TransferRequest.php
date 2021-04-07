<?php

declare(strict_types=1);

namespace happybe\openapi\waterdog\packets;

class TransferRequest extends ProxyRequest {

    /** @var string */
    private string $currentServer;
    /** @var string */
    private string $targetServer;

    /** @var string */
    private string $playerName;

    protected function encodePayload(): void {
        $this->getBuffer()->putString($this->currentServer);
        $this->getBuffer()->putString($this->targetServer);
        $this->getBuffer()->putString($this->playerName);
    }

    /**
     * @param string $currentServer
     */
    public function setCurrentServer(string $currentServer): void {
        $this->currentServer = $currentServer;
    }

    /**
     * @param string $targetServer
     */
    public function setTargetServer(string $targetServer): void {
        $this->targetServer = $targetServer;
    }

    /**
     * @param string $playerName Player, who will be transferred
     */
    public function setPlayerName(string $playerName): void {
        $this->playerName = $playerName;
    }

    public function getMessageId(): int {
        return ProtocolInfo::TRANSFER;
    }
}