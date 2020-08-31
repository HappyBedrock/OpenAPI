<?php

declare(strict_types=1);

namespace bedrockplay\openapi\servers;

use bedrockplay\openapi\math\TimeFormatter;
use bedrockplay\openapi\mysql\query\CheckBanQuery;
use bedrockplay\openapi\mysql\QueryQueue;
use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\Player;
use pocketmine\utils\Binary;

/**
 * Class Server
 * @package bedrockplay\openapi
 */
class Server {
    use TimeFormatter;

    /** @var string $serverName */
    public $serverName;
    /** @var string $serverAlias */
    public $serverAlias;

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
     * @param int $serverPort
     * @param int $onlinePlayers
     * @param bool $isOnline
     * @param bool $isWhitelisted
     */
    public function __construct(string $serverName, string $serverAlias, int $serverPort, int $onlinePlayers = 0, bool $isOnline = false, bool $isWhitelisted = false) {
        $this->update($serverName, $serverAlias, $serverPort, $onlinePlayers, $isOnline, $isWhitelisted);
    }

    /**
     * @param string $serverName
     * @param string $serverAlias
     * @param int $serverPort
     * @param int $onlinePlayers
     * @param bool $isOnline
     * @param bool $isWhitelisted
     */
    public function update(string $serverName, string $serverAlias, int $serverPort, int $onlinePlayers = 0, bool $isOnline = false, bool $isWhitelisted = false) {
        $this->serverName = $serverName;
        $this->serverAlias = $serverAlias;
        $this->serverPort = $serverPort;
        $this->onlinePlayers = $onlinePlayers;
        $this->isOnline = $isOnline;
        $this->isWhitelisted = $isWhitelisted;
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

    /**
     * @return bool
     */
    public function isLobby(): bool {
        return strpos($this->getServerName(), "Lobby") !== false;
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

                $player->sendMessage("§9Transfer> §cYou aren't permitted to play on our game servers.");
                $player->sendMessage("§9Ban> §6You are banned by {$admin} until {$until} for {$reason}.");
                return;
            }

            $pk = new ScriptCustomEventPacket();
            $pk->eventName = "bungeecord:main";
            $pk->eventData = Binary::writeShort(7) . "Connect" . Binary::writeShort(strlen($this->serverName)) . $this->serverName;

            $player->dataPacket($pk);
        };

        if(ServerManager::getCurrentServer()->isLobby() && !$this->isLobby()) {
            QueryQueue::submitQuery(new CheckBanQuery($player->getName()), $callback);
            return;
        }

        $callback();
    }
}