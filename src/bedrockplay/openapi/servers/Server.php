<?php

declare(strict_types=1);

namespace bedrockplay\openapi\servers;

use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\Player;
use pocketmine\utils\Binary;

/**
 * Class Server
 * @package bedrockplay\openapi
 */
class Server {

    /** @var string $serverName */
    public $serverName;
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
     * @param int $serverPort
     * @param int $onlinePlayers
     * @param bool $isOnline
     * @param bool $isWhitelisted
     */
    public function __construct(string $serverName, int $serverPort, int $onlinePlayers = 0, bool $isOnline = false, bool $isWhitelisted = false) {
        $this->update($serverName, $serverPort, $onlinePlayers, $isOnline, $isWhitelisted);
    }

    /**
     * @param string $serverName
     * @param int $serverPort
     * @param int $onlinePlayers
     * @param bool $isOnline
     * @param bool $isWhitelisted
     */
    public function update(string $serverName, int $serverPort, int $onlinePlayers = 0, bool $isOnline = false, bool $isWhitelisted = false) {
        $this->serverName = $serverName;
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
     * @param Player $player
     */
    public function transferPlayerHere(Player $player) {
        $pk = new ScriptCustomEventPacket();
        $pk->eventName = "bungeecord:main";
        $pk->eventData = Binary::writeShort(7) . "Connect" . Binary::writeShort(strlen($this->serverName)) . $this->serverName;

        $player->dataPacket($pk);
    }
}