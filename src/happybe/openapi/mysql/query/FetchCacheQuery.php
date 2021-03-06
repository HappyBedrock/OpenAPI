<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;
use pocketmine\Server;

class FetchCacheQuery extends AsyncQuery {

    /** @var string */
    public $player;
    /** @var string */
    public $tables;

    /** @var string|array */
    public $cache;

    public function __construct(string $player, array $tables) {
        $this->player = $player;
        $this->tables = serialize($tables);
    }

    public function query(mysqli $mysqli): void {
        $cache = [];

        foreach (unserialize($this->tables) as $table) {
            $result = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_" . $table . " WHERE Name='{$this->player}';");
            $cache[$table] = $result->fetch_assoc();
        }

        $this->cache = serialize($cache);
    }

    public function complete(Server $server): void {
        $this->cache = unserialize($this->cache);
    }
}