<?php

declare(strict_types=1);

namespace happybe\openapi\event;

use happybe\openapi\mysql\query\LazyRegisterQuery;
use pocketmine\event\player\PlayerEvent;
use pocketmine\Player;

/**
 * Class LoginQueryReceiveEvent
 * @package happybe\openapi\event
 */
class LoginQueryReceiveEvent extends PlayerEvent {

    /** @var LazyRegisterQuery $query */
    protected $query;

    /**
     * LoginQueryReceiveEvent constructor.
     * @param Player $player
     * @param LazyRegisterQuery $query
     */
    public function __construct(Player $player, LazyRegisterQuery $query) {
        $this->player = $player;
        $this->query = $query;
    }

    /**
     * @return LazyRegisterQuery
     */
    public function getQuery(): LazyRegisterQuery {
        return $this->query;
    }
}