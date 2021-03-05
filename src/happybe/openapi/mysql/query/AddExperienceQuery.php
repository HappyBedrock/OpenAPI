<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;

class AddExperienceQuery extends AsyncQuery {

    /** @var string */
    public $player;
    /** @var int */
    public $experience;

    /** @var bool */
    public $levelUp = false;
    /** @var int */
    public $newLevel = 0;

    public function __construct(string $player, int $experience) {
        $this->player = $player;
        $this->experience = $experience;
    }

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
            $mysqli->query($query ="UPDATE HB_Values SET Experience='" . (($this->experience + $currentExperience) - $requiredExperience) . "',Level='{$this->newLevel}' WHERE Name='{$this->player}';");
            return;
        }

        $mysqli->query("UPDATE HB_Values SET Experience=Experience+{$this->experience} WHERE Name='{$this->player}';");
    }
}