<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql\query;

use bedrockplay\openapi\mysql\AsyncQuery;
use mysqli;

/**
 * Class ConnectQuery
 * @package bedrockplay\openapi\mysql\query
 */
class ConnectQuery extends AsyncQuery {

    /** @var bool $connected */
    public $connected = false;

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $this->connected = true;
    }
}