<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql\query;

use bedrockplay\openapi\mysql\AsyncQuery;
use bedrockplay\openapi\mysql\DatabaseData;
use mysqli;
use pocketmine\Server;

/**
 * Class FetchRowQuery
 * @package bedrockplay\openapi\mysql\query
 */
class FetchRowQuery extends AsyncQuery {

    /** @var string $table */
    public $table;

    /** @var string $conditionKey */
    public $conditionKey;
    /** @var string $conditionValue */
    public $conditionValue;

    /** @var array|string $row */
    public $row;

    /**
     * FetchRowQuery constructor.
     *
     * @param string $conditionKey
     * @param string $conditionValue
     * @param string $table
     */
    public function __construct(string $conditionKey, string $conditionValue, string $table = "Values") {
        $this->conditionKey = $conditionKey;
        $this->conditionValue = $conditionValue;

        $this->table = $table;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_{$this->table} WHERE {$this->conditionKey}='{$this->conditionValue}'");
        if($result->num_rows > 0) {
            $this->row = serialize($result->fetch_assoc());
            return;
        }

        $this->row = "";
    }

    public function onCompletion(Server $server) {
        $this->row = (array)unserialize($this->row);
        parent::onCompletion($server);
    }
}