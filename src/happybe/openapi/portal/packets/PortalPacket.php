<?php

declare(strict_types=1);

namespace happybe\openapi\portal\packets;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;
use UnexpectedValueException;

/**
 * Class PortalPacket
 * @package happybe\openapi\portal\packets
 */
abstract class PortalPacket extends DataPacket {

    protected function decodeHeader() {
        $b1 = $this->getByte();
        $b2 = $this->getByte();

        $pid = $b1 | $b2 << 8;

        if($pid !== static::NETWORK_ID){
            throw new UnexpectedValueException("Expected " . static::NETWORK_ID . " for packet ID, got $pid");
        }
    }

    protected function encodeHeader() {
        $this->putByte(static::NETWORK_ID);
        $this->putByte(static::NETWORK_ID >> 8);
    }

    /**
     * @param NetworkSession $session
     * @return bool
     */
    final public function handle(NetworkSession $session): bool {
        return true;
    }

    /**
     * Handles Portal tcp packets
     */
    abstract public function handlePacket(): void;
}