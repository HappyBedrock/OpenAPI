<?php

declare(strict_types=1);

namespace happybe\openapi\ranks;

class Rank {

    /** @var string */
    public $name;

    /** @var string */
    public $displayFormat;
    /** @var array */
    public $permissions;
    /** @var bool */
    public $isVisible;

    public function __construct(string $name, string $displayFormat, array $permissions = [], bool $isVisible = true) {
        $this->name = $name;
        $this->displayFormat = $displayFormat;
        $this->permissions = $permissions;
        $this->isVisible = $isVisible;
    }

    /**
     * Returns raw rank format (For example: 'Guest' or 'Owner')
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Returns permissions whose player has with this rank
     */
    public function getPermissions(): array {
        return $this->permissions;
    }

    /**
     * Returns displayed format (For example '' for Guest or '§l§6OWNER' for owner)
     */
    public function getDisplayFormat(): string {
        return $this->displayFormat;
    }

    /**
     * @deprecated
     * @link getDisplayFormat()
     */
    public function getFormatForChat(): string {
        return $this->getDisplayFormat();
    }

    /**
     * @deprecated
     * @link getDisplayFormat()
     */
    public function getFormatForNameTag(): string {
        return $this->getDisplayFormat();
    }
}