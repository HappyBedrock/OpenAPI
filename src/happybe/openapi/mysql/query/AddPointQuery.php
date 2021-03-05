<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;

class AddPointQuery extends AsyncQuery {

    /** @var string */
    public $player;
    /** @var string m*/
    public $table;
    /** @var string */
    public $column;

    public function __construct(string $player, string $table, string $column) {
        $this->player = $player;
        $this->table = $table;
        $this->column = $column;
    }

    public function query(mysqli $mysqli): void {
        $mysqli->query("UPDATE " . DatabaseData::TABLE_PREFIX . "_{$this->table} SET {$this->column}={$this->column}+1 WHERE Name='{$this->player}';");
    }
}