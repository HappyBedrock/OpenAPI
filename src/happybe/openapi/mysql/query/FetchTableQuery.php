<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;
use pocketmine\Server;

class FetchTableQuery extends AsyncQuery {

    /** @var string */
    public $table;
    /** @var string|array */
    public $rows;

    public function __construct(string $table) {
        $this->table = $table;
    }

    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_{$this->table};");

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $this->rows = serialize($rows);
    }

    public function complete(Server $server): void {
        $this->rows = unserialize($this->rows);
    }
}