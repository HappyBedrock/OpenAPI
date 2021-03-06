<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;

class AddTokensQuery extends AsyncQuery {

    /** @var string */
    public $player;
    /** @var int */
    public $amount;

    public function __construct(string $player, int $amount) {
        $this->player = $player;
        $this->amount = $amount;
    }

    public function query(mysqli $mysqli): void {
        $mysqli->query("UPDATE " . DatabaseData::TABLE_PREFIX . "_" . DatabaseData::DEFAULT_TABLE . " SET Tokens=Tokens+{$this->amount} WHERE Name='{$this->player}'");
    }
}