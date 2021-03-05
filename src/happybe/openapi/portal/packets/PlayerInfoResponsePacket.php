<?php

declare(strict_types=1);

namespace happybe\openapi\portal\packets;

use happybe\openapi\portal\PortalPacketHandler;
use pocketmine\utils\UUID;

/**
 * Class PlayerInfoResponsePacket
 * @package happybe\openapi\portal\packets
 */
class PlayerInfoResponsePacket extends PortalPacket {

    public const NETWORK_ID = ProtocolInfo::PLAYER_INFO_RESPONSE_PACKET;

    public const RESPONSE_SUCCESS = 0;
    public const RESPONSE_PLAYER_NOT_FOUND = 1;

    /** @var UUID $uuid */
    public $uuid;
    /** @var int $status */
    public $status;
    /** @var string $xuid */
    public $xuid;
    /** @var string $address */
    public $address;

    protected function decodePayload() {
        $this->uuid = $this->getUUID();
        $this->status = $this->getByte();
        $this->xuid = $this->getString();
        $this->address = $this->getString();
    }

    protected function encodePayload() {
        $this->putUUID($this->uuid);
        $this->putByte($this->status);
        $this->putString($this->xuid);
        $this->putString($this->address);
    }


    public function handlePacket(): void {
        PortalPacketHandler::handlePlayerInfoResponsePacket($this);
    }
}