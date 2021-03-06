<?php

declare(strict_types=1);

namespace happybe\openapi\servers;

use happybe\openapi\math\TimeFormatter;
use happybe\openapi\mysql\query\CheckBanQuery;
use happybe\openapi\mysql\QueryQueue;
use happybe\openapi\portal\packets\TransferRequestPacket;
use pocketmine\Player;

class Server {
    use TimeFormatter;

    /** @var string */
    public $serverName;
    /** @var string */
    public $serverAlias;

    /** @var string */
    public $serverAddress;
    /** @var int */
    public $serverPort;

    /** @var int */
    public $onlinePlayers;
    /** @var bool */
    public $isOnline;
    /** @var int */
    public $whitelistState;

    public function __construct(string $serverName, string $serverAlias, string $serverAddress, int $serverPort, int $onlinePlayers = 0, bool $isOnline = false, int $whitelistState = 0) {
        $this->update($serverName, $serverAlias, $serverAddress, $serverPort, $onlinePlayers, $isOnline, $whitelistState);
    }

    public function update(string $serverName, string $serverAlias, string $serverAddress, int $serverPort, int $onlinePlayers = 0, bool $isOnline = false, int $whitelistState = 0) {
        $this->serverName = $serverName;
        $this->serverAlias = $serverAlias;
        $this->serverAddress = $serverAddress;
        $this->serverPort = $serverPort;
        $this->onlinePlayers = $onlinePlayers;
        $this->isOnline = $isOnline;
        $this->whitelistState = $whitelistState;
    }

    public function transferPlayerHere(Player $player) {
        $callback = function (CheckBanQuery $query = null) use ($player) {
            if($query !== null && $query->banned && ServerManager::getCurrentServer()->isLobby() && !$this->isLobby()) {
                $admin = $query->banData["Admin"];
                $until = $this->getTimeName((int)$query->banData["Time"]);
                $reason = $query->banData["Reason"];

                $player->sendMessage("§l§o§eTRANSFER§r§f: §bYou aren't permitted to play on our game servers.");
                $player->sendMessage("§l§o§eBAN§r§f: §bYou are banned by {$admin} until {$until} for {$reason}.");
                return;
            }

            $pk = new TransferRequestPacket();
            $pk->uuid = $player->getUniqueId();
            $pk->server = $this->getServerName();
            $pk->group = substr($this->getServerName(), 0, strpos($this->getServerName(), "-"));

            ServerManager::getPortalConnection()->sendPacketToProxy($pk);
        };

        if(ServerManager::getCurrentServer()->isLobby() && !$this->isLobby()) {
            QueryQueue::submitQuery(new CheckBanQuery($player->getName()), $callback);
            return;
        }

        $callback();
    }

    public function isLobby(): bool {
        return strpos($this->getServerName(), "Hub") !== false;
    }

    public function getServerName(): string {
        return $this->serverName;
    }

    public function getServerAlias(): string {
        return $this->serverAlias;
    }

    public function getServerAddress(): string {
        return $this->serverAddress;
    }

    public function getServerPort(): int {
        return $this->serverPort;
    }

    public function getOnlinePlayers(): int {
        return $this->onlinePlayers;
    }

    public function isOnline(): bool {
        return $this->isOnline;
    }

    public function getWhitelistState(): int {
        return $this->whitelistState;
    }

    /**
     * @deprecated
     */
    public function isWhitelisted(?Player $player = null): bool {
        if($player !== null && $player->hasPermission("happybe.vip")) {
            return $this->whitelistState > 1;
        }

        return $this->whitelistState > 0;
    }
}