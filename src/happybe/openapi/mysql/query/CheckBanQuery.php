<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\math\TimeFormatter;
use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;
use pocketmine\Server;

/**
 * Class CheckBanQuery
 * @package happybe\openapi\mysql\query
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
        $this->banData = (string)serialize($row);
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server) {
        if($this->banData !== null) {
            $this->banData = (array)unserialize((string)$this->banData);
        }
        parent::onCompletion($server);
    }
}