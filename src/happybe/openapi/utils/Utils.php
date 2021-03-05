<?php

declare(strict_types=1);

namespace happybe\openapi\utils;

use Exception;
use happybe\openapi\OpenAPI;

/**
 * Class Utils
 * @package happybe\openapi\utils
 */
class Utils {

    /**
     * @param string $link
     * @return string
     */
    public static function readURL(string $link): string {
        try {
            return file_get_contents(str_replace(" ", "%20", $link), false, stream_context_create(["ssl" => ["verify_peer" => false, "verify_peer_name" => false]]));
        }
        catch (Exception $exception) {
            OpenAPI::getInstance()->getLogger()->error("Could not read link $link ({$exception->getMessage()}). Trying again in 1 second.");
            sleep(1);
            return self::readURL($link);
        }
    }
}