<?php

declare(strict_types=1);

namespace happybe\openapi\portal;

use happybe\openapi\event\PortalPacketReceiveEvent;
use happybe\openapi\OpenAPI;
use happybe\openapi\portal\packets\PortalPacket;
use happybe\openapi\portal\thread\PortalThread;
use happybe\openapi\servers\ServerManager;
use pocketmine\Server;
use pocketmine\snooze\SleeperNotifier;

class PortalConnection {

    private const TCP_SOCKET_IP = "193.70.81.203"; // todo - move this to config
    private const TCP_SOCKET_PORT = 19131;

    /** @var PortalThread */
    private $thread;

    public function __construct() {
        $this->thread = new PortalThread(
            self::TCP_SOCKET_IP,
            self::TCP_SOCKET_PORT,
            OpenAPI::getInstance()->getConfig()->get("portal-secret"),
            ServerManager::getCurrentServer()->getServerName(),
            ServerManager::getCurrentServerGroup()->getGroupName(),
            ServerManager::getCurrentServer()->getServerAddress() . ":" . ServerManager::getCurrentServer()->getServerPort(),
            $notifier = new SleeperNotifier()
        );

        Server::getInstance()->getTickSleeper()->addNotifier($notifier, function (): void {
            while (($buffer = $this->thread->getBuffer()) !== null) {
                $packet = PacketPool::getPacketByBuffer($buffer);
                if ($packet instanceof PortalPacket) {
                    $packet->decode();
                    $packet->handlePacket();

                    (new PortalPacketReceiveEvent($packet))->call();
                }
            }
        });
    }

    public function sendPacketToProxy(PortalPacket $packet) {
        $this->thread->addPacketToQueue($packet);
    }

    public function close() {
        $this->thread->quit();
    }
}