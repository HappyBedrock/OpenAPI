<?php

declare(strict_types=1);

namespace happybe\openapi\servers;

use pocketmine\Player;

class ServerGroup {

    public const ONLINE_LIMIT = 30;

    /** @var string */
    private $groupName;
    /** @var Server[] */
    private $servers = [];

    public function __construct(string $groupName) {
        $this->groupName = $groupName;
    }

    public function canAddServer(Server $server): bool {
        return strpos($server->getServerName(), $this->getGroupName()) !== false;
    }

    public function addServer(Server $server) {
        $this->servers[] = $server;
    }

    public function getOnlinePlayers(): int {
        $online = 0;
        foreach ($this->servers as $server) {
            $online += $server->getOnlinePlayers();
        }

        return $online;
    }

    /**
     * Returns server which has player count < self::ONLINE_LIMIT is online
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
            if(!$servers[$name]->isOnline()) {
                continue;
            }
            if(
                ($servers[$name]->getWhitelistState() > 2 && !$player->hasPermission("happype.operator")) ||
                ($servers[$name]->getWhitelistState() > 1 && !$player->hasPermission("happype.vip")) ||
                ($servers[$name]->getWhitelistState() > 0 && !$player->hasPermission("happype.voter"))
            ) {
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

    public function getGroupName(): string {
        return $this->groupName;
    }
}