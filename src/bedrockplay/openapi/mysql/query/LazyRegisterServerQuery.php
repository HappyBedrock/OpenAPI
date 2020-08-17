<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql\query;

use bedrockplay\openapi\mysql\AsyncQuery;
use bedrockplay\openapi\mysql\DatabaseData;
use mysqli;

/**
 * Class LazyRegisterServerQuery
 * @package bedrockplay\openapi\mysql\query
 */
class LazyRegisterServerQuery extends AsyncQuery {

    /** @var string $serverName */
    public $serverName;
    /** @var int $serverPort */
    public $serverPort;

    /**
     * LazyRegisterServerQuery constructor.
     * @param string $serverName
     * @param int $serverPort
     */
    public function __construct(string $serverName, int $serverPort) {
        $this->serverName = $serverName;
        $this->serverPort = $serverPort;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_Servers WHERE ServerName='{$this->serverName}';");
        if($result->num_rows === 0) {
            $mysqli->query("INSERT INTO " . DatabaseData::TABLE_PREFIX . "_Servers (ServerName, ServerAlias, ServerPort, IsOnline) VALUES ('{$this->serverName}', '{$this->serverName}', '{$this->serverPort}', '1');");
            return;
        }

        $mysqli->query("UPDATE " . DatabaseData::TABLE_PREFIX . "_Servers SET IsOnline='1',ServerPort='{$this->serverPort}' WHERE ServerName='{$this->serverName}';");
    }
}