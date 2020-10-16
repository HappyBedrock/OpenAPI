<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;

/**
 * Class SubtractKeyQuery
 * @package happybe\openapi\mysql\query
 */
class SubtractKeyQuery extends AsyncQuery {

    /** @var string $player */
    public $player;
    /** @var string $key */
    public $key;
    /** @var string $table */
    public $table;

    /**
     * SubtractKeyQuery constructor.
     * @param string $player
     * @param string $key
     * @param string $table
     */
    public function __construct(string $player, string $key, string $table = "Values") {
        $this->player = $player;
        $this->key = $key;
        $this->table = DatabaseData::TABLE_PREFIX . "_" . $table;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $mysqli->query("UPDATE {$this->table} SET {$this->key}={$this->key}-1 WHERE Name='{$this->player}';");
    }
}