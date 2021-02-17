<?php

declare(strict_types=1);

namespace happybe\openapi\servers;

use happybe\openapi\math\TimeFormatter;
use happybe\openapi\mysql\query\CheckBanQuery;
use happybe\openapi\mysql\QueryQueue;
use happybe\openapi\portal\packets\TransferRequestPacket;
use pocketmine\Player;

/**
 * Class Server
 * @package happybe\openapi
 */
class Server {
    use TimeFormatter;

    /** @var string $serverName */
    public $serverName;
    /** @var string $serverAlias */
    public $serverAlias;

    /** @var string $serverAddress */
    public $serverAddress;
    /** @var int $serverPort */
    public $serverPort;

    /** @var int $onlinePlayers */
    public $onlinePlayers;
    /** @var bool $isOnline */
    public $isOnline;
    /** @var bool $isWhitelisted */
    public $isWhitelisted;

    /**
     * Server constructor.
     *
     * @param string $serverName
     * @param string $serverAlias
     * @param string $serverAddress
     * @param int $serverPort
     * @param int $onlinePlayers
     * @param bool $isOnline
     * @param bool $isWhitelisted
     */
    public function __construct(string $serverName, string $serverAlias, string $serverAddress, int $serverPort, int $onlinePlayers = 0, bool $isOnline = false, bool $isWhitelisted = false) {
        $this->update($serverName, $serverAlias, $serverAddress, $serverPort, $onlinePlayers, $isOnline, $isWhitelisted);
    }

    /**
     * @param string $serverName
     * @param string $serverAlias
     * @param string $serverAddress
     * @param int $serverPort
     * @param int $onlinePlayers
     * @param bool $isOnline
     * @param bool $isWhitelisted
     */
    public function update(string $serverName, string $serverAlias, string $serverAddress, int $serverPort, int $onlinePlayers = 0, bool $isOnline = false, bool $isWhitelisted = false) {
        $this->serverName = $serverName;
        $this->serverAlias = $serverAlias;
        $this->serverAddress = $serverAddress;
        $this->serverPort = $serverPort;
        $this->onlinePlayers = $onlinePlayers;
        $this->isOnline = $isOnline;
        $this->isWhitelisted = $isWhitelisted;
    }

    /**
     * @param Player $player
     */
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

    /**
     * @return bool
     */
    public function isLobby(): bool {
        return strpos($this->getServerName(), "Hub") !== false;
    }

    /**
     * @return string
     */
    public function getServerName(): string {
        return $this->serverName;
    }

    /**
     * @return string
     */
    public function getServerAlias(): string {
        return $this->serverAlias;
    }

    /**
     * @return string
     */
    public function getServerAddress(): string {
        return $this->serverAddress;
    }

    /**
     * @return int
     */
    public function getServerPort(): int {
        return $this->serverPort;
    }

    /**
     * @return int
     */
    public function getOnlinePlayers(): int {
        return $this->onlinePlayers;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool {
        return $this->isOnline;
    }

    /**
     * @return bool
     */
    public function isWhitelisted(): bool {
        return $this->isWhitelisted;
    }
}