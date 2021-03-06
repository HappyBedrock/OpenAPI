<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\math\TimeFormatter;
use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;
use pocketmine\Server;

class CheckBanQuery extends AsyncQuery {
    use TimeFormatter;

    /** @var string */
    public $player;

    /** @var bool */
    public $banned = false;
    /** @var string|array */
    public $banData;

    public function __construct(string $player) {
        $this->player = $player;
    }

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

    public function complete(Server $server): void {
        if($this->banData !== null) {
            $this->banData = (array)unserialize((string)$this->banData);
        }
    }
}