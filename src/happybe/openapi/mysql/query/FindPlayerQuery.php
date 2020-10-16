<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;

/**
 * Class FindPlayerQuery
 * @package happybe\openapi\mysql\query
 */
class FindPlayerQuery extends AsyncQuery {

    /** @var string $player */
    public $player;
    /** @var string $table */
    public $table;

    /** @var bool $exists */
    public $exists;

    /**
     * FindPlayerQuery constructor.
     * @param string $player
     * @param string $table
     */
    public function __construct(string $player, string $table = "Values") {
        $this->player = $player;
        $this->table = $table;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_{$this->table} WHERE Name='{$this->player}'");
        $this->exists = $result->num_rows !== 0;
    }
}