<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql\query;

use bedrockplay\openapi\mysql\AsyncQuery;
use bedrockplay\openapi\mysql\DatabaseData;
use mysqli;

/**
 * Class UpdateRowQuery
 * @package bedrockplay\openapi\mysql\query
 */
class UpdateRowQuery extends AsyncQuery {

    /** @var string $from */
    public $updates;
    /** @var string $conditionKey */
    public $conditionKey;
    /** @var string $conditionValue */
    public $conditionValue;
    /** @var string $table */
    public $table;

    /**
     * UpdateRowQuery constructor.
     *
     * @param array $updates
     * @param string $conditionKey
     * @param string $conditionValue
     * @param string|null $table
     */
    public function __construct(array $updates, string $conditionKey, string $conditionValue, string $table = null) {
        $this->updates = serialize($updates);
        $this->conditionKey = $conditionKey;
        $this->conditionValue = $conditionValue;

        if($table === null) {
            $table = DatabaseData::DEFAULT_TABLE;
        }
        $this->table = $table;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $updates = [];
        foreach (unserialize($this->updates) as $k => $v) {
            $updates[] = "$k='$v'";
        }

        $mysqli->query("UPDATE " . DatabaseData::TABLE_PREFIX . "_{$this->table} SET " . implode(",", $updates) . " WHERE {$this->conditionKey}='{$this->conditionValue}';");
    }
}