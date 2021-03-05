<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;

class AddFriendQuery extends AsyncQuery {

    /** @var string */
    public $player;
    /** @var string */
    public $newFriend;

    /** @var bool */
    public $changed = false;

    public function __construct(string $player, string $newFriend) {
        $this->player = $player;
        $this->newFriend = $newFriend;
    }

    public function query(mysqli $mysqli): void {
        $this->addFriend($mysqli, $this->player, $this->newFriend);
        $this->addFriend($mysqli, $this->newFriend, $this->player);
    }

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