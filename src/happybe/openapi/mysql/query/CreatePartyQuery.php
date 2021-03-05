<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;

class CreatePartyQuery extends AsyncQuery {

    /** @var string */
    public $owner;
    /** @var string */
    public $server;

    public function __construct(string $owner, string $server) {
        $this->owner = $owner;
        $this->server = $server;
    }

    public function query(mysqli $mysqli): void {
        $mysqli->query("INSERT INTO HB_Parties(Owner, CurrentServer) VALUES ('{$this->owner}', '{$this->server}');");
    }
}