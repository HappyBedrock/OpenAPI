<?php

declare(strict_types=1);

namespace bedrockplay\openapi;

use bedrockplay\openapi\mysql\DatabaseData;
use bedrockplay\openapi\mysql\query\ConnectQuery;
use bedrockplay\openapi\mysql\query\FetchValueQuery;
use bedrockplay\openapi\mysql\query\LazyRegisterQuery;
use bedrockplay\openapi\mysql\QueryQueue;
use bedrockplay\openapi\ranks\RankDatabase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\plugin\PluginBase;

/**
 * Class OpenAPI
 * @package bedrockplay\openapi
 */
class OpenAPI extends PluginBase implements Listener {

    /** @var OpenAPI $instance */
    private static $instance;

    public function onEnable() {
        self::$instance = $this;
        $this->saveResource("/config.yml", false);

        RankDatabase::init();

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        DatabaseData::update(
            $this->getConfig()->get("mysql-host"),
            $this->getConfig()->get("mysql-user"),
            $this->getConfig()->get("mysql-password")
        );

        $logger = $this->getLogger();
        QueryQueue::submitQuery(new ConnectQuery(), function (ConnectQuery $query) use ($logger) {
            if($query->connected) {
                $logger->info("§aSuccessfully connected to MySQL database!");
                return;
            }

            $password = str_repeat("*", strlen($query->password));
            $logger->error("An error occurred whilst connecting to MySQL database (host={$query->host};user={$query->user};password={$password})!");
        });
    }

    /**
     * @param PlayerLoginEvent $event
     */
    public function onLogin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();

        QueryQueue::submitQuery(new LazyRegisterQuery($player->getName()));
        QueryQueue::submitQuery(new FetchValueQuery($player->getName(), "Rank"), function (FetchValueQuery $query) use ($player) {
            RankDatabase::savePlayerRank($player, $query->value);
        });
    }

    /**
     * @param PlayerJoinEvent $event
     *
     * @priority LOW
     */
    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();

        if(!$player->namedtag->hasTag("Rank")) {
            $player->namedtag->setString("Rank", "Guest");
        }
    }

    /**
     * @return OpenAPI
     */
    public static function getInstance(): OpenAPI {
        return self::$instance;
    }
}