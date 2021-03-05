<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;
use pocketmine\Server;

/**
 * Class LazyRegisterQuery
 * @package happybe\openapi\mysql\query
 */
class LazyRegisterQuery extends AsyncQuery {

    /** @var array $tablesToRegister */
    public static $tablesToRegister = [];

    /** @var string $tablesToPrepare */
    public $tablesToPrepare;

    /** @var string $player */
    public $player;

    /** @var string|array $row */
    public $row;
    /** @var string|array $partiesRow */
    public $partiesRow;
    /** @var string|array $friendsRow */
    public $friendsRow;

    /**
     * LazyRegisterQuery constructor.
     * @param string $player
     */
    public function __construct(string $player) {
        $this->tablesToPrepare = serialize(self::$tablesToRegister);
        $this->player = $player;
    }

    /**
     * @param mysqli $mysqli
     * @return void
     */
    public function query(mysqli $mysqli): void {
        foreach (unserialize($this->tablesToPrepare) as $table) {
            $check = $mysqli->query("SELECT Name FROM $table WHERE Name='{$this->player}';");
            if(!$check || $check->num_rows === 0) {
                $mysqli->query("INSERT INTO {$table} (Name) VALUES ('{$this->player}');");
            }
        }
        $this->row = serialize($mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_Values WHERE Name='{$this->player}';")->fetch_assoc());

        $query = $mysqli->query("SELECT * FROM HB_Parties WHERE FIND_IN_SET('{$this->player}', Members) or Owner='{$this->player}';");
        $this->partiesRow = $query->num_rows === 0 ? serialize([]) : serialize($query->fetch_assoc());

        $query = $mysqli->query("SELECT * FROM HB_Friends WHERE Name='{$this->player}';");
        $this->friendsRow = $query->num_rows === 0 ? serialize([]) : serialize($query->fetch_assoc());
    }

    /**
     * @param string $table
     */
    public static function addTableToRegister(string $table) {
        self::$tablesToRegister[] = DatabaseData::TABLE_PREFIX . "_" . $table;
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server) {
        $this->row = (array)unserialize((string)$this->row);
        $this->partiesRow = (array)unserialize((string)$this->partiesRow);
        $this->friendsRow = (array)unserialize((string)$this->friendsRow);

        parent::onCompletion($server);
    }
}