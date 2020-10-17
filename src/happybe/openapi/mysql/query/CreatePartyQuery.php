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

    /**
     * CreatePartyQuery constructor.
     * @param string $owner
     */
    public function __construct(string $owner) {
        $this->owner = $owner;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $mysqli->query("INSERT INTO HB_Parties(Owner, Members) VALUES ('{$this->owner}', '');");
    }
}