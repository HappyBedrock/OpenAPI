<?php

declare(strict_types=1);

namespace vixikhd\bpcore\api;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\Player;
use pocketmine\utils\UUID;

/**
 * Class FloatingTextApi
 * @package vixikhd\bpcore\api
 */
class FloatingTextApi {

    /** @var array $texts */
    private static $texts = [];

    /**
     * @param Vector3 $pos
     * @return int
     */
    public static function createText(Vector3 $pos): int {
        $eid = Entity::$entityCount++;

        $pk = new AddPlayerPacket();
        $pk->username = "Text";
        $pk->uuid = UUID::fromRandom();
        $pk->entityRuntimeId = $eid;
        $pk->entityUniqueId = $eid;
        $pk->position = $pos;
        $pk->item = Item::get(0);
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_IMMOBILE],
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0]
        ];

        self::$texts[$eid] = $pk;

        return $eid;
    }

    /**
     * @param int $eid
     * @param Player $player
     * @param string $text
     */
    public static function sendText(int $eid, Player $player, string $text = "Text") {
        /** @var AddPlayerPacket $pk */
        $pk = clone self::$texts[$eid];
        $pk->username = $text;

        $player->dataPacket($pk);
    }

    /**
     * @param int $eid
     * @param Player $player
     */
    public static function removeText(int $eid, Player $player) {
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $eid;
        $player->dataPacket($pk);
    }
}