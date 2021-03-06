<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;
use pocketmine\Server;

class GetAvailablePrizesQuery extends AsyncQuery {

    /** @var string */
    public $player;

    /** @var string|array */
    public $particlePrizes;
    /** @var string|array  */
    public $gadgetPrizes;

    public function __construct(string $player) {
        $this->player = $player;
    }

    public function query(mysqli $mysqli): void {
        $particlesQuery = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_Particles WHERE Name='{$this->player}';");
        $particlePrizes = [];
        foreach ($particlesQuery->fetch_assoc() as $columnName => $value) {
            if($value == "1") {
                $particlePrizes[] = $columnName;
            }
        }
        $this->particlePrizes = serialize($particlePrizes);

        $gadgetsQuery = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_Gadgets WHERE Name='{$this->player}';");
        $gadgetPrizes = [];
        foreach ($gadgetsQuery->fetch_assoc() as $columnName => $value) {
            if($value == "1") {
                $gadgetPrizes[] = $columnName;
            }
        }
        $this->gadgetPrizes = serialize($gadgetPrizes);
    }

    public function complete(Server $server): void {
        $this->particlePrizes = (array)unserialize((string)$this->particlePrizes);
        $this->gadgetPrizes = (array)unserialize((string)$this->gadgetPrizes);
    }
}