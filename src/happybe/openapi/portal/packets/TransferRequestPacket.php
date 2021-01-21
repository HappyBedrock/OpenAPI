<?php

declare(strict_types=1);

namespace happybe\openapi\portal\packets;

use pocketmine\utils\UUID;

/**
 * Class TransferRequestPacket
 * @package happybe\openapi\portal\packets
 */
class TransferRequestPacket extends PortalPacket {

    public const NETWORK_ID = ProtocolInfo::TRANSFER_REQUEST_PACKET;

    /** @var UUID $uuid */
    public $uuid;
    /** @var string $group */
    public $group;
    /** @var string $server */
    public $server;

    public function decodePayload() {
        $this->uuid = $this->getUUID();
        $this->group = $this->getString();
        $this->server = $this->getString();
    }

    public function encodePayload() {
        $this->putUUID($this->uuid);
        $this->putString($this->group);
        $this->putString($this->server);
    }

    /**
     * Should not be handled by server
     */
    public function handlePacket(): void {}
}