<?php

declare(strict_types=1);

namespace happybe\openapi\portal\packets;

use happybe\openapi\OpenAPI;

/**
 * Class AuthResponsePacket
 * @package happybe\openapi\portal\packets
 */
class AuthResponsePacket extends PortalPacket {

    public const NETWORK_ID = ProtocolInfo::AUTH_RESPONSE_PACKET;

    public const RESPONSE_SUCCESS = 0;
    public const RESPONSE_INCORRECT_SECRET = 1;
    public const RESPONSE_UNKNOWN_TYPE = 2;
    public const RESPONSE_INVALID_DATA = 3;

    /** @var int $status */
    public $status;

    /**
     * @param int $status
     *
     * @return static
     */
    public static function create(int $status): self {
        $result = new self;
        $result->status = $status;

        return $result;
    }

    protected function decodePayload(): void {
        $this->status = $this->getByte();
    }

    protected function encodePayload(): void {
        $this->putByte($this->status);
    }

    public function handlePacket(): void {
        if($this->status === self::RESPONSE_SUCCESS) {
            OpenAPI::getInstance()->getLogger()->info("Authentication with Portal was successful!");
            return;
        }
        OpenAPI::getInstance()->getLogger()->info("An error occurred while authenticating ({$this->status})!");
    }
}