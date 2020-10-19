<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;

/**
 * Class AddTokensQuery
 * @package happybe\openapi\mysql\query
 */
class AddTokensQuery extends AsyncQuery {

    /** @var string $player */
    public $player;
    /** @var int $amount */
    public $amount;

    /**
     * AddTokensQuery constructor.
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
        $mysqli->query("UPDATE " . DatabaseData::TABLE_PREFIX . "_" . DatabaseData::DEFAULT_TABLE . " SET Tokens=Tokens+{$this->amount} WHERE Name='{$this->player}'");
    }
}