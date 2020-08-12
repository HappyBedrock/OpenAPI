<?php

declare(strict_types=1);

namespace bedrockplay\openapi\lang;

use bedrockplay\openapi\OpenAPI;
use Exception;
use pocketmine\Player;

/**
 * Class LanguageManager
 * @package bedrockplay\openapi\lang
 */
class LanguageManager {

    public const DEFAULT_LANGUAGE = "eng";

    private const LANGUAGE_VERSION_DATA_LOCAL_PATH = "languages/version.json";
    private const LANGUAGE_VERSION_DATA_ONLINE_PATH = "https://raw.githubusercontent.com/BedrockPlay/Translations/master/version.json";

    /** @var array $languageData*/
    private static $languageData = [];

    public static function init() {
        $dataFolder = OpenAPI::getInstance()->getDataFolder();
        $latestLanguageData = json_decode(self::readLink(self::LANGUAGE_VERSION_DATA_ONLINE_PATH), true);
        $download = false;


        if(!is_dir($dataFolder . "languages")) {
            mkdir($dataFolder . "languages");
            $download = true;
        }
        else {
            $currentLanguageData = json_decode(file_get_contents($dataFolder . self::LANGUAGE_VERSION_DATA_LOCAL_PATH), true);
            if(version_compare($currentLanguageData["version"], $latestLanguageData["version"]) != 0) {
                $download = true;
            }
        }

        if($download) {
            $startTime = microtime(true);
            self::downloadTranslations($dataFolder, $latestLanguageData);
            OpenAPI::getInstance()->getLogger()->info("§aSuccessfully downloaded " . (string)count(self::$languageData) . " languages in " . (string)round(microtime(true)-$startTime, 2) . " seconds!");
        }

        self::loadLanguageData($dataFolder);
    }

    /**
     * @param string $localDataFolder
     * @param array $latestLanguageData
     */
    private static function downloadTranslations(string $localDataFolder, array $latestLanguageData) {
        foreach ($latestLanguageData["languages"] as $langIndex => ["name" => $languageName, "data" => $languageOnlinePath]) {
            file_put_contents($localDataFolder . "languages/" . basename($languageOnlinePath), self::readLink($languageOnlinePath));
        }

        file_put_contents($localDataFolder . self::LANGUAGE_VERSION_DATA_LOCAL_PATH, json_encode($latestLanguageData, JSON_PRETTY_PRINT));
    }

    /**
     * @param string $localDataFolder
     */
    private static function loadLanguageData(string $localDataFolder) {
        foreach (glob($localDataFolder . "languages/*.yml") as $languageFile) {
            self::$languageData[basename($languageFile, ".yml")] = (array)yaml_parse_file($localDataFolder);
        }
    }

    /**
     * @param string $link
     * @return string
     */
    private static function readLink(string $link): string {
        try {
            return file_get_contents($link, false, stream_context_create(["ssl" => ["verify_peer" => false, "verify_peer_name" => false]]));
        }
        catch (Exception $exception) {
            OpenAPI::getInstance()->getLogger()->error("Could not read link $link ({$exception->getMessage()}). Trying again in 1 second.");
            sleep(1);
            return self::readLink($link);
        }
    }

    /**
     * @param Player $player
     * @param string $languageIndex
     *
     * @return bool $isSuccess
     */
    public static function saveLanguage(Player $player, string $languageIndex): bool {
        if(!isset(self::$languageData[$languageIndex])) {
            if(!$player->namedtag->hasTag("Language")) {
                $player->namedtag->setString("Language", self::DEFAULT_LANGUAGE);
            }
            return false;
        }

        $player->namedtag->setString("Language", $languageIndex);
        return true;
    }

    /**
     * @param Player $player
     * @return array
     */
    public static function getLanguage(Player $player): array {
        if(!$player->namedtag->hasTag("Language")) {
            return self::$languageData[self::DEFAULT_LANGUAGE];
        }

        return self::$languageData[$player->namedtag->getString("Language")];
    }
}