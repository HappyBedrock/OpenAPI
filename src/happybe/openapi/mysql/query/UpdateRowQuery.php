<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;

class UpdateRowQuery extends AsyncQuery {

    /** @var string */
    public $updates;
    /** @var string */
    public $conditionKey;
    /** @var string  */
    public $conditionValue;
    /** @var string  */
    public $table;

    public function __construct(array $updates, string $conditionKey, string $conditionValue, string $table = null) {
        $this->updates = serialize($updates);
        $this->conditionKey = $conditionKey;
        $this->conditionValue = $conditionValue;

        if($table === null) {
            $table = DatabaseData::DEFAULT_TABLE;
        }
        $this->table = $table;
    }

    public function query(mysqli $mysqli): void {
        $updates = [];
        foreach (unserialize($this->updates) as $k => $v) {
            $updates[] = "$k='$v'";
        }

        $mysqli->query("UPDATE " . DatabaseData::TABLE_PREFIX . "_{$this->table} SET " . implode(",", $updates) . " WHERE {$this->conditionKey}='{$this->conditionValue}';");
        if($mysqli->error) {
            var_dump($mysqli->error);
        }
    }
}