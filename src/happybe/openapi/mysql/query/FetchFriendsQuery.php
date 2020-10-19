<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;
use pocketmine\Server;

/**
 * Class FetchFriendsQuery
 * @package happybe\openapi\mysql\query
 */
class FetchFriendsQuery extends AsyncQuery {

    /** @var string $player */
    public $player;
    /** @var string|array $friends */
    public $friends;

    /**
     * FetchFriendsQuery constructor.
     * @param string $player
     */
    public function __construct(string $player) {
        $this->player = $player;
    }

    /**
     * @param mysqli $mysqli
     */
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

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server) {
        $this->friends = (array)unserialize($this->friends);
        parent::onCompletion($server);
    }
}