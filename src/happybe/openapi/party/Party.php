<?php

declare(strict_types=1);

namespace happybe\openapi\party;

use happybe\openapi\mysql\query\AddPartyMemberQuery;
use happybe\openapi\mysql\query\RemovePartyMemberQuery;
use happybe\openapi\mysql\QueryQueue;
use pocketmine\Player;

/**
 * Class Party
 * @package happybe\openapi\party
 */
class Party {

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
     */
    public function addMember(Player $player) {
        QueryQueue::submitQuery(new AddPartyMemberQuery($this->getOwner()->getName(), $player->getName()));
        $this->members[$player->getName()] = $player;
    }

    /**
     * @param Player $player
     */
    public function removeMember(Player $player) {
        QueryQueue::submitQuery(new RemovePartyMemberQuery($this->getOwner()->getName(), $player->getName()));
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
        /** @var Player $player */
        foreach (array_merge($this->getMembers(), [$this->getOwner()]) as $player) {
            $player->sendMessage($message);
        }
    }

    /**
     * @return Player[]
     */
    public function getMembers(): array {
        return $this->members;
    }

    /**
     * @return Player
     */
    public function getOwner(): Player {
        return $this->owner;
    }
}