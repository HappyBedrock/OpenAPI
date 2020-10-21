<?php

declare(strict_types=1);

namespace happybe\openapi\floatingtext;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\Player;
use pocketmine\utils\UUID;

/**
 * Class FloatingText
 * @package happybe\openapi\floatingtext
 */
class FloatingText {

    /** @var Vector3 $position */
    private $position;
    /** @var int $entityRuntimeId */
    private $entityRuntimeId;

    /** @var AddPlayerPacket $packet */
    private $packet;

    /**
     * FloatingText constructor.
     * @param Vector3 $position
     */
    public function __construct(Vector3 $position) {
        $this->position = $position;
        $this->entityRuntimeId = Entity::$entityCount++;

        $this->packet = new AddPlayerPacket();
        $this->packet->uuid = UUID::fromRandom();
        $this->packet->entityRuntimeId = $this->getEntityRuntimeId();
        $this->packet->entityUniqueId = $this->getEntityRuntimeId();
        $this->packet->position = $position;
        $this->packet->item = Item::get(0);

        $this->packet->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_IMMOBILE],
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0]
        ];
    }

    /**
     * @param Player $player
     * @param string $text
     */
    public function spawnTo(Player $player, string $text) {
        $pk = clone $this->packet;
        $pk->username = $text;

        $player->dataPacket($pk);
    }

    /**
     * @param Player $player
     */
    public function despawnFrom(Player $player) {
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $this->getEntityRuntimeId();

        $player->dataPacket($pk);
    }

    /**
     * @param string $text
     * @return AddPlayerPacket
     */
    public function encodeSpawnPacket(string $text): AddPlayerPacket {
        $pk = clone $this->packet;
        $pk->username = $text;

        return $pk;
    }

    /**
     * @return RemoveActorPacket
     */
    public function encodeDespawnPacket(): RemoveActorPacket {
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $this->getEntityRuntimeId();

        return $pk;
    }

    /**
     * @return Vector3
     */
    public function getPosition(): Vector3 {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getEntityRuntimeId(): int {
        return $this->entityRuntimeId;
    }
}