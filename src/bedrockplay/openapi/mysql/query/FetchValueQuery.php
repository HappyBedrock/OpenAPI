<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql\query;

use bedrockplay\openapi\mysql\AsyncQuery;
use bedrockplay\openapi\mysql\DatabaseData;
use mysqli;

/**
 * Class FetchValueQuery
 * @package bedrockplay\openapi\mysql\query
 */
class FetchValueQuery extends AsyncQuery {

    /** @var string $player */
    public $player;
    /** @var string $key */
    public $key;
    /** @var string $value */
    public $value;

    /**
     * FetchValueQuery constructor.
     * @param string $player
     * @param string $key
     */
    public function __construct(string $player, string $key) {
        $this->player = $player;
        $this->key = $key;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT {$this->key} FROM " . DatabaseData::TABLE_PREFIX . "_" . DatabaseData::DEFAULT_TABLE . " WHERE Name='{$this->player}'");
        $row = $result->fetch_assoc();

        $this->value = $row[$this->key] ?? null;
    }
}