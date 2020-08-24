<?php

declare(strict_types=1);

namespace bedrockplay\openapi\ranks;

use bedrockplay\openapi\mysql\query\UpdateRowQuery;
use bedrockplay\openapi\mysql\QueryQueue;
use bedrockplay\openapi\OpenAPI;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

/**
 * Class RankDatabase
 * @package bedrockplay\openapi\ranks
 */
class RankDatabase {

    public const UPDATE_NONE = 0;
    public const UPDATE_GUEST_TO_VIP = 1;
    public const UPDATE_GUEST_TO_MVP = 2;
    public const UPDATE_VIP_TO_MVP = 3;
    public const UPDATE_MVP_TO_BEDROCK = 4;
    public const UPDATE_BEDROCK_TO_MVP = 5;
    public const UPDATE_BEDROCK_TO_BEDROCK = 6;

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
            new Rank("Guest", "§b§l", [], false)
        ];

        foreach ($ranks as $rank) {
            self::$ranks[strtolower($rank->getName())] = $rank;
        }
    }

    /**
     * @param Player $player
     * @param string $rank
     * @param bool $saveToDatabase
     */
    public static function savePlayerRank(Player $player, string $rank, bool $saveToDatabase = false) {
        /** @var Rank|null $rankClass */
        $rankClass = self::$ranks[strtolower($rank)] ?? null;
        if($rankClass === null) {
            $player->kick("Invalid rank ($rank)");
            OpenAPI::getInstance()->getLogger()->error("Invalid rank received from database ($rank)");
            return;
        }

        $player->namedtag->setString("Rank", $rankClass->getName());

        $player->recalculatePermissions();
        foreach ($rankClass->getPermissions() as $permission) {
            $player->addAttachment(OpenAPI::getInstance(), $permission, true);
        }

        if($saveToDatabase) {
            QueryQueue::submitQuery(new UpdateRowQuery(["Rank" => $rankClass->getName()], "Name", $player->getName()));
        }
    }

    /**
     * @param Player $player
     * @param int $update
     */
    public static function saveRankUpdate(Player $player, int $update = self::UPDATE_NONE) {
        if($player->namedtag === null) {
            $player->namedtag = new CompoundTag();
        }
        $player->namedtag->setInt("RankUpdate", $update);
    }

    /**
     * @param Player $player
     * @return int
     */
    public static function applyRankUpdate(Player $player): int {
        $update = $player->namedtag->getInt("RankUpdate");
        $player->namedtag->removeTag("RankUpdate");

        return $update;
    }

    /**
     * @param Player $player
     * @return Rank
     */
    public static function getPlayerRank(Player $player): Rank {
        return self::$ranks[strtolower($player->namedtag->getString("Rank"))];
    }

    /**
     * @param string $rank
     * @return Rank|null
     */
    public static function getRankByName(string $rank): ?Rank {
        return self::$ranks[strtolower($rank)] ?? null;
    }
}