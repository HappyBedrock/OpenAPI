<?php

declare(strict_types=1);

namespace bedrockplay\openapi;

use bedrockplay\openapi\lang\LanguageManager;
use bedrockplay\openapi\mysql\DatabaseData;
use bedrockplay\openapi\mysql\query\ConnectQuery;
use bedrockplay\openapi\mysql\query\FetchRowQuery;
use bedrockplay\openapi\mysql\query\LazyRegisterQuery;
use bedrockplay\openapi\mysql\QueryQueue;
use bedrockplay\openapi\mysql\TableCache;
use bedrockplay\openapi\ranks\RankDatabase;
use bedrockplay\openapi\scoreboard\ScoreboardBuilder;
use bedrockplay\openapi\servers\ServerManager;
use bedrockplay\openapi\utils\DeviceData;
use bedrockplay\openapi\utils\Utils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\UpdateNotifyEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\network\mcpe\RakLibInterface;
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

            $password = str_repeat("*", strlen($query->password)); // Logger shouldn't store login credentials
            $logger->error("An error occurred whilst connecting to MySQL database (host={$query->host};user={$query->user};password={$password})!");
        });

        RankDatabase::init();
        ServerManager::init();
        LanguageManager::init();

        if(
            !$this->getServer()->getConfigBool("xbox-auth") &&
            $this->getServer()->getConfigString("server-ip", "0.0.0.0") == "0.0.0.0"
        ) {
            $logger->warning("Your server is opened and has turned Xbox Auth OFF. Enable Xbox auth or set server address to 127.0.0.1!");
        }

        foreach ($this->getServer()->getNetwork()->getInterfaces() as $interface) {
            if($interface instanceof RakLibInterface) {
                $interface->setPacketLimit(PHP_INT_MAX);
                $logger->notice("Disabled packet limit");
                break;
            }
        }
    }

    public function onDisable() {
        ServerManager::save();
    }

    /**
     * @param DataPacketReceiveEvent $event
     */
    public function onPacketReceive(DataPacketReceiveEvent $event) {
        $packet = $event->getPacket();
        if($packet instanceof LoginPacket) {
            DeviceData::saveDevice($packet->username, $packet->clientData["DeviceOS"]);
        }
    }

    /**
     * @param PlayerLoginEvent $event
     */
    public function onLogin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();

        QueryQueue::submitQuery(new LazyRegisterQuery($player->getName()), function (LazyRegisterQuery $query) use ($player) {
            RankDatabase::savePlayerRank($player, $query->row["Rank"]);
            RankDatabase::saveRankUpdate($player, $query->update);
            LanguageManager::saveLanguage($player, $query->row["Language"]);
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

        TableCache::handleJoin($player);
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event) {
        ScoreboardBuilder::removeScoreBoard($event->getPlayer());
        TableCache::handleQuit($event->getPlayer());
    }

    /**
     * @param UpdateNotifyEvent $event
     */
    public function onUpdate(UpdateNotifyEvent $event) {
        $updater = $event->getUpdater();
        file_put_contents($this->getServer()->getDataPath() . "PocketMine-MP.phar", Utils::readURL($updater->getUpdateInfo()["download_url"]));

        $this->getLogger()->notice("Restarting server due to update...");
        $this->getServer()->shutdown();
    }

    /**
     * @return OpenAPI
     */
    public static function getInstance(): OpenAPI {
        return self::$instance;
    }
}