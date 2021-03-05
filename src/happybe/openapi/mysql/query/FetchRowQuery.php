<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;
use pocketmine\Server;

class FetchRowQuery extends AsyncQuery {

    /** @var string */
    public $table;

    /** @var string */
    public $conditionKey;
    /** @var string */
    public $conditionValue;

    /** @var array|string */
    public $row;

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

    public function complete(Server $server): void {
        $this->row = (array)unserialize($this->row);
    }
}