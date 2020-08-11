<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql\query;

use bedrockplay\openapi\mysql\AsyncQuery;
use bedrockplay\openapi\mysql\DatabaseData;
use mysqli;
use pocketmine\Server;

/**
 * Class FetchTableQuery
 * @package bedrockplay\openapi\mysql\query
 */
class FetchTableQuery extends AsyncQuery {

    /** @var string $table */
    public $table;
    /** @var string|array $rows */
    public $rows;

    /**
     * FetchTableQuery constructor.
     * @param string $table
     */
    public function __construct(string $table) {
        $this->table = $table;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_{$this->table};");

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $this->rows = serialize($rows);
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server) {
        $this->rows = unserialize($this->rows);
        parent::onCompletion($server);
    }
}