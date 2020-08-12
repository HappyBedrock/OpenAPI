<?php

declare(strict_types=1);

namespace bedrockplay\openapi\lang;

use pocketmine\Player;

/**
 * Class Translator
 * @package bedrockplay\openapi\lang
 */
class Translator {

    public const PREFIX_GAME = "game";
    public const PREFIX_SERVER = "server";
    public const PREFIX_CHAT = "chat";
    public const PREFIX_ACCOUNT = "account";
    public const PREFIX_CRATES = "crates";
    public const PREFIX_ANTICHEAT = "anticheat";
    public const PREFIX_GADGETS = "gadgets";
    public const PREFIX_PARTICLES = "particles";
    public const PREFIX_PETS = "pets";
    public const PREFIX_VANILLA = "vanilla";
    public const PREFIX_SKYWARS = "skywars";
    public const PREFIX_THEBRIDGE = "thebridge";
    public const PREFIX_EGGWARS = "eggwars";
    public const PREFIX_UHCRUN = "uhcrun";
    public const PREFIX_DUELS = "duels";

    public const SUBPREFIX_JOIN = "join";
    public const SUBPREFIX_QUIT = "quit";

    /**
     * @param Player $player
     * @param string $index
     * @param array $params
     *
     * @return string
     */
    public static function translate(Player $player, string $index, array $params = []): string {
        $translatedString = LanguageManager::getLanguage($player)[$index] ?? "NULL";
        foreach ($params as $i => $param) {
            $translatedString = str_replace("{%$i}", $param, $translatedString);
        }

        return $translatedString;
    }

    /**
     * @param Player $player
     * @param string $index
     * @param array $params
     *
     * @return string
     */
    public static function translateMessage(Player $player, string $index, array $params = []): string {
        return self::translate($player, "message.$index", $params);
    }

    /**
     * @param Player $player
     * @param string $index
     * @param string $prefix
     * @param array $params
     *
     * @return string
     */
    public static function translateWithPrefix(Player $player, string $index, string $prefix = Translator::PREFIX_GAME, array $params = []): string {
        return self::translate($player, "prefix.{$prefix}") . self::translate($player, $index, $params);
    }

    /**
     * @param Player $player
     * @param string $index
     * @param string $prefix
     * @param array $params
     *
     * @return string
     */
    public static function translateMessageWithPrefix(Player $player, string $index, string $prefix = Translator::PREFIX_GAME, array $params = []): string {
        return self::translate($player, "prefix.{$prefix}") . self::translateMessage($player, $index, $params);
    }
}