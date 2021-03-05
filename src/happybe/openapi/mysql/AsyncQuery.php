<?php

declare(strict_types=1);

namespace happybe\openapi\mysql;

use Exception;
use mysqli;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\MainLogger;

abstract class AsyncQuery extends AsyncTask {

    /** @var string */
    public $host;
    /** @var string */
    public $user;
    /** @var string */
    public $password;

    final public function onRun() {
        try {
            $this->query($mysqli = new mysqli($this->host, $this->user, $this->password, DatabaseData::DATABASE));
            $mysqli->close();
        }
        catch (Exception $exception) {
            MainLogger::getLogger()->logException($exception);
        }
    }

    final public function onCompletion(Server $server) {
        $this->complete($server);
        QueryQueue::activateCallback($this);
    }

    /**
     * Function for executing the query.
     */
    abstract public function query(mysqli $mysqli): void;

    /**
     * This function should be used for any tasks
     * whose should be executed on main thread
     */
    public function complete(Server $server): void {}
}