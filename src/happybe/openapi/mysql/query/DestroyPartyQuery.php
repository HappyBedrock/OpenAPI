<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;

/**
 * Class DestroyPartyQuery
 * @package happybe\openapi\mysql\query
 */
class DestroyPartyQuery extends AsyncQuery {

    /** @var string $owner */
    public $owner;

    /**
     * DestroyPartyQuery constructor.
     * @param string $owner
     */
    public function __construct(string $owner) {
        $this->owner = $owner;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $mysqli->query("DELETE FROM HB_Parties WHERE Owner='{$this->owner}';");
    }
}