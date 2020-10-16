<?php

declare(strict_types=1);

namespace happybe\openapi\form;

/**
 * Class CustomForm
 * @package happybe\openapi\form
 */
class CustomForm extends Form {

    /**
     * CustomForm constructor.
     * @param string $title
     */
    public function __construct(string $title = "TITLE") {
        $this->data["type"] = "custom_form";
        $this->data["title"] = $title;
        $this->data["content"] = [];
    }

    /**
     * @param string $text
     */
    public function addInput(string $text) {
        $this->data["content"][] = ["type" => "input", "text" => $text];
    }

    /**
     * @param string $text
     */
    public function addLabel(string $text) {
        $this->data["content"][] = ["type" => "label", "text" => $text];
    }

    /**
     * @param string $text
     * @param bool|null $default
     */
    public function addToggle(string $text, ?bool $default = null) {
        if($default !== null) {
            $this->data["content"][] = ["type" => "toggle", "text" => $text, "default" => $default];
            return;
        }
        $this->data["content"][] = ["type" => "toggle", "text" => $text];
    }

    /**
     * @param string $text
     * @param array $options
     * @param int|null $default
     */
    public function addDropdown(string $text, array $options, ?int $default = null) {
        if($default !== null) {
            $this->data["content"][] = ["type" => "dropdown", "text" => $text, "options" => $options, "default" => $default];
            return;
        }
        $this->data["content"][] = ["type" => "dropdown", "text" => $text, "options" => $options];
    }
}