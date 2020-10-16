<?php

declare(strict_types=1);

namespace happybe\openapi\mysql;

use pocketmine\Server;

/**
 * Class QueryQueue
 * @package happybe\openapi\mysql
 */
class QueryQueue {

    /** @var callable[] $callbacks */
    private static $callbacks = [];

    /**
     * @param AsyncQuery $query
     * @param callable|null $callbackFunction
     */
    public static function submitQuery(AsyncQuery $query, ?callable $callbackFunction = null) {
        self::$callbacks[spl_object_hash($query)] = $callbackFunction;

        $query->host = DatabaseData::getHost();
        $query->user = DatabaseData::getUser();
        $query->password = DatabaseData::getPassword();

        Server::getInstance()->getAsyncPool()->submitTask($query);
    }

    /**
     * @param AsyncQuery $query
     */
    public static function activateCallback(AsyncQuery $query) {
        $callable = self::$callbacks[spl_object_hash($query)] ?? null;
        if(is_callable($callable)) {
            $callable($query);
        }
    }
}