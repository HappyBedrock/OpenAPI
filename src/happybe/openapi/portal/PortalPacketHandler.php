<?php

declare(strict_types=1);

namespace happybe\openapi\portal;

use happybe\openapi\OpenAPI;
use happybe\openapi\portal\packets\AuthResponsePacket;
use happybe\openapi\portal\packets\PlayerInfoResponsePacket;
use happybe\openapi\portal\packets\TransferResponsePacket;
use pocketmine\Server;

/**
 * Class PortalPacketHandler
 * @package happybe\openapi\portal
 */
class PortalPacketHandler {

    /**
     * @param AuthResponsePacket $packet
     */
    public static function handleAuthResponsePacket(AuthResponsePacket $packet) {
        if($packet->status == AuthResponsePacket::RESPONSE_SUCCESS) {
            OpenAPI::getInstance()->getLogger()->info("Authentication with Portal was successful!");
            return;
        }

        $reason = "";
        switch ($packet->status) {
            case AuthResponsePacket::RESPONSE_INCORRECT_SECRET:
                $reason = "Incorrect secret";
                break;
            case AuthResponsePacket::RESPONSE_UNKNOWN_TYPE:
                $reason = "Unknown login type";
                break;
            case AuthResponsePacket::RESPONSE_INVALID_DATA:
                $reason = "Sent invalid data (server is already authenticated or invalid group sent)";
                break;
        }

        OpenAPI::getInstance()->getLogger()->info("An error occurred while authenticating: $reason");
    }

    /**
     * @param PlayerInfoResponsePacket $packet
     */
    public static function handlePlayerInfoResponsePacket(PlayerInfoResponsePacket $packet) {
        $player = Server::getInstance()->getPlayerByUUID($packet->uuid);
        if($player === null) {
            return;
        }

        OpenAPI::getInstance()->getLogger()->debug("Received info from proxy ({$player->getName()}): XUID={$packet->xuid};Address={$packet->address}");
    }

    /**
     * @param TransferResponsePacket $packet
     */
    public static function handleTransferResponsePacket(TransferResponsePacket $packet) {
        if($packet->status == TransferResponsePacket::RESPONSE_SUCCESS) {
            return;
        }

        $reason = $packet->reason;
        switch ($packet->status) {
            case TransferResponsePacket::RESPONSE_GROUP_NOT_FOUND:
                $reason = "Invalid group";
                break;
            case TransferResponsePacket::RESPONSE_SERVER_NOT_FOUND:
                $reason = "Invalid server";
                break;
            case TransferResponsePacket::RESPONSE_ALREADY_ON_SERVER:
                $reason = "Player is already connected to that server";
                break;
            case TransferResponsePacket::RESPONSE_PLAYER_NOT_FOUND:
                $reason = "Invalid player";
        }

        $player = Server::getInstance()->getPlayerByUUID($packet->uuid);
        $name = $packet->uuid->toString();
        if($player !== null) {
            $name = $player->getName();
        }


        OpenAPI::getInstance()->getLogger()->info("Error whilst transferring player {$name}: {$reason}");
    }
}