<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql\query;

use bedrockplay\openapi\mysql\AsyncQuery;
use bedrockplay\openapi\mysql\DatabaseData;
use mysqli;

/**
 * Class AddPointQuery
 * @package bedrockplay\openapi\mysql\query
 */
class AddPointQuery extends AsyncQuery {

    /** @var string $player */
    public $player;
    /** @var string $table */
    public $table;
    /** @var string $column */
    public $column;

    /**
     * AddPointQuery constructor.
     * @param string $player
     * @param string $table
     * @param string $column
     */
    public function __construct(string $player, string $table, string $column) {
        $this->player = $player;
        $this->table = $table;
        $this->column = $column;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $mysqli->mysqli("UPDATE " . DatabaseData::TABLE_PREFIX . "_{$this->table} SET {$this->column}={$this->column}+1 WHERE Name='{$this->player}';");
    }
}