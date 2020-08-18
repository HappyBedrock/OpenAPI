<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql\query;

use bedrockplay\openapi\mysql\AsyncQuery;
use bedrockplay\openapi\mysql\DatabaseData;
use mysqli;
use pocketmine\Server;

/**
 * Class LazyRegisterQuery
 * @package bedrockplay\openapi\mysql\query
 */
class LazyRegisterQuery extends AsyncQuery {

    public const MONTH_IN_SECONDS = 60 * 60 * 24 * 30;

    public const UPDATE_NONE = 0;
    public const UPDATE_GUEST_TO_VIP = 1;
    public const UPDATE_GUEST_TO_MVP = 2;
    public const UPDATE_VIP_TO_MVP = 3;
    public const UPDATE_MVP_TO_BEDROCK = 4;
    public const UPDATE_BEDROCK_TO_MVP = 5;
    public const UPDATE_BEDROCK_TO_BEDROCK = 6;

    /** @var string $tablesToRegister */
    public static $tablesToRegister = null;

    /** @var string $player */
    public $player;
    /** @var int $update */
    public $update = self::UPDATE_NONE;

    /** @var string|array $row */
    public $row;

    /**
     * LazyRegisterQuery constructor.
     * @param string $player
     */
    public function __construct(string $player) {
        $this->player = $player;
    }

    /**
     * @param mysqli $mysqli
     * @return void
     */
    public function query(mysqli $mysqli): void {
        $check = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_" . DatabaseData::DEFAULT_TABLE . " WHERE Name='{$this->player}';");
        if(is_null($row = $check->fetch_assoc())) {
            foreach (unserialize(self::$tablesToRegister) as $table) {
                $mysqli->query("INSERT INTO $table (Name) VALUES ({$this->player})");
            }

            $row = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_" . DatabaseData::DEFAULT_TABLE . " WHERE Name='{$this->player}';")->fetch_assoc();
        }

        /** @var string|null $rankUpdate */
        $rankUpdate = null;

        $expirationQueryResult = $mysqli->query("SELECT * FROM BP_RankExpiration WHERE Name='{$this->player}';");
        if($expirationQueryResult->num_rows > 0) {
            $line = $expirationQueryResult->fetch_assoc();

            if(time() < (int)$line["ExpiryTime"]) {
                $this->update = self::UPDATE_BEDROCK_TO_MVP;
                $rankUpdate = "MVP";
                $mysqli->query("DELETE FROM BP_RankExpiration WHERE Name='{$this->player}';");
            }
        }

        $ranksQueryResult = $mysqli->query("SELECT * FROM BP_RankQueue WHERE Name='{$this->player}';");
        if($ranksQueryResult->num_rows > 0) {
            while ($line = $ranksQueryResult->fetch_assoc()) {
                $currentRank = $row["Rank"];
                $minRank = $line["OldRank"];
                $newRank = $line["Rank"];

                if($this->getRankValue($minRank) > $this->getRankValue($currentRank)) {
                    echo "Received wrong rank {$newRank} (Player {$this->player} already has that rank). Storing rank for later use.\n";
                    continue;
                }

                if($this->getRankValue($newRank) == $this->getRankValue($currentRank) && $this->getRankValue($minRank) <= 2) {
                    echo "Received wrong rank {$newRank} (Player want increase expiration time of lifetime rank). Storing rank for later use\n";
                    continue;
                }


                if(strtolower($newRank) == "bedrock") {
                    if(strtolower($minRank) == "bedrock" && strtolower($currentRank) == "bedrock") {
                        $mysqli->query("DELETE FROM BP_RankQueue WHERE Id='{$line["Id"]}';");
                        $this->update = self::UPDATE_BEDROCK_TO_BEDROCK;

                        $mysqli->query("UPDATE BP_RankExpiration SET ExpiryTime=ExpiryTime+" . (string)self::MONTH_IN_SECONDS . " WHERE Name='{$this->player}';");
                        break;
                    }

                    if(strtolower($newRank) == "bedrock" && strtolower($rankUpdate) == "mvp") {
                        $mysqli->query("DELETE FROM BP_RankQueue WHERE Id='{$line["Id"]}';");
                        $this->update = self::UPDATE_BEDROCK_TO_BEDROCK;
                        $rankUpdate = null;

                        $mysqli->query("INSERT INTO BP_RankExpiration(Name, ExpiryRank, OldRank) VALUES ('{$this->player}', '".self::MONTH_IN_SECONDS."', '{$currentRank}'");
                        break;
                    }

                    if(strtolower($newRank) == "bedrock" && strtolower($currentRank) == "mvp") {
                        $mysqli->query("DELETE FROM BP_RankQueue WHERE Id='{$line["Id"]}';");
                        $this->update = self::UPDATE_MVP_TO_BEDROCK;
                        $rankUpdate = "Bedrock";
                        break;
                    }
                }

                if(strtolower($newRank) == "mvp") {
                    if(strtolower($currentRank) == "vip" && strtolower($minRank) == "vip") {
                        $this->update = self::UPDATE_VIP_TO_MVP;
                    } elseif($currentRank != "vip" && strtolower($minRank) != "vip") {
                        $this->update = self::UPDATE_GUEST_TO_MVP;
                    } else {
                        echo "Player is updating from guest to mvp whilst having vip rank.\n";
                        break;
                    }

                    $mysqli->query("DELETE FROM BP_RankQueue WHERE Id='{$line["Id"]}';");

                    $rankUpdate = "MVP";
                    break;
                }

                if(strtolower($newRank) == "vip") {
                    $mysqli->query("DELETE FROM BP_RankQueue WHERE Id='{$line["Id"]}';");
                    $this->update = self::UPDATE_GUEST_TO_VIP;
                    $rankUpdate = "VIP";
                }
            }
        }

        if($rankUpdate !== null) {
            $mysqli->query("UPDATE BP_Values SET Rank='$rankUpdate' WHERE Name='{$this->player}'");
            $row["Rank"] = $rankUpdate;
        }

        $this->row = serialize($row);
    }

    /**
     * @param string $rank
     * @return int
     */
    public function getRankValue(string $rank): int {
        switch (strtolower($rank)) {
            case "bedrock":
                return 3;
            case "mvp":
                return 2;
            case "vip":
                return 1;
        }

        return 0;
    }

    /**
     * @param string $table
     */
    public static function addTableToRegister(string $table) {
        $name = DatabaseData::TABLE_PREFIX . "_" . $table;

        if(self::$tablesToRegister === null) {
            self::$tablesToRegister = serialize([$name]);
            return;
        }

        $tables = unserialize(self::$tablesToRegister);
        $tables[] = $name;
        self::$tablesToRegister = serialize($tables);
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server) {
        $this->row = unserialize($this->row);
        parent::onCompletion($server);
    }
}