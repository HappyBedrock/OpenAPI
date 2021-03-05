<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;

class FetchValueQuery extends AsyncQuery {

    /** @var string */
    public $player;

    /** @var string */
    public $key;
    /** @var string */
    public $value;

    /** @var string */
    public $table;

    public function __construct(string $player, string $key, ?string $table = null) {
        $this->player = $player;
        $this->key = $key;

        $this->table = DatabaseData::TABLE_PREFIX . "_" . ($table === null ? DatabaseData::DEFAULT_TABLE : $table);
    }

    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT {$this->key} FROM {$this->table} WHERE Name='{$this->player}'");
        $row = $result->fetch_assoc();

        $this->value = $row[$this->key] ?? null;
    }
}