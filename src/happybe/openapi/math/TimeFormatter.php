<?php

declare(strict_types=1);

namespace happybe\openapi\math;

/**
 * Trait TimeFormatter
 * @package happybe\openapi\math
 */
trait TimeFormatter {

    /**
     * @param string $string
     * @return int
     */
    public function getTimeFromString(string $string): int {
        $values = [
            "s" => 1,
            "m" => 60,
            "h" => 60 * 60,
            "d" => 60 * 60 * 24,
            "w" => 60 * 60 * 24 * 7,
            "y" => 60 * 60 * 24 * 365
        ];

        $time = 0;
        $temp = "";

        for($i = 0; $i < strlen($string); $i++) {
            if(is_numeric($string[$i])) {
                $temp .= $string[$i];
                if($i + 1 === strlen($string)) {
                    return is_numeric($string) ? (int)$string : 0;
                }
            }
            else {
                if(isset($values[$string[$i]])) {
                    $time += ((int)$temp) * $values[$string[$i]];
                }
                $temp = "";
            }
        }

        return $time;
    }

    /**
     * @param string $time
     * @return bool
     */
    public function canFormatTime(string $time) {
        $isInt = false; // first letter must be numeric
        for($i = 0; $i < strlen($time); $i++) {
            if(is_numeric($time[$i])) {
                $isInt = true;
            }
            else {
                if(!$isInt) {
                    return false;
                }
                $isInt = false;
            }

        }

        return true;
    }

    /**
     * @param int $time
     * @return string
     */
    public function getTimeName(int $time) {
        return gmdate("Y/m/d H:i:s", $time) . " UTC";
    }
}