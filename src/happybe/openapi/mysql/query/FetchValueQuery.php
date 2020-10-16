<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;

/**
 * Class FetchValueQuery
 * @package happybe\openapi\mysql\query
 */
class FetchValueQuery extends AsyncQuery {

    /** @var string $player */
    public $player;

    /** @var string $key */
    public $key;
    /** @var string $value */
    public $value;

    /** @var string $table */
    public $table;

    /**
     * FetchValueQuery constructor.
     *
     * @param string $player
     * @param string $key
     * @param string|null $table
     */
    public function __construct(string $player, string $key, ?string $table = null) {
        $this->player = $player;
        $this->key = $key;

        $this->table = DatabaseData::TABLE_PREFIX . "_" . ($table === null ? DatabaseData::DEFAULT_TABLE : $table);
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT {$this->key} FROM {$this->table} WHERE Name='{$this->player}'");
        $row = $result->fetch_assoc();

        $this->value = $row[$this->key] ?? null;
    }
}