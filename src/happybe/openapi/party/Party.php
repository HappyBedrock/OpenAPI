<?php

declare(strict_types=1);

namespace happybe\openapi\party;

use happybe\openapi\mysql\query\AddPartyMemberQuery;
use happybe\openapi\mysql\query\RemovePartyMemberQuery;
use happybe\openapi\mysql\query\UpdateRowQuery;
use happybe\openapi\mysql\QueryQueue;
use happybe\openapi\servers\Server;
use happybe\openapi\servers\ServerManager;
use pocketmine\Player;

/**
 * Class Party
 * @package happybe\openapi\party
 */
class Party {

    /** @var bool $isOnline */
    private $isOnline = true;
    /** @var Player $owner */
    private $owner;
    /** @var Player[] $members */
    private $members = [];

    /**
     * Party constructor.
     * @param Player $owner
     */
    public function __construct(Player $owner) {
        $this->owner = $owner;
    }

    /**
     * @param Player $player
     * @param bool $updateInDatabase
     */
    public function addMember(Player $player, bool $updateInDatabase = true) {
        if($updateInDatabase) {
            QueryQueue::submitQuery(new AddPartyMemberQuery($this->getOwner()->getName(), $player->getName()));
        }

        $this->members[$player->getName()] = $player;
    }

    /**
     * @param Player $player
     * @param bool $updateInDatabase
     */
    public function removeMember(Player $player, bool $updateInDatabase = true) {
        if($updateInDatabase) {
            QueryQueue::submitQuery(new RemovePartyMemberQuery($this->getOwner()->getName(), $player->getName()));
        }

        unset($this->members[$player->getName()]);
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function containsPlayer(Player $player): bool {
        return isset($this->members[$player->getName()]);
    }

    /**
     * @param string $message
     */
    public function broadcastMessage(string $message) {
        foreach ($this->getAll() as $player) {
            $player->sendMessage($message);
        }
    }

    /**
     * @param Server $server
     */
    public function transfer(Server $server) {
        QueryQueue::submitQuery(new UpdateRowQuery(["CurrentServer" => $server->getServerName()], "Owner", $this->getOwner()->getName(), "Parties"));
        $this->isOnline = $server->getServerName() == ServerManager::getCurrentServer()->getServerName();

        $server->transferPlayerHere($this->getOwner());
        foreach ($this->getMembers() as $member) {
            $server->transferPlayerHere($member);
        }

        PartyManager::removeParty($this);
    }

    /**
     * @return bool
     */
    public function isOnline(): bool {
        return $this->isOnline;
    }

    /**
     * @return Player[]
     */
    public function getMembers(): array {
        return $this->members;
    }

    /**
     * @return Player[]
     */
    public function getAll(): array {
        $all = $this->getMembers();
        $all[$this->getOwner()->getName()] = $this->getOwner();

        return $all;
    }

    /**
     * @return Player
     */
    public function getOwner(): Player {
        return $this->owner;
    }
}