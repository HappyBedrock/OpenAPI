<?php

declare(strict_types=1);

namespace happybe\openapi\ranks;

use happybe\openapi\mysql\query\UpdateRowQuery;
use happybe\openapi\mysql\QueryQueue;
use happybe\openapi\OpenAPI;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

/**
 * Class RankDatabase
 * @package happybe\openapi\ranks
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
            new Rank("Owner", "§6§lOWNER", ["happybe.operator", "pocketmine.command.gamemode", "pocketmine.command.teleport", "pocketmine.command.kick"]),
            new Rank("Developer", "§6§lDEVELOPER", ["happybe.operator"]),
            new Rank("Admin", "§6§lADMIN", ["happybe.operator", "pocketmine.command.teleport", "pocketmine.command.kick"]),
            new Rank("Mod", "§e§lMOD", ["happybe.moderator", "pocketmine.command.teleport", "pocketmine.command.kick"]),
            new Rank("Helper", "§e§lHELPER", ["happybe.helper", "pocketmine.command.kick"]),
            new Rank("Builder", "§e§lBUILDER", ["happybe.builder"]),
            // Purchasable ranks
            new Rank("Bedrock", "§cBED§bRO§aCK", ["happybe.bedrock"]),
            new Rank("MVP+", "§3§lMVP§b+", ["happybe.mvp.plus"]),
            new Rank("MVP", "§3§lMVP", ["happybe.mvp"]),
            new Rank("VIP", "§3§lVIP", ["happybe.vip"]),
            // Gettable ranks
            new Rank("YouTube", "§c§l", ["happybe.youtube"]),
            new Rank("Voter", "§b§l", ["happybe.voter"]),
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

        if($player->namedtag === null) {
            $player->namedtag = new CompoundTag();
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
     * @param int $voteTime
     * @param bool $hasVoted
     */
    public static function saveHasVoted(Player $player, int $voteTime, bool $hasVoted = true) {
        $today = (int)date("dm");

        if($hasVoted && $voteTime != $today) {
            $hasVoted = false;
            $today = $voteTime;
        }

        if(in_array(strtolower(RankDatabase::getPlayerRank($player)->getName()), ["guest", "voter"])) {
            if($hasVoted) {
                RankDatabase::savePlayerRank($player, "Voter", true);
            } else {
                RankDatabase::savePlayerRank($player, "Guest", true);
            }
        }

        if($hasVoted) {
            $player->addAttachment(OpenAPI::getInstance(), "happybe.voter");
        }

        $player->namedtag->setByte("HasVoted", (int)$hasVoted);
        QueryQueue::submitQuery(new UpdateRowQuery(["HasVoted" => $hasVoted, "VoteDate" => $today], "Name", $player->getName()));
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function hasVoted(Player $player): bool {
        if($player->namedtag === null) {
            return false;
        }
        return (bool)$player->namedtag->getByte("HasVoted", 0);
    }

    /**
     * @param Player $player
     * @return Rank
     */
    public static function getPlayerRank(Player $player): Rank {
        if($player->namedtag === null) {
            return self::$ranks["guest"];
        }
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