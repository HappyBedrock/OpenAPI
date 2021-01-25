<?php

declare(strict_types=1);

namespace happybe\openapi\portal;

use happybe\openapi\portal\packets\AuthRequestPacket;
use happybe\openapi\portal\packets\AuthResponsePacket;
use happybe\openapi\portal\packets\PlayerInfoRequestPacket;
use happybe\openapi\portal\packets\PlayerInfoResponsePacket;
use happybe\openapi\portal\packets\PortalPacket;
use happybe\openapi\portal\packets\TransferRequestPacket;
use happybe\openapi\portal\packets\TransferResponsePacket;

/**
 * Class PacketPool
 * @package happybe\openapi\portal
 */
class PacketPool {

    /** @var array $pool */
    private static $pool = [];

    public static function init() {
        self::registerPacket(new AuthRequestPacket());
        self::registerPacket(new AuthResponsePacket());
        self::registerPacket(new TransferRequestPacket());
        self::registerPacket(new TransferResponsePacket());
        self::registerPacket(new PlayerInfoRequestPacket());
        self::registerPacket(new PlayerInfoResponsePacket());
    }

    /**
     * @param PortalPacket $packet
     */
    public static function registerPacket(PortalPacket $packet) {
        self::$pool[$packet::NETWORK_ID] = $packet;
    }

    /**
     * @param int $networkId
     * @return PortalPacket|null
     */
    public static function getPacket(int $networkId): ?PortalPacket {
        return isset(self::$pool[$networkId]) ? clone self::$pool[$networkId] : null;
    }

    /**
     * @param string $buffer
     * @return PortalPacket|null
     */
    public static function getPacketByBuffer(string $buffer):? PortalPacket {
        $b1 = ord($buffer[0]);
        $b2 = ord($buffer[1]);

        $pk = self::getPacket($b1 | $b2 << 8);
        $pk->setBuffer($buffer, 2);

        return $pk;
    }
}