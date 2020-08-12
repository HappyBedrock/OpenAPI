<?php

declare(strict_types=1);

namespace bedrockplay\openapi\stats;

use bedrockplay\openapi\mysql\query\AddPointQuery;
use bedrockplay\openapi\mysql\QueryQueue;
use pocketmine\Player;

/**
 * Class Stats
 * @package bedrockplay\openapi\stats
 */
class Stats {

    /**
     * @param Player $player
     * @param string $columnName
     * @param string $table
     */
    public static function addPoint(Player $player, string $columnName, string $table = "Stats") {
        QueryQueue::submitQuery(new AddPointQuery($player->getName(), $table, $columnName));
    }
}