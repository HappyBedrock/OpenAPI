<?php

declare(strict_types=1);

namespace happybe\openapi\portal\packets;

/**
 * Class AuthRequestPacket
 * @package happybe\openapi\portal\packets
 */
class AuthRequestPacket extends PortalPacket {

    public const NETWORK_ID = ProtocolInfo::AUTH_REQUEST_PACKET;

    public const CLIENT_TYPE_SERVER = 0;

    /** @var int $type */
    public $type;
    /** @var string $secret */
    public $secret;
    /** @var string $name */
    public $name;
    /** @var string $group */
    public $group;
    /** @var string $address */
    public $address;

    /**
     * @param int $type
     * @param string $secret
     * @param string $name
     * @param string $group
     * @param string $address
     *
     * @return static
     */
    public static function create(int $type, string $secret, string $name, string $group, string $address): self {
        $result = new self;
        $result->type = $type;
        $result->secret = $secret;
        $result->name = $name;
        $result->group = $group;
        $result->address = $address;

        return $result;
    }

    protected function decodePayload(): void {
        $this->type = $this->getByte();
        $this->secret = $this->getString();
        $this->name = $this->getString();
        $this->group = $this->getString();
        $this->address = $this->getString();
    }

    protected function encodePayload(): void {
        $this->putByte($this->type);
        $this->putString($this->secret);
        $this->putString($this->name);
        $this->putString($this->group);
        $this->putString($this->address);
    }

    /**
     * Shouldn't be handled by the server
     */
    public function handlePacket(): void {}
}