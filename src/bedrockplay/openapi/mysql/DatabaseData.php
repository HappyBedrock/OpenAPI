<?php

declare(strict_types=1);

namespace bedrockplay\openapi\mysql;

/**
 * Class DatabaseData
 * @package bedrockplay\openapi\mysql
 */
class DatabaseData {

    public const DATABASE = "BedrockPlay";

    /** @var string $host */
    private static $host;
    /** @var string $user */
    private static $user;
    /** @var string $password */
    private static $password;

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