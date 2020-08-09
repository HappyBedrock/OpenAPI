<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql;

use mysqli;
use pocketmine\scheduler\AsyncTask;

/**
 * Class AsyncQuery
 * @package bedrockplay\openapi\mysql
 */
abstract class AsyncQuery extends AsyncTask {

    final public function onRun() {
        $this->query(new mysqli(DatabaseData::getHost(), DatabaseData::getUser(), DatabaseData::getPassword(), DatabaseData::DATABASE));
    }

    /**
     * @param mysqli $mysqli
     * @return mixed
     */
    abstract function query(mysqli $mysqli);
}