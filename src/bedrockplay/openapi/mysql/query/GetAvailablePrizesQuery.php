<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql\query;

use bedrockplay\openapi\mysql\AsyncQuery;
use bedrockplay\openapi\mysql\DatabaseData;
use mysqli;
use pocketmine\Server;

/**
 * Class GetAvailablePrizesQuery
 * @package bedrockplay\openapi\mysql\query
 */
class GetAvailablePrizesQuery extends AsyncQuery {

    /** @var string $player */
    public $player;

    /** @var string|array $particlePrizes */
    public $particlePrizes;
    /** @var string|array $gadgetPrizes */
    public $gadgetPrizes;

    /**
     * GetAvailablePrizesQuery constructor.
     * @param string $player
     */
    public function __construct(string $player) {
        $this->player = $player;
    }

    /**
     * @param mysqli $mysqli
     */
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

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server) {
        $this->particlePrizes = (array)unserialize((string)$this->particlePrizes);
        $this->gadgetPrizes = (array)unserialize((string)$this->gadgetPrizes);
        parent::onCompletion($server);
    }
}