<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;

class FindPlayerQuery extends AsyncQuery {

    /** @var string */
    public $player;
    /** @var string */
    public $table;

    /** @var bool */
    public $exists;

    public function __construct(string $player, string $table = "Values") {
        $this->player = $player;
        $this->table = $table;
    }

    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_{$this->table} WHERE Name='{$this->player}'");
        $this->exists = $result->num_rows !== 0;
    }
}