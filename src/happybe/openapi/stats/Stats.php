<?php

declare(strict_types=1);

namespace happybe\openapi\stats;

use happybe\openapi\mysql\query\AddExperienceQuery;
use happybe\openapi\mysql\query\AddPointQuery;
use happybe\openapi\mysql\QueryQueue;
use pocketmine\Player;

/**
 * Class Stats
 * @package happybe\openapi\stats
 */
class Stats {

    public static function addExperience(Player $player, int $experience) {
        QueryQueue::submitQuery(new AddExperienceQuery($player->getName(), $experience), function (AddExperienceQuery $query) use ($player) {
            if($query->levelUp && ($player->isOnline())) {
                $player->sendMessage("§l§o§eHAPPYBEDROCK§r§f: §b§lLEVEL UP! §r§aCurrent level: {$query->newLevel}!");
                $player->namedtag->setInt("HappyBedrockLevel", $query->newLevel);
            }
        });
    }

    /**
     * @param Player $player
     * @param string $columnName
     * @param string $table
     */
    public static function addPoint(Player $player, string $columnName, string $table = "Stats") {
        QueryQueue::submitQuery(new AddPointQuery($player->getName(), $table, $columnName));
    }
}