<?php

declare(strict_types=1);

namespace bedrockplay\openapi\servers;

class ServerGroup {

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