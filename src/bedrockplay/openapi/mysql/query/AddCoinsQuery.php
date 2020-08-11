<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql\query;

use bedrockplay\openapi\mysql\AsyncQuery;
use bedrockplay\openapi\mysql\DatabaseData;
use mysqli;

/**
 * Class AddCoinsQuery
 * @package bedrockplay\openapi\mysql\query
 */
class AddCoinsQuery extends AsyncQuery {

    /** @var string $player */
    public $player;
    /** @var int $amount */
    public $amount;

    /**
     * AddCoinsQuery constructor.
     * @param string $player
     * @param int $amount
     */
    public function __construct(string $player, int $amount) {
        $this->player = $player;
        $this->amount = $amount;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $query = $mysqli->query("SELECT Coins FROM " . DatabaseData::TABLE_PREFIX . "_" . DatabaseData::DEFAULT_TABLE . " WHERE Name='{$this->player}'");
        $status = (int)($query->fetch_assoc()["Coins"] ?? 0);

        $status += $this->amount;
        $status = max(0, $status);

        $mysqli->query("UPDATE " . DatabaseData::TABLE_PREFIX . "_" . DatabaseData::DEFAULT_TABLE . " SET Coins='{$status}' WHERE Name={$this->player}");
    }
}