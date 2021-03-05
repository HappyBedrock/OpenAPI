<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;
use pocketmine\Server;

class ServerSyncQuery extends AsyncQuery {

    /** @var string */
    public $currentServer;
    /** @var int */
    public $onlinePlayers;

    /** @var string|array */
    public $table;

    public function __construct(string $currentServer, int $onlinePlayers) {
        $this->currentServer = $currentServer;
        $this->onlinePlayers = $onlinePlayers;
    }

    public function query(mysqli $mysqli): void {
        $mysqli->query("UPDATE " . DatabaseData::TABLE_PREFIX . "_Servers SET OnlinePlayers='{$this->onlinePlayers}' WHERE ServerName='{$this->currentServer}';");
        $result = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_Servers;");

        $table = [];
        while ($row = $result->fetch_assoc()) {
            $table[] = $row;
        }

        $this->table = serialize($table);
    }

    public function complete(Server $server): void {
        $this->table = unserialize((string)$this->table);
    }
}