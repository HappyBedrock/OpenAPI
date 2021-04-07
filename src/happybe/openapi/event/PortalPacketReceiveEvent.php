<?php

declare(strict_types=1);

namespace happybe\openapi\event;

use happybe\openapi\waterdog\packets\CustomMessagePacket;
use pocketmine\event\Event;

/**
 * Class PortalPacketReceiveEvent
 * @package happybe\openapi\event
 */
class PortalPacketReceiveEvent extends Event {

    /** @var CustomMessagePacket $packet */
    protected $packet;

    /**
     * PortalPacketReceiveEvent constructor.
     * @param CustomMessagePacket $packet
     */
    public function __construct(CustomMessagePacket $packet) {
        $this->packet = $packet;
    }

    public function getPacket(): CustomMessagePacket {
        return $this->packet;
    }
}