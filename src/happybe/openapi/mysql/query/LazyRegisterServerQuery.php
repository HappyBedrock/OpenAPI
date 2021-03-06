<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;

class LazyRegisterServerQuery extends AsyncQuery {

    /** @var string */
    public $serverName;
    /** @var int */
    public $serverPort;

    public function __construct(string $serverName, int $serverPort) {
        $this->serverName = $serverName;
        $this->serverPort = $serverPort;
    }

    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_Servers WHERE ServerName='{$this->serverName}';");
        if($result->num_rows === 0) {
            $mysqli->query("INSERT INTO " . DatabaseData::TABLE_PREFIX . "_Servers (ServerName, ServerAlias, ServerPort, IsOnline) VALUES ('{$this->serverName}', '{$this->serverName}', '{$this->serverPort}', '1');");
            return;
        }

        $mysqli->query("UPDATE " . DatabaseData::TABLE_PREFIX . "_Servers SET IsOnline='1',ServerPort='{$this->serverPort}' WHERE ServerName='{$this->serverName}';");
    }
}