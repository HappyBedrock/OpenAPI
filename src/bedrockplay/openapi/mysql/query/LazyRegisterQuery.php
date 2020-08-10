<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql\query;

use bedrockplay\openapi\mysql\AsyncQuery;
use bedrockplay\openapi\mysql\DatabaseData;
use mysqli;

/**
 * Class LazyRegisterQuery
 * @package bedrockplay\openapi\mysql\query
 */
class LazyRegisterQuery extends AsyncQuery {

    /** @var string $tablesToRegister */
    public static $tablesToRegister = null;

    /** @var string $player */
    public $player;

    /**
     * LazyRegisterQuery constructor.
     * @param string $player
     */
    public function __construct(string $player) {
        $this->player = $player;
    }

    /**
     * @param mysqli $mysqli
     * @return void
     */
    public function query(mysqli $mysqli): void {
        $check = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_" . DatabaseData::DEFAULT_TABLE . " WHERE Name='{$this->player}'");
        if(!is_null($check->fetch_assoc())) {
            return;
        }

        foreach (unserialize(self::$tablesToRegister) as $table) {
            $mysqli->query("INSERT INTO $table (Name) VALUES ({$this->player})");
        }
    }

    /**
     * @param string $table
     */
    public static function addTableToRegister(string $table) {
        $name = DatabaseData::TABLE_PREFIX . "_" . $table;

        if(self::$tablesToRegister === null) {
            self::$tablesToRegister = serialize([$name]);
            return;
        }

        $tables = unserialize(self::$tablesToRegister);
        $tables[] = $name;
        self::$tablesToRegister = serialize($tables);
    }
}