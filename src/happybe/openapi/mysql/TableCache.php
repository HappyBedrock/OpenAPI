<?php

declare(strict_types=1);

namespace happybe\openapi\mysql;

use happybe\openapi\mysql\query\FetchCacheQuery;
use pocketmine\Player;

/**
 * Class TableCache
 * @package happybe\openapi\mysql
 */
class TableCache {

    /** @var array $tables */
    private static $tables = [];

    /**
     * @param string $string
     */
    public static function addTableToCache(string $string) {
        self::$tables[$string] = [];
    }

    /**
     * @param Player $player
     * @param string $table
     *
     * @return array|null
     */
    public static function getTableFromCache(Player $player, string $table): ?array {
        return self::$tables[$table][$player->getName()] ?? null;
    }

    /**
     * @param Player $player
     */
    public static function handleJoin(Player $player) {
        QueryQueue::submitQuery(new FetchCacheQuery($player->getName(), array_keys(self::$tables)), function (FetchCacheQuery $query) use ($player) {
            foreach ($query->cache as $table => $tableData) {
                self::$tables[$table][$player->getName()] = $tableData;
            }
        });
    }

    /**
     * @param Player $player
     */
    public static function handleQuit(Player $player) {
        foreach (self::$tables as $i => $tables) {
            if(isset($tables[$player->getName()])) {
                unset(self::$tables[$i][$player->getName()]);
            }
        }
    }
}