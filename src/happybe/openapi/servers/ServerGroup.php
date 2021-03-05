<?php

declare(strict_types=1);

namespace happybe\openapi\servers;

use pocketmine\Player;

class ServerGroup {

    public const ONLINE_LIMIT = 30;

    /** @var string $groupName */
    private $groupName;
    /** @var Server[] $servers */
    private $servers = [];

    /**
     * ServerGroup constructor.
     * @param string $groupName
     */
    public function __construct(string $groupName) {
        $this->groupName = $groupName;
    }

    /**
     * @param Server $server
     * @return bool
     */
    public function canAddServer(Server $server): bool {
        return strpos($server->getServerName(), $this->getGroupName()) !== false;
    }

    /**
     * @param Server $server
     */
    public function addServer(Server $server) {
        $this->servers[] = $server;
    }

    /**
     * @return int
     */
    public function getOnlinePlayers(): int {
        $online = 0;
        foreach ($this->servers as $server) {
            $online += $server->getOnlinePlayers();
        }

        return $online;
    }

    /**
     * Returns server which has player count < self::ONLINE_LIMIT is online
     *
     * @param Player $player
     * @return Server|null
     */
    public function getFitServer(Player $player): ?Server {
        $servers = $this->servers;
        if(isset($servers[ServerManager::getCurrentServer()->getServerName()])) {
            unset($servers[ServerManager::getCurrentServer()->getServerName()]);
        }

        $toSort = array_map(function (Server $server) {return $server->getOnlinePlayers();}, $servers);
        asort($toSort);

        /** @var Server|null $targetServer */
        $targetServer = null;
        foreach ($toSort as $name => $onlinePlayers) {
            if(!$servers[$name]->isOnline() || (!$player->hasPermission("happybe.operator") && $servers[$name]->whitelistState())) {
                continue;
            }

            if($targetServer === null) {
                $targetServer = $servers[$name];
                continue;
            }

            if($onlinePlayers < self::ONLINE_LIMIT) {
                $targetServer = $servers[$name];
            } else {
                break;
            }
        }

        return $targetServer;
    }

    /**
     * @return Server[]
     */
    public function getServers(): array {
        return $this->servers;
    }

    /**
     * @return string
     */
    public function getGroupName(): string {
        return $this->groupName;
    }
}