<?php

declare(strict_types=1);

namespace bedrockplay\openapi\scoreboard;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;

/**
 * Class ScoreboardBuilder
 * @package bedrockplay\openapi\scoreboard
 */
class ScoreboardBuilder {

    /** @var array $scoreBoards */
    private static $scoreBoards = [];
    /** @var array $titles */
    private static $titles = [];

    /**
     * Sends text as a scoreboard to the player
     *
     * @param Player $player
     * @param string $text
     */
    public static function sendScoreBoard(Player $player, string $text) {
        $text = self::formatLines($text);
        $text = self::removeDuplicateLines($text);
        
        $splitText = explode("\n", $text);
        $title = array_shift($splitText);

        if(!isset(self::$titles[$player->getName()]) || self::$titles[$player->getName()] !== $title) {
            if(isset(self::$titles[$player->getName()])) {
                self::removeScoreBoard($player);
            }

            self::createScoreBoard($player, self::$titles[$player->getName()] = $title);
        }

        if(!isset(self::$scoreBoards[$player->getName()])) {
            self::sendLines($player, $splitText);
            self::$scoreBoards[$player->getName()] = $splitText;
            return;
        }

        self::updateLines($player, $splitText);
        self::$scoreBoards[$player->getName()] = $splitText;
    }

    /**
     * Removes scoreboard from player
     *
     * @param Player $player
     */
    public static function removeScoreBoard(Player $player) {
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = strtolower($player->getName());

        $player->dataPacket($pk);

        unset(self::$titles[$player->getName()]);
    }

    /**
     * Creates objective which can display lines
     *
     * @param Player $player
     * @param string $title
     */
    private static function createScoreBoard(Player $player, string $title) {
        $pk = new SetDisplayObjectivePacket();
        $pk->objectiveName = strtolower($player->getName());
        $pk->displayName = $title;
        $pk->sortOrder = 0; // Ascending
        $pk->criteriaName = "dummy";
        $pk->displaySlot = "sidebar";

        $player->dataPacket($pk);
    }

    /**
     * Displays lines
     *
     * @param Player $player
     * @param array $splitText
     */
    private static function sendLines(Player $player, array $splitText) {
        $entries = [];
        foreach ($splitText as $i => $line) {
            $entry = new ScorePacketEntry();
            $entry->objectiveName = strtolower($player->getName());
            $entry->scoreboardId = $i + 1;
            $entry->score = $i + 1; // Lmao it works :,D
            $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
            $entry->customName = $line;

            $entries[] = $entry;
        }

        $pk = new SetScorePacket();
        $pk->type = SetScorePacket::TYPE_CHANGE;
        $pk->entries = $entries;

        $player->dataPacket($pk);
    }

    /**
     * Updates scoreboard
     *
     * @param Player $player
     * @param array $splitText
     */
    private static function updateLines(Player $player, array $splitText) {
        // Removing old lines
        $entries = [];
        for($i = 0; $i < 15; $i++) {
            $entry = new ScorePacketEntry();
            $entry->objectiveName = strtolower($player->getName());
            $entry->scoreboardId = $i + 1;
            $entry->score = $i + 1;

            $entries[] = $entry;
        }

        $pk = new SetScorePacket();
        $pk->type = SetScorePacket::TYPE_REMOVE;
        $pk->entries = $entries;

        $player->dataPacket($pk);

        self::sendLines($player, $splitText);
    }


    /**
     * Client removes duplicate lines, so we must add edit them to be different
     *
     * @param string $text
     * @return string
     */
    private static function removeDuplicateLines(string $text): string {
        $lines = explode("\n", $text);

        $used = [];
        foreach ($lines as $i => $line) {
            if($i === 0) {
                continue; // Title
            }

            while (in_array($line, $used)) {
                $line .= " ";
            }

            $lines[$i] = $line;
            $used[] = $line;
        }

        return implode("\n", $lines);
    }

    /**
     * Adds " " to begin of every line
     *
     * @param string $text
     * @return string
     */
    private static function formatLines(string $text): string {
        $lines = explode("\n", $text);
        foreach ($lines as $i => $line) {
            if($i === 0) {
                continue;
            }

            $lines[$i] = " " . $line;
        }

        return implode("\n", $lines);
    }
}