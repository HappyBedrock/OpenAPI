<?php

declare(strict_types=1);

namespace bedrockplay\openapi\ranks;

/**
 * Class Rank
 * @package bedrockplay\openapi\ranks
 */
class Rank {

    /** @var string $name */
    public $name;
    /** @var string $chatFormat */
    public $chatFormatting;
    /** @var array $permissions */
    public $permissions;
    /** @var bool $isVisible */
    public $isVisible;

    /**
     * Rank constructor.
     *
     * @param string $name
     * @param string $chatFormatting
     * @param array $permissions
     * @param bool $isVisible
     */
    public function __construct(string $name, string $chatFormatting, array $permissions = [], bool $isVisible = false) {
        $this->name = $name;
        $this->chatFormatting = $chatFormatting;
        $this->permissions = $permissions;
        $this->isVisible = $isVisible;
    }

    /**
     * Returns raw rank format
     *
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Returns colors used for the rank
     *
     * @return string
     */
    public function getChatFormatting(): string {
        return $this->chatFormatting;
    }

    /**
     * Returns permissions whose player has with this rank
     *
     * @return array
     */
    public function getPermissions(): array {
        return $this->permissions;
    }

    /**
     * Returns trimmed and formatted rank
     *
     * @return string
     */
    public function __toString() {
        return $this->getChatFormatting() . $this->getName();
    }
}