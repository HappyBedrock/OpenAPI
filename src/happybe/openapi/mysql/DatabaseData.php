<?php

declare(strict_types=1);

namespace happybe\openapi\mysql;

use happybe\openapi\mysql\query\LazyRegisterQuery;

/**
 * Class DatabaseData
 * @package happybe\openapi\mysql
 */
class DatabaseData {

    public const DATABASE = "HappyBE";
    public const TABLE_PREFIX = "HB";
    public const DEFAULT_TABLE = "Values";

    /** @var string $host */
    private static $host;
    /** @var string $user */
    private static $user;
    /** @var string $password */
    private static $password;

    public static function init() {
        LazyRegisterQuery::addTableToRegister("Values");
        LazyRegisterQuery::addTableToRegister("Stats"); // TODO - Create table per minigame
        LazyRegisterQuery::addTableToRegister("Friends");
        LazyRegisterQuery::addTableToRegister("SkyWars");
        LazyRegisterQuery::addTableToRegister("TheBridge");
    }

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     */
    public static function update(string $host, string $user, string $password) {
        self::$host = $host;
        self::$user = $user;
        self::$password = $password;
    }

    /**
     * @return string
     */
    public static function getHost(): string {
        return self::$host;
    }

    /**
     * @return string
     */
    public static function getUser(): string {
        return self::$user;
    }

    /**
     * @return string
     */
    public static function getPassword(): string {
        return self::$password;
    }
}