<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;

class DestroyPartyQuery extends AsyncQuery {

    /** @var string */
    public $owner;

    public function __construct(string $owner) {
        $this->owner = $owner;
    }

    public function query(mysqli $mysqli): void {
        $mysqli->query("DELETE FROM HB_Parties WHERE Owner='{$this->owner}';");
    }
}