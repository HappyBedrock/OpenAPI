<?php

declare(strict_types=1);

namespace happybe\openapi\utils;

use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\Player;

/**
 * Class DeviceData
 * @package happybe\openapi\utils
 */
class DeviceData {

    /** @var int[] $deviceOS */
    private static $deviceOS = [];

    /**
     * @param string $name
     * @param int $os
     */
    public static function saveDevice(string $name, int $os) {
        self::$deviceOS[$name] = $os;
    }

    /**
     * @param Player $player
     */
    public static function unloadPlayer(Player $player) {
        if(isset(self::$deviceOS[$player->getName()])) {
            unset(self::$deviceOS[$player->getName()]);
        }
    }

    /**
     * @param Player $player
     * @return string
     */
    public static function getDeviceName(Player $player): string {
        $deviceOS = self::$deviceOS[$player->getName()] ?? -1;

        switch ($deviceOS) {
            case DeviceOS::ANDROID:
                return "Android"; // Android first :333
            case DeviceOS::IOS:
                return "iOS";
            case DeviceOS::OSX:
                return "OSX";
            case DeviceOS::AMAZON:
                return "Amazon";
            case DeviceOS::GEAR_VR:
                return "Gear VR";
            case DeviceOS::HOLOLENS:
                return "Hololens";
            case DeviceOS::WINDOWS_10:
                return "Windows 10";
            case DeviceOS::WIN32: // WTF
                return "Windows 32";
            case DeviceOS::DEDICATED:
                return "Dedicated";
            case DeviceOS::TVOS:
                return "TV OS";
            case DeviceOS::PLAYSTATION:
                return "PlayStation";
            case DeviceOS::NINTENDO:
                return "Nintendo";
            case DeviceOS::XBOX:
                return "Xbox";
            case DeviceOS::WINDOWS_PHONE: // should be before iOS
                return "Windows Phone";
        }

        return "Unknown";
    }
}