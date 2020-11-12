<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;

/**
 * Class AddFriendQuery
 * @package happybe\openapi\mysql\query
 */
class AddFriendQuery extends AsyncQuery {

    /** @var string $player */
    public $player;
    /** @var string $newFriend */
    public $newFriend;

    /** @var bool $changed */
    public $changed = false;

    /**
     * AddFriendQuery constructor.
     * @param string $player
     * @param string $newFriend
     */
    public function __construct(string $player, string $newFriend) {
        $this->player = $player;
        $this->newFriend = $newFriend;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $this->addFriend($mysqli, $this->player, $this->newFriend);
        $this->addFriend($mysqli, $this->newFriend, $this->player);
    }

    /**
     * @param mysqli $mysqli
     * @param string $player
     * @param string $friend
     */
    public function addFriend(mysqli $mysqli, string $player, string $friend) {
        $result = $mysqli->query("SELECT * FROM HB_Friends WHERE Name='{$player}';");
        if($result->num_rows === 0) {
            return;
        }

        $result = $result->fetch_assoc();
        $friendList = [];
        if($result["Friends"] !== "") {
            $friendList = explode(",", $result["Friends"]);
        }

        if(!in_array($friend, $friendList)) {
            $this->changed = true;
            $friendList[] = $friend;

            $mysqli->query("UPDATE HB_Friends SET Friends='".implode(",", $friendList)."' WHERE Name='{$player}';");
            if($mysqli->error) {
                echo "Encountered MySQL error: {$mysqli->error}\n";
            }
        }
    }
}