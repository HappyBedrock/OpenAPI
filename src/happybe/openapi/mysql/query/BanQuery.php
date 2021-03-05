<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;

class BanQuery extends AsyncQuery {

    /** @var string */
    public $table = "Bans";

    /** @var string */
    public $player;
    /** @var string */
    public $admin;
    /** @var int */
    public $time;
    /** @var string */
    public $reason;

    public function __construct(string $player, string $admin, int $time, string $reason) {
        $this->player = $player;
        $this->admin = $admin;
        $this->time = $time;
        $this->reason = $reason;
    }

    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_{$this->table} WHERE Name='{$this->player}'");
        if($result->num_rows === 0) {
            $mysqli->query("INSERT INTO " . DatabaseData::TABLE_PREFIX . "_{$this->table} (Name, Admin, Time, Reason) VALUES ('{$this->player}', '{$this->admin}', '{$this->time}', '{$this->reason}');");
            return;
        }

        $mysqli->query("UPDATE " . DatabaseData::TABLE_PREFIX . "_{$this->table} SET Admin='{$this->admin}', Time='{$this->time}', Reason='{$this->reason}' WHERE Name='{$this->player}';");
    }
}