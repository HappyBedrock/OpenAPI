<?php

declare(strict_types=1);

namespace happybe\openapi\packets;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;

/**
 * Class SunTransferPacket
 * @package happybe\openapi\packets
 */
class SunTransferPacket extends DataPacket {

    public const NETWORK_ID = 0xfa;

    /** @var string $address */
    public $address;
    /** @var int $port */
    public $port;

    public function decodePayload() {
        $this->address = $this->getString();
        $this->port = $this->getLShort();
    }

    public function encodePayload() {
        $this->putString($this->address);
        $this->putLShort($this->port);
    }

    /**
     * @param NetworkSession $session
     * @return bool
     */
    public function handle(NetworkSession $session): bool {
        return true;
    }
}