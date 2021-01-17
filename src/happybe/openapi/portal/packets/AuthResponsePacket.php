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
    /** @var string $reason */
    public $reason;

    /**
     * @param int $status
     * @param string $reason
     *
     * @return static
     */
    public static function create(int $status, string $reason): self {
        $result = new self;
        $result->status = $status;
        $result->reason = $reason;

        return $result;
    }

    protected function decodePayload(): void {
        $this->status = $this->getByte();
        $this->reason = $this->getString();
    }

    protected function encodePayload(): void {
        $this->putByte($this->status);
        $this->putString($this->reason);
    }

    public function handlePacket(): void {
        OpenAPI::getInstance()->getLogger()->info($this->reason);
    }
}