<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;

/**
 * Class CreatePartyQuery
 * @package happybe\openapi\mysql\query
 */
class CreatePartyQuery extends AsyncQuery {

    /** @var string $owner */
    public $owner;
    /** @var string $server */
    public $server;

    /**
     * CreatePartyQuery constructor.
     * @param string $owner
     * @param string $server
     */
    public function __construct(string $owner, string $server) {
        $this->owner = $owner;
        $this->server = $server;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $mysqli->query("INSERT INTO HB_Parties(Owner, CurrentServer) VALUES ('{$this->owner}', '{$this->server}');");
    }
}