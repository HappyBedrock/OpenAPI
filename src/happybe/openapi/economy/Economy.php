<?php

declare(strict_types=1);

namespace happybe\openapi\economy;

use happybe\openapi\mysql\query\AddCoinsQuery;
use happybe\openapi\mysql\query\FetchValueQuery;
use happybe\openapi\mysql\query\UpdateRowQuery;
use happybe\openapi\mysql\QueryQueue;
use pocketmine\Player;

/**
 * Class Economy
 * @package happybe\openapi\economy
 */
class Economy {

    /**
     * @param Player $player
     * @param int $amount
     */
    public static function addCoins(Player $player, int $amount) {
        QueryQueue::submitQuery(new AddCoinsQuery($player->getName(), $amount));
    }

    /**
     * @param Player $player
     * @param int $amount
     */
    public static function removeCoins(Player $player, int $amount) {
        QueryQueue::submitQuery(new AddCoinsQuery($player->getName(), -$amount));
    }

    /**
     * @param Player $player
     * @param int $amount
     */
    public static function setCoins(Player $player, int $amount) {
        QueryQueue::submitQuery(new UpdateRowQuery(["Coins" => $amount], "Name", $player->getName()));
    }

    /**
     * @param Player $player
     * @param callable $callback
     */
    public static function getCoins(Player $player, callable $callback) {
        QueryQueue::submitQuery(new FetchValueQuery($player->getName(), "Coins"), function (FetchValueQuery $query) use ($callback) {
            $callback((int)$query->value);
        });
    }
}