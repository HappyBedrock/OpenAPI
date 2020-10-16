<?php

declare(strict_types=1);

namespace happybe\openapi\stats;

use happybe\openapi\mysql\query\AddPointQuery;
use happybe\openapi\mysql\QueryQueue;
use pocketmine\Player;

/**
 * Class Stats
 * @package happybe\openapi\stats
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