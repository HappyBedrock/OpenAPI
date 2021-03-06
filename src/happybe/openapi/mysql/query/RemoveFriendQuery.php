<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;

class RemoveFriendQuery extends AsyncQuery {

    /** @var string */
    public $player;
    /** @var string */
    public $friendToRemove;

    /** @var bool */
    public $changed = false;

    public function __construct(string $player, string $friendToRemove) {
        $this->player = $player;
        $this->friendToRemove = $friendToRemove;
    }

    public function query(mysqli $mysqli): void {
        $this->removeFriend($mysqli, $this->player, $this->friendToRemove);
        $this->removeFriend($mysqli, $this->friendToRemove, $this->player);
    }

    public function removeFriend(mysqli $mysqli, string $player, string $friend) {
        $result = $mysqli->query("SELECT * FROM HB_Friends WHERE Name='{$player}';");

        if($result->num_rows === 0) {
            return;
        }

        $friends = explode(",", $result->fetch_assoc()["Friends"]);
        if(in_array($friend, $friends)) {
            $this->changed = true;
            $key = array_search($friend, $friends);
            unset($friends[$key]);

            $mysqli->query("UPDATE HB_Friends SET Friends='".implode(",", $friends)."' WHERE Name='{$player}';");
        }
    }
}