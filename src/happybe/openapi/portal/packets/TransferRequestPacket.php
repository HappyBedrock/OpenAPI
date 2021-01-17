<?php

declare(strict_types=1);

namespace happybe\openapi\portal\packets;

/**
 * Class TransferRequestPacket
 * @package happybe\openapi\portal\packets
 */
class TransferRequestPacket extends PortalPacket {

    public const NETWORK_ID = ProtocolInfo::TRANSFER_REQUEST_PACKET;

    /** @var int $entityRuntimeId */
    public $entityRuntimeId;
    /** @var string $group */
    public $group;
    /** @var string $server */
    public $server;

    public function decodePayload() {
        $this->entityRuntimeId = $this->getEntityRuntimeId();
        $this->group = $this->getString();
        $this->server = $this->getString();
    }

    public function encodePayload() {
        $this->putEntityRuntimeId($this->entityRuntimeId);
        $this->putString($this->group);
        $this->putString($this->server);
    }

    /**
     * Should not be handled by server
     */
    public function handlePacket(): void {}
}