<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;

/**
 * Class RemoveFriendQuery
 * @package happybe\openapi\mysql\query
 */
class RemoveFriendQuery extends AsyncQuery {

    /** @var string $player */
    public $player;
    /** @var string $friendToRemove */
    public $friendToRemove;

    /** @var bool $changed */
    public $changed = false;

    /**
     * RemoveFriendQuery constructor.
     * @param string $player
     * @param string $friendToRemove
     */
    public function __construct(string $player, string $friendToRemove) {
        $this->player = $player;
        $this->friendToRemove = $friendToRemove;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $this->removeFriend($mysqli, $this->player, $this->friendToRemove);
        $this->removeFriend($mysqli, $this->friendToRemove, $this->player);
    }

    /**
     * @param mysqli $mysqli
     * @param string $player
     * @param string $friend
     */
    public function removeFriend(mysqli $mysqli, string $player, string $friend) {
        $result = $mysqli->query("SELECT * FROM HB_Friends WHERE Name='{$player}';");

        if($result->num_rows === 0) {
            return;
        }

        $friends = explode(",", $result["Friends"]);
        if(in_array($friend, $friends)) {
            $this->changed = true;
            $key = array_search($friend, $friends);
            unset($friends[$key]);

            $mysqli->query("UPDATE HB_Friends SET HB_Friends='".implode(",", $friends)."' WHERE Name='{$player}';");
            if($mysqli->error) {
                echo "Encountered error while removing friend - {$mysqli->error}\n";
            }
        }
    }
}