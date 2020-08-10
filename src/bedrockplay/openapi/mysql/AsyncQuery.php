<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql;

use Exception;
use mysqli;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

/**
 * Class AsyncQuery
 * @package bedrockplay\openapi\mysql
 */
abstract class AsyncQuery extends AsyncTask {

    /** @var string $host */
    public $host;
    /** @var string $user */
    public $user;
    /** @var string $password */
    public $password;

    final public function onRun() {
        try {
            $this->query(new mysqli($this->host, $this->user, $this->password, DatabaseData::DATABASE));
        }
        catch (Exception $exception) {}
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server) {
        parent::onCompletion($server);

        QueryQueue::activateCallback($this);
    }

    /**
     * @param mysqli $mysqli
     */
    abstract public function query(mysqli $mysqli): void;
}