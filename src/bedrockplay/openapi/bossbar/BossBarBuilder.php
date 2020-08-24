<?php

declare(strict_types=1);

namespace bedrockplay\openapi\bossbar;

use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\Player;

/**
 * Class BossBarBuilder
 * @package bedrockplay\openapi\bossbar
 */
class BossBarBuilder {

    /** @var int $bossBarEid */
    private static $bossBarEid;
    /** @var array $bossBars */
    private static $bossBars = [];

    /**
     * @param Player $player
     * @param string $text
     */
    public static function sendBossBarText(Player $player, string $text) {
        if(!isset(self::$bossBars[$player->getName()])) {
            self::$bossBars[$player->getName()] = $text;
            self::createBossEntity($player, $text);
            self::showBossBar($player, $text);
            return;
        }

        if(self::$bossBars[$player->getName()] == $text) {
            return;
        }

        self::$bossBars[$player->getName()] = $text;

        self::updateBossNameTag($player, $text);
        self::updateBossTitle($player, $text);
    }

    /**
     * @param Player $player
     */
    public static function removeBossBar(Player $player) {
        if(!isset(self::$bossBars[$player->getName()])) {
            return;
        }
        unset(self::$bossBars[$player->getName()]);

        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = self::getBossBarEid();
        $player->dataPacket($pk);
    }

    /**
     * @param Player $player
     * @param string $text
     */
    private static function createBossEntity(Player $player, string $text) {
        $pk = new AddActorPacket();
        $pk->type = AddActorPacket::LEGACY_ID_MAP_BC[EntityIds::CREEPER];
        $pk->entityUniqueId = $pk->entityRuntimeId = self::getBossBarEid();
        $pk->position = new Vector3($player->getX(), -10, $player->getZ());
        $pk->motion = new Vector3();
        $pk->attributes[] = Attribute::getAttribute(Attribute::HEALTH)->setMaxValue(100)->setValue(100);
        $pk->metadata = [Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0], Entity::DATA_AIR => [Entity::DATA_TYPE_SHORT, 400], Entity::DATA_MAX_AIR => [Entity::DATA_TYPE_SHORT, 400], Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1], Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $text], Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0]];

        $player->dataPacket($pk);
    }

    /**
     * @param Player $player
     * @param string $text
     */
    private static function showBossBar(Player $player, string $text) {
        $pk = new BossEventPacket();
        $pk->bossEid = self::getBossBarEid();
        $pk->eventType = BossEventPacket::TYPE_SHOW;
        $pk->title = $text;
        $pk->healthPercent = 1;
        $pk->color = 0;
        $pk->overlay = 0;

        $player->dataPacket($pk);
    }

    /**
     * @param Player $player
     * @param string $text
     */
    private static function updateBossNameTag(Player $player, string $text) {
        $pk = new SetActorDataPacket();
        $pk->entityRuntimeId = self::getBossBarEid();
        $pk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $text]];

        $player->dataPacket($pk);
    }

    /**
     * @param Player $player
     * @param string $title
     */
    private static function updateBossTitle(Player $player, string $title) {
        $pk = new BossEventPacket();
        $pk->bossEid = self::getBossBarEid();
        $pk->eventType = BossEventPacket::TYPE_TITLE;
        $pk->title = $title;
        $pk->healthPercent = 1;

        $player->dataPacket($pk);
    }

    /**
     * @return int
     */
    private static function getBossBarEid(): int {
        if(self::$bossBarEid === null) {
            self::$bossBarEid = Entity::$entityCount++;
        }

        return self::$bossBarEid;
    }
}