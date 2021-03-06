<?php

declare(strict_types=1);

namespace happybe\openapi\servers;

use happybe\openapi\mysql\DatabaseData;
use happybe\openapi\mysql\query\LazyRegisterServerQuery;
use happybe\openapi\mysql\query\ServerSyncQuery;
use happybe\openapi\mysql\query\UpdateRowQuery;
use happybe\openapi\mysql\QueryQueue;
use happybe\openapi\OpenAPI;
use happybe\openapi\portal\PacketPool;
use happybe\openapi\portal\PortalConnection;
use mysqli;
use pocketmine\scheduler\ClosureTask;

class ServerManager {

    protected const REFRESH_TICKS = 40;

    /** @var Server[] */
    private static $servers = [];
    /** @var ServerGroup[] */
    private static $serverGroups = [];

    /** @var Server */
    private static $currentServer;

    /** @var PortalConnection */
    private static $portalConnection;

    public static function init() {
        /** @var string $currentServerName */
        $currentServerName = OpenAPI::getInstance()->getConfig()->get("current-server-name");
        self::updateServerData($currentServerName, "null", "172.18.0.1", $currentServerPort = \pocketmine\Server::getInstance()->getConfigInt("server-port"));
        QueryQueue::submitQuery(new LazyRegisterServerQuery($currentServerName, $currentServerPort));

        self::$currentServer = self::getServer($currentServerName);

        OpenAPI::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currentTick): void {
            QueryQueue::submitQuery(new ServerSyncQuery(self::getCurrentServer()->getServerName(), count(\pocketmine\Server::getInstance()->getOnlinePlayers())), function (ServerSyncQuery $query) {
                foreach ((array)$query->table as $row) {
                    if(ServerManager::getCurrentServer()->getServerName() === $row["ServerName"]) {
                        continue;
                    }

                    ServerManager::updateServerData(
                        (string)$row["ServerName"],
                        (string)$row["ServerAlias"],
                        (string)$row["ServerAddress"],
                        (int)$row["ServerPort"],
                        (int)$row["OnlinePlayers"],
                        (bool)($row["IsOnline"] == "1"),
                        (int)($row["WhitelistState"])
                    );
                }
            });
        }), self::REFRESH_TICKS);

        PacketPool::init();
        self::$portalConnection = new PortalConnection();
    }

    public static function save() {
        $query = new UpdateRowQuery(["IsOnline" => 0, "OnlinePlayers" => 0], "ServerName", self::getCurrentServer()->getServerName(), "Servers");
        $query->query(new mysqli(DatabaseData::getHost(), DatabaseData::getUser(), DatabaseData::getPassword(), DatabaseData::DATABASE));

        self::getPortalConnection()->close();
    }

    public static function updateServerData(string $serverName, string $serverAlias, string $serverAddress, int $serverPort, int $onlinePlayers = 0, bool $isOnline = false, int $whitelistState = 0) {
        if(!isset(self::$servers[$serverName])) {
            if(strpos($serverName, "-") === false) {
                return;
            }

            self::$servers[$serverName] = $server = new Server($serverName, $serverAlias, $serverAddress, $serverPort, $onlinePlayers, $isOnline, $whitelistState);
            OpenAPI::getInstance()->getLogger()->info("Registered new server ($serverName)");

            $groupName = substr($serverName, 0, strpos($serverName , "-"));
            $targetGroup = self::getServerGroup($groupName);

            if(is_null($targetGroup)) {
                self::$serverGroups[$groupName] = $group = new ServerGroup($groupName);
                $group->addServer($server);
                return;
            }

            $targetGroup->addServer($server);
            return;
        }

        self::$servers[$serverName]->update($serverName, $serverAlias, $serverAddress, $serverPort, $onlinePlayers, $isOnline, $whitelistState);
    }

    public static function getOnlinePlayers(): int {
        $online = count(\pocketmine\Server::getInstance()->getOnlinePlayers());
        foreach (self::$servers as $server) {
            if($server->getServerName() == self::getCurrentServer()->getServerName()) {
                continue;
            }

            $online += $server->getOnlinePlayers();
        }

        return $online;
    }

    public static function getCurrentServerGroup(): ServerGroup {
        return self::getServerGroup(substr(self::getCurrentServer()->getServerName(), 0, strpos(self::getCurrentServer()->getServerName(), "-")));
    }

    public static function getServerGroup(string $name): ?ServerGroup {
        return self::$serverGroups[$name] ?? null;
    }

    public static function getServer(string $name): ?Server {
        return self::$servers[$name] ?? null;
    }

    /**
     * @return ServerGroup[]
     */
    public static function getServerGroups(): array {
        return self::$serverGroups;
    }

    public static function getCurrentServer(): Server {
        return self::$currentServer;
    }

    public static function getPortalConnection(): PortalConnection {
        return self::$portalConnection;
    }
}