<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;

class SubtractKeyQuery extends AsyncQuery {

    /** @var string */
    public $player;
    /** @var string */
    public $key;
    /** @var string */
    public $table;

    public function __construct(string $player, string $key, string $table = "Values") {
        $this->player = $player;
        $this->key = $key;
        $this->table = DatabaseData::TABLE_PREFIX . "_" . $table;
    }

    public function query(mysqli $mysqli): void {
        $mysqli->query("UPDATE {$this->table} SET {$this->key}={$this->key}-1 WHERE Name='{$this->player}';");
    }
}