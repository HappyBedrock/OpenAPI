<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;

/**
 * Class ConnectQuery
 * @package happybe\openapi\mysql\query
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