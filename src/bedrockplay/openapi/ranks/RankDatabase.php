<?php

declare(strict_types=1);

namespace bedrockplay\openapi\ranks;

use bedrockplay\openapi\OpenAPI;
use pocketmine\Player;

/**
 * Class RankDatabase
 * @package bedrockplay\openapi\ranks
 */
class RankDatabase {

    /** @var Rank[] $ranks */
    public static $ranks = [];

    public static function init() {
        $ranks = [
            // Staff
            new Rank("Owner", "§6§l", ["bedrockplay.operator", "pocketmine.command.gamemode", "pocketmine.command.teleport", "pocketmine.command.kick"]),
            new Rank("Developer", "§6§l", ["bedrockplay.operator"]),
            new Rank("Admin", "§6§l", ["bedrockplay.operator", "pocketmine.command.teleport", "pocketmine.command.kick"]),
            new Rank("Mod", "§e§l", ["bedrockplay.moderator", "pocketmine.command.teleport", "pocketmine.command.kick"]),
            new Rank("Helper", "§e§l", ["bedrockplay.helper", "pocketmine.command.kick"]),
            new Rank("Builder", "§e§l", ["bedrockplay.builder"]),
            // Buyable ranks
            new Rank("Bedrock", "§9§l", ["bedrockplay.bedrock"]),
            new Rank("MVP", "§3§l", ["bedrockplay.mvp"]),
            new Rank("VIP", "§3§l", ["bedrockplay.vip"]),
            // Gettable ranks
            new Rank("YouTube", "§c§l", ["bedrockplay.bedrock"]),
            new Rank("Voter", "§b§l", ["bedrockplay.voter"]),
            // Guest
            new Rank("Guest", "§b§l", [])
        ];

        foreach ($ranks as $rank) {
            self::$ranks[strtolower($rank->getName())] = $rank;
        }
    }

    /**
     * @param Player $player
     * @param string $rank
     */
    public static function savePlayerRank(Player $player, string $rank) {
        if(!isset(self::$ranks[strtolower($rank)])) {
            $player->kick("Invalid rank ($rank)");
            OpenAPI::getInstance()->getLogger()->error("Invalid rank received from database ($rank)");
            return;
        }

        $player->namedtag->setString("Rank", $rank);
    }

    /**
     * @param Player $player
     * @return Rank
     */
    public static function getPlayerRank(Player $player): Rank {
        return self::$ranks[strtolower($player->namedtag->getString("Rank"))];
    }
}