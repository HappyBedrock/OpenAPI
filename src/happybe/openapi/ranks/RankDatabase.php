<?php

declare(strict_types=1);

namespace happybe\openapi\ranks;

use happybe\openapi\mysql\query\UpdateRowQuery;
use happybe\openapi\mysql\QueryQueue;
use happybe\openapi\OpenAPI;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class RankDatabase {

    public const UPDATE_NONE = 0;
    public const UPDATE_GUEST_TO_VIP = 1;
    public const UPDATE_GUEST_TO_MVP = 2;
    public const UPDATE_VIP_TO_MVP = 3;
    public const UPDATE_MVP_TO_BEDROCK = 4;
    public const UPDATE_BEDROCK_TO_MVP = 5;
    public const UPDATE_BEDROCK_TO_BEDROCK = 6;

    /** @var Rank[] */
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
            new Rank("YouTube", "§c§lYOUTUBE", ["happybe.youtube"]),
            new Rank("Voter", "§b§lVOTER", ["happybe.voter"]),
            // Guest
            new Rank("Guest", "", [], false)
        ];

        foreach ($ranks as $rank) {
            self::$ranks[strtolower($rank->getName())] = $rank;
        }
    }

    public static function setPlayerRank(Player $player, string $rank, bool $saveToDatabase = false) {
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

    public static function saveRankUpdate(Player $player, int $update = self::UPDATE_NONE) {
        if($player->namedtag === null) {
            $player->namedtag = new CompoundTag();
        }
        $player->namedtag->setInt("RankUpdate", $update);
    }

    public static function applyRankUpdate(Player $player): int {
        $update = $player->namedtag->getInt("RankUpdate");
        $player->namedtag->removeTag("RankUpdate");

        return $update;
    }

    public static function saveVoteTime(Player $player) {
        QueryQueue::submitQuery(new UpdateRowQuery(["HasVoted" => 1, "VoteDate" => time()], "Name", $player->getName()));
    }

    public static function hasVoted(Player $player): bool {
        return self::getPlayerRank($player)->getName() == "Voter";
    }

    public static function checkRankExpiration(Player $player, int $voteTime) {
        if(self::getPlayerRank($player)->getName() != "Voter") {
            return;
        }
        if($voteTime + 86400 >= time()) {
            return;
        }

        $player->sendMessage("§e§l§oRANKS:§r§f:§b Your VOTER rank expired. Vote again to extend it.");
        if(self::getPlayerRank($player)->getName() == "Voter") {
            self::setPlayerRank($player, "Guest", true);
        }

        QueryQueue::submitQuery(new UpdateRowQuery(["HasVoted" => 0], "Name", $player->getName()));
    }

    public static function getPlayerRank(Player $player): Rank {
        if($player->namedtag === null) {
            return self::$ranks["guest"];
        }
        return self::$ranks[strtolower($player->namedtag->getString("Rank"))];
    }

    public static function getRankByName(string $rank): ?Rank {
        return self::$ranks[strtolower($rank)] ?? null;
    }
}