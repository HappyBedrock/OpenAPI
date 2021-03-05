<?php

declare(strict_types=1);

namespace happybe\openapi\portal\packets;

use happybe\openapi\portal\PortalPacketHandler;
use pocketmine\utils\UUID;

/**
 * Class TransferResponsePacket
 * @package happybe\openapi\portal\packets
 */
class TransferResponsePacket extends PortalPacket {

    public const NETWORK_ID = ProtocolInfo::TRANSFER_RESPONSE_PACKET;

    public const RESPONSE_SUCCESS = 0;
    public const RESPONSE_GROUP_NOT_FOUND = 1;
    public const RESPONSE_SERVER_NOT_FOUND = 2;
    public const RESPONSE_ALREADY_ON_SERVER = 3;
    public const RESPONSE_PLAYER_NOT_FOUND = 4;
    public const RESPONSE_ERROR = 5;

    /** @var UUID $uuid */
    public $uuid;
    /** @var int $status */
    public $status;
    /** @var string $reason */
    public $reason = "";

    protected function decodePayload() {
        $this->uuid = $this->getUUID();
        $this->status = $this->getByte();
        if($this->status == self::RESPONSE_ERROR) {
            $this->reason = $this->getString();
        }
    }

    protected function encodePayload() {
        $this->putUUID($this->uuid);
        $this->putByte($this->status);
        if($this->status == self::RESPONSE_ERROR) {
            $this->putString($this->reason);
        }
    }

    public function handlePacket(): void {
        PortalPacketHandler::handleTransferResponsePacket($this);
    }
}