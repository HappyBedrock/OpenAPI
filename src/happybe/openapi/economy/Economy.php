<?php

declare(strict_types=1);

namespace happybe\openapi\economy;

use happybe\openapi\mysql\query\AddTokensQuery;
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
    public static function addTokens(Player $player, int $amount) {
        QueryQueue::submitQuery(new AddTokensQuery($player->getName(), $amount));
    }

    /**
     * @param Player $player
     * @param int $amount
     */
    public static function removeTokens(Player $player, int $amount) {
        QueryQueue::submitQuery(new AddTokensQuery($player->getName(), -$amount));
    }

    /**
     * @param Player $player
     * @param int $amount
     */
    public static function setTokens(Player $player, int $amount) {
        QueryQueue::submitQuery(new UpdateRowQuery(["Tokens" => $amount], "Name", $player->getName()));
    }

    /**
     * @param Player $player
     * @param callable $callback
     */
    public static function getTokens(Player $player, callable $callback) {
        QueryQueue::submitQuery(new FetchValueQuery($player->getName(), "Tokens"), function (FetchValueQuery $query) use ($callback) {
            $callback((int)$query->value);
        });
    }
}