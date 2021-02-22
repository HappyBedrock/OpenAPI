<?php

declare(strict_types=1);

namespace happybe\openapi\particles;

use pocketmine\level\particle\Particle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;

/**
 * Class CustomParticle
 * @package happybe\openapi\particles
 */
class CustomParticle extends Particle {

    public const DRAGON_BREATH_TRAIL_PARTICLE = "minecraft:dragon_breath_trail";
    public const DRAGON_BREATH_LINGERING_PARTICLE = "minecraft:dragon_breath_lingering";
    public const VILLAGER_HAPPY_PARTICLE = "minecraft:villager_happy";
    public const MOBSPELL_EMITTER_PARTICLE = "minecraft:mobspell_emitter";
    public const SPARKLER_EMITTER_PARTICLE = "minecraft:sparkler_emitter";
    public const CRITICAL_EMITTER_PARTICLE = "minecraft:critical_hit_emitter";
    public const FLAME_PARTICLE = "minecraft:basic_flame_particle";
    public const CLOUD_PARTICLE = "minecraft:water_evaporation_bucket_emitter";

    /** @var string $name */
    private $name;

    /**
     * CustomParticle constructor.
     * @param string $particleName
     * @param Vector3 $pos
     */
    public function __construct(string $particleName, Vector3 $pos) {
        $this->name = $particleName;
        parent::__construct($pos->getX(), $pos->getY(), $pos->getZ());
    }


    /**
     * @inheritDoc
     */
    public function encode() {
        $pk = new SpawnParticleEffectPacket();
        $pk->position = $this->asVector3();
        $pk->particleName = $this->name;

        return $pk;
    }
}