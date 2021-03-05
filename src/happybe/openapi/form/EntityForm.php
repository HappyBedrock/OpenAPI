<?php

declare(strict_types=1);

namespace happybe\openapi\form;

use InvalidStateException;
use pocketmine\entity\Entity;

/**
 * Warning: This form cannot have been used with Player->sendForm()
 * Sending this form is client-sided. Player have to click the entity
 * you link this form with using
 *
 * @link EntityForm::linkToEntity()
 */
class EntityForm extends SimpleForm {

    /** @var EntityForm[] */
    private static $handlers = [];

    public function linkToEntity(Entity $entity) {
        if($entity->namedtag !== null && $entity->namedtag->hasTag("IsLinkedWithForm")) {
            throw new InvalidStateException("Entity is already linked with another form.");
        }

        $entity->setNameTag($this->data["title"]);

        $entity->getDataPropertyManager()->setByte(Entity::DATA_HAS_NPC_COMPONENT, 1);
        $entity->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, $this->data["content"]);
        $entity->getDataPropertyManager()->setString(Entity::DATA_NPC_ACTIONS, json_encode(array_map(function ($buttonText): array {
            return [
                "button_name" => $buttonText["text"],
                "data" => null,
                "mode" => 0,
                "text" => "",
                "type" => 1
            ];
        }, $this->data["buttons"])));

        self::$handlers[$entity->getId()] = $this;
    }

    public static function getFormByEntity(Entity $entity): ?EntityForm {
        return self::$handlers[$entity->getId()] ?? null;
    }

    public function jsonSerialize() {
        throw new InvalidStateException("EntityForm cannot be sent with Player->sendForm()");
    }
}