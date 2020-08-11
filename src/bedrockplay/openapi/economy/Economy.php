<?php

declare(strict_types=1);

namespace bedrockplay\openapi\economy;

use bedrockplay\openapi\mysql\query\AddCoinsQuery;
use bedrockplay\openapi\mysql\query\FetchValueQuery;
use bedrockplay\openapi\mysql\QueryQueue;
use pocketmine\Player;

/**
 * Class Economy
 * @package bedrockplay\openapi\economy
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
     * @param callable $callback
     */
    public static function getCoins(Player $player, callable $callback) {
        QueryQueue::submitQuery(new FetchValueQuery($player->getName(), "Coins"), function (FetchValueQuery $query) use ($callback) {
            $callback((int)$query->value);
        });
    }
}