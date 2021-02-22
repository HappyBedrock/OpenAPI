<?php

declare(strict_types=1);

namespace happybe\openapi\portal\thread;

use happybe\openapi\portal\packets\AuthRequestPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\Thread;
use pocketmine\utils\Binary;
use Threaded;

/**
 * Class PortalThread
 * @package happybe\openapi\portal\thread
 */
class PortalThread extends Thread {

    /** @var string */
    private $host;
    /** @var int */
    private $port;

    /** @var string */
    private $secret;
    /** @var string */
    private $name;
    /** @var string */
    private $group;
    /** @var string */
    private $address;

    /** @var Threaded */
    private $sendQueue;
    /** @var Threaded */
    private $receiveBuffer;

    /** @var SleeperNotifier */
    private $notifier;

    /** @var bool */
    private $isRunning;

    /**
     * PortalThread constructor.
     *
     * @param string $host Ip to proxy (Mostly 172.18.0.1 for ptero)
     * @param int $port Port to proxy (Mostly 19131)
     * @param string $secret
     * @param string $name
     * @param string $group
     * @param string $address Ip:Port of the current server
     *
     * @param SleeperNotifier $notifier
     */
    public function __construct(string $host, int $port, string $secret, string $name, string $group, string $address, SleeperNotifier $notifier) {
        $this->host = $host;
        $this->port = $port;

        $this->secret = $secret;

        $this->name = $name;
        $this->group = $group;
        $this->address = $address;

        $this->sendQueue = new Threaded();
        $this->receiveBuffer = new Threaded();

        $this->notifier = $notifier;

        $this->isRunning = false;
        $this->start();
    }

    public function run(): void {
        $this->registerClassLoader();

        $socket = $this->connectToSocketServer();

        while ($this->isRunning) {
            while (($send = $this->sendQueue->shift()) !== null) {
                $length = strlen($send);
                $wrote = @socket_write($socket, Binary::writeLInt($length) . $send, 4 + $length);
                if ($wrote !== 4 + $length) {
                    socket_close($socket);
                    $socket = $this->connectToSocketServer();
                }
            }

            do {
                $read = socket_read($socket, 4);
                if(!$read && socket_last_error($socket) == 10054) {
                    socket_close($socket);
                    $socket = $this->connectToSocketServer();
                }
//                elseif(!$read) {
//                    if(is_int($err = socket_last_error($socket)) && $err != 11) {
//                        echo "[OpenAPI] Error whilst reading socket ($err): ".socket_strerror($err)."\n";
//                    }
//                }
                if($read !== false) {
                    if (strlen($read) === 4) {
                        $length = Binary::readLInt($read);
                        $read = @socket_read($socket, $length);
                        if ($read !== false) {
                            $this->receiveBuffer[] = $read;
                            $this->notifier->wakeupSleeper();
                        }
                    } elseif ($read === "") {
                        socket_close($socket);
                        $socket = $this->connectToSocketServer();
                    }
                }
            } while ($read !== false);
            usleep(25000);
        }

        socket_close($socket);
    }

    /**
     * @return resource
     */
    public function connectToSocketServer() {
        while (!($socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP))) {
            echo("[OpenAPI] Could not create socket: " . socket_strerror(socket_last_error()) . " Trying again in 10 seconds.\n");
            sleep(10);
        }

        do {
            $connected = @socket_connect($socket, $this->host, $this->port);
            if (!$connected) {
                echo ("[OpenAPI] Could not connect to remote socket at {$this->host}:{$this->port}: " . socket_strerror(socket_last_error($socket)) . " Trying again in 10 seconds.\n");
                sleep(10);
            }
        } while (!$connected);

        socket_set_nonblock($socket);

        $this->addPacketToQueue(AuthRequestPacket::create(AuthRequestPacket::CLIENT_TYPE_SERVER, $this->secret, $this->name, $this->group, $this->address));

        return $socket;
    }

    /**
     * Starts thread
     *
     * @param int $options
     * @return bool
     */
    public function start($options = PTHREADS_INHERIT_NONE): bool {
        $this->isRunning = true;
        return parent::start($options);
    }

    /**
     * Stops thread
     */
    public function quit(): void {
        $this->isRunning = false;
        parent::quit();
    }

    /**
     * @param DataPacket $packet
     */
    public function addPacketToQueue(DataPacket $packet): void {
        $packet->encode();
        $this->sendQueue[] = $packet->getBuffer();
    }

    /**
     * @return string|null
     */
    public function getBuffer(): ?string {
        return $this->receiveBuffer->shift();
    }
}