<?php

declare(strict_types=1);

namespace happybe\openapi\event;

use happybe\openapi\portal\packets\PortalPacket;
use pocketmine\event\Event;

/**
 * Class PortalPacketReceiveEvent
 * @package happybe\openapi\event
 */
class PortalPacketReceiveEvent extends Event {

    /** @var PortalPacket $packet */
    protected $packet;

    /**
     * PortalPacketReceiveEvent constructor.
     * @param PortalPacket $packet
     */
    public function __construct(PortalPacket $packet) {
        $this->packet = $packet;
    }

    public function getPacket(): PortalPacket {
        return $this->packet;
    }
}