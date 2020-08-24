<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql\query;

use bedrockplay\openapi\math\TimeFormatter;
use bedrockplay\openapi\mysql\AsyncQuery;
use bedrockplay\openapi\mysql\DatabaseData;
use mysqli;
use pocketmine\Server;

/**
 * Class CheckBanQuery
 * @package bedrockplay\openapi\mysql\query
 */
class CheckBanQuery extends AsyncQuery {
    use TimeFormatter;

    /** @var string $player */
    public $player;

    /** @var bool $banned */
    public $banned = false;
    /** @var string|array $banData */
    public $banData;

    /**
     * CheckBanQuery constructor.
     * @param string $player
     */
    public function __construct(string $player) {
        $this->player = $player;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_Bans WHERE Name='{$this->player}';");
        if($result->num_rows === 0) {
            return;
        }

        $row = $result->fetch_assoc();
        if(time() > (int)$row["Time"]) {
            $mysqli->query("DELETE FROM " . DatabaseData::TABLE_PREFIX . "_Bans WHERE Name='{$this->player}';");
            return;
        }

        $this->banned = true;
        $this->banData = serialize($row);
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server) {
        if($this->banData !== null) {
            $this->banData = (array)unserialize($this->banData);
        }
        parent::onCompletion($server);
    }
}