<?php

declare(strict_types=1);

namespace happybe\openapi;

use happybe\openapi\bossbar\BossBarBuilder;
use happybe\openapi\event\LoginQueryReceiveEvent;
use happybe\openapi\form\EntityForm;
use happybe\openapi\lang\LanguageManager;
use happybe\openapi\mysql\DatabaseData;
use happybe\openapi\mysql\query\ConnectQuery;
use happybe\openapi\mysql\query\LazyRegisterQuery;
use happybe\openapi\mysql\QueryQueue;
use happybe\openapi\mysql\TableCache;
use happybe\openapi\party\PartyManager;
use happybe\openapi\ranks\RankDatabase;
use happybe\openapi\scoreboard\ScoreboardBuilder;
use happybe\openapi\servers\ServerManager;
use happybe\openapi\utils\DeviceData;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\NpcRequestPacket;
use pocketmine\network\mcpe\RakLibInterface;
use pocketmine\plugin\PluginBase;

class OpenAPI extends PluginBase implements Listener {

    /** @var OpenAPI */
    private static $instance;

    public function onEnable() {
        self::$instance = $this;
        $this->saveResource("/config.yml", false);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        DatabaseData::init();
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
        if($packet instanceof NpcRequestPacket) {
            $entity = $this->getServer()->findEntity($packet->entityRuntimeId);
            if($entity === null) {
                return;
            }

            $form = EntityForm::getFormByEntity($entity);
            if($form === null) {
                return;
            }

            switch ($packet->requestType) {
                case $packet->actionType:
                    $form->handleResponse($event->getPlayer(), $packet->actionType);
                    break;
            }
        }
    }

    /**
     * @param PlayerLoginEvent $event
     */
    public function onLogin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();

        QueryQueue::submitQuery(new LazyRegisterQuery($player->getName()), function (LazyRegisterQuery $query) use ($player) {
            RankDatabase::setPlayerRank($player, $query->row["Rank"] ?? "ReadError");
            LanguageManager::saveLanguage($player, $query->row["Language"] ?? "ReadError");
            PartyManager::handleLoginQuery($player, $query);

            $player->namedtag->setInt("HappyBedrockLevel", (int)$query->row["Level"] ?? -1);

            (new LoginQueryReceiveEvent($player, $query))->call();
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
        TableCache::handleQuit($event->getPlayer());
        BossBarBuilder::removeBossBar($event->getPlayer());
        PartyManager::handleQuit($event->getPlayer());
        ScoreboardBuilder::removeScoreBoard($event->getPlayer());
    }

    /**
     * @return OpenAPI
     */
    public static function getInstance(): OpenAPI {
        return self::$instance;
    }
}