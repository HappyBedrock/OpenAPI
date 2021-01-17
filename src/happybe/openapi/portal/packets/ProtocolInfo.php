<?php

declare(strict_types=1);

namespace happybe\openapi\portal\packets;

/**
 * Interface ProtocolInfo
 * @package happybe\openapi\portal\packets
 */
interface ProtocolInfo {

    public const AUTH_REQUEST_PACKET = 0xd0;
    public const AUTH_RESPONSE_PACKET = 0xd1;
    public const TRANSFER_REQUEST_PACKET = 0xd2;
    public const TRANSFER_RESPONSE_PACKET = 0xd3;
}