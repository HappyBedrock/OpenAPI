<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;

class ConnectQuery extends AsyncQuery {

    /** @var bool */
    public $connected = false;

    public function query(mysqli $mysqli): void {
        $this->connected = true;
    }
}