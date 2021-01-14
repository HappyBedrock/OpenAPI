<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;

/**
 * Class AddExperienceQuery
 * @package happybe\openapi\mysql\query
 */
class AddExperienceQuery extends AsyncQuery {

    /** @var string $player */
    public $player;
    /** @var int $experience */
    public $experience;

    /** @var bool $levelUp */
    public $levelUp = false;
    /** @var int $newLevel */
    public $newLevel = 0;

    /**
     * AddExperienceQuery constructor.
     * @param string $player
     * @param int $experience
     */
    public function __construct(string $player, int $experience) {
        $this->player = $player;
        $this->experience = $experience;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT * FROM HB_Values WHERE Name='{$this->player}';")->fetch_assoc();
        $currentExperience = $result["Experience"] ?? null;
        $currentLevel = $result["Level"] ?? null;
        if($currentExperience === null || $currentLevel === null) {
            return;
        }

        $currentExperience = (int)$currentExperience;
        $currentLevel = (int)$currentLevel;

        $requiredExperience = 100 + ($currentLevel * 50);
        if($currentExperience + $this->experience >= $requiredExperience) {
            $this->levelUp = true;
            $this->newLevel = $currentLevel + 1;

            $mysqli->query("UPDATE HB_Values SET Experience='" . (($this->experience + $currentExperience) - $requiredExperience) . "', Level='{$this->newLevel}' WHERE Name='{$this->player};';");
            return;
        }

        $mysqli->query("UPDATE HB_Values SET Experience=Experience+{$this->experience} WHERE Name='{$this->player}';");
    }
}