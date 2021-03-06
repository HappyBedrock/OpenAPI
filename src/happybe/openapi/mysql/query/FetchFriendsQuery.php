<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;
use pocketmine\Server;

class FetchFriendsQuery extends AsyncQuery {

    /** @var string */
    public $player;
    /** @var string|array */
    public $friends;

    public function __construct(string $player) {
        $this->player = $player;
    }

    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT * FROM HB_Friends WHERE Name='{$this->player}';");
        if($result->num_rows === 0) {
            $this->friends = serialize([]);
            return;
        }

        $friends = $result->fetch_assoc()["Friends"] ?? "";
        if($friends === "") {
            $this->friends = serialize([]);
            return;
        }

        $this->friends = serialize(explode(",", $friends));
    }

    public function complete(Server $server) {
        $this->friends = (array)unserialize($this->friends);
    }
}