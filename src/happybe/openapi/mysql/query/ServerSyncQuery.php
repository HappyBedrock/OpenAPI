<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use happybe\openapi\mysql\DatabaseData;
use mysqli;
use pocketmine\Server;

/**
 * Class ServerSyncQuery
 * @package happybe\openapi\mysql\query
 */
class ServerSyncQuery extends AsyncQuery {

    /** @var string $currentServer */
    public $currentServer;
    /** @var int $onlinePlayers */
    public $onlinePlayers;

    /** @var string|array $table */
    public $table;

    /**
     * ServerSyncQuery constructor.
     *
     * @param string $currentServer
     * @param int $onlinePlayers
     */
    public function __construct(string $currentServer, int $onlinePlayers) {
        $this->currentServer = $currentServer;
        $this->onlinePlayers = $onlinePlayers;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $mysqli->query("UPDATE " . DatabaseData::TABLE_PREFIX . "_Servers SET OnlinePlayers='{$this->onlinePlayers}' WHERE ServerName='{$this->currentServer}';");
        $result = $mysqli->query("SELECT * FROM " . DatabaseData::TABLE_PREFIX . "_Servers;");

        $table = [];
        while ($row = $result->fetch_assoc()) {
            $table[] = $row;
        }

        $this->table = serialize($table);
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server) {
        $this->table = unserialize($this->table);
        parent::onCompletion($server);
    }
}