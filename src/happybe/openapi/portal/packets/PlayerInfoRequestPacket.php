<?php

declare(strict_types=1);

namespace happybe\openapi\portal\packets;

use pocketmine\utils\UUID;

/**
 * Class PlayerInfoRequestPacket
 * @package happybe\openapi\portal\packets
 */
class PlayerInfoRequestPacket extends PortalPacket {

    public const NETWORK_ID = ProtocolInfo::PLAYER_INFO_REQUEST_PACKET;

    /** @var UUID $uuid */
    public $uuid;

    public static function create(UUID $uuid) {
        $pk = new PlayerInfoRequestPacket();
        $pk->uuid = $uuid;
    }

    protected function decodePayload(): void {
        $this->uuid = $this->getUUID();
    }

    protected function encodePayload(): void {
        $this->putUUID($this->uuid);
    }

    /**
     * Shouldn't be handled by server
     */
    public function handlePacket(): void {}
}