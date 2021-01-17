<?php

declare(strict_types=1);

namespace happybe\openapi\portal\packets;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;

/**
 * Class PortalPacket
 * @package happybe\openapi\portal\packets
 */
abstract class PortalPacket extends DataPacket {

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