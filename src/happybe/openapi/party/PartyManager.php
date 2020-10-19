<?php

declare(strict_types=1);

namespace happybe\openapi\party;

use happybe\openapi\form\CustomForm;
use happybe\openapi\form\ModalForm;
use happybe\openapi\mysql\query\CreatePartyQuery;
use happybe\openapi\mysql\query\DestroyPartyQuery;
use happybe\openapi\mysql\query\FetchFriendsQuery;
use happybe\openapi\mysql\QueryQueue;
use pocketmine\Player;
use pocketmine\Server;

/**
 * Class PartyManager
 * @package happybe\openapi\party
 */
class PartyManager {

    /** @var Party[] $parties */
    private static $parties = [];

    /**
     * @param Player $player
     */
    public static function createParty(Player $player) {
        QueryQueue::submitQuery(new FetchFriendsQuery($player->getName()), function (FetchFriendsQuery $query) use ($player) {
            $friends = array_values(array_filter($query->friends, function ($friend) {
                $player = Server::getInstance()->getPlayerExact($friend);
                if($player === null) {
                    return false; // Player is offline
                }

                return PartyManager::getPartyByPlayer($player) === null; // Player is already in a party
            }));

            if(count($friends) === 0) {
                $player->sendMessage("§9Parties> §cCannot create party - there aren't any online friends on the current server without a party.");
                return;
            }

            $form = new CustomForm("Invite friends to your party");
            foreach ($friends as $friend) {
                $form->addToggle($friend, false);
            }

            $form->setCustomData($friends);

            $form->setAdvancedCallable(function (Player $player, $data, CustomForm $form) {
                if(!is_array($data)) {
                    return;
                }

                QueryQueue::submitQuery(new CreatePartyQuery($player->getName()));
                PartyManager::$parties[$player->getName()] = new Party($player);

                /** @var string[] $friends */
                $friends = $form->getCustomData();
                $j = 0;
                for($i = 0; $i < count($friends); $i++) {
                    if($data[$i] === true) {
                        $friend = Server::getInstance()->getPlayerExact($friends[$i]);
                        if($friend === null) {
                            $player->sendMessage("§9Parties> §6Your friend {$friends[$i]} is no longer online.");
                            continue;
                        }

                        PartyManager::sendPartyInvitation($player, $friend);
                        $j++;
                    }
                }

                $player->sendMessage("§9Party> §aParty created ($j invites sent)!");

            });

            $player->sendForm($form);
        });
    }

    /**
     * @param Player $owner
     * @param Player $friend
     */
    public static function sendPartyInvitation(Player $owner, Player $friend) {
        $form = new ModalForm("Party Invitation", "{$owner->getName()} invited you to his party!");
        $form->setFirstButton("§aAccept invitation");
        $form->setSecondButton("§cDecline invitation");

        $form->setCallable(function (Player $friend, $data) use ($owner) {
            if($owner === null || !$owner->isOnline()) {
                $friend->sendMessage("§9Parties> §cInvitation expired.");
                return;
            }

            $party = self::$parties[$owner->getName()] ?? null;
            if($party === null) {
                $friend->sendMessage("§9Parties> §cParty doesn't exist anymore.");
                return;
            }

            if($data === true) {
                $party->addMember($friend);
                $party->broadcastMessage("§9Party> §a{$friend->getName()} joined the party!");
            }
        });

        $friend->sendForm($form);
    }

    /**
     * @param Player $player
     * @return Party|null
     */
    public static function getPartyByPlayer(Player $player): ?Party {
        if(isset(self::$parties[$player->getName()])) {
            return self::$parties[$player->getName()];
        }

        foreach (self::$parties as $party) {
            if($party->containsPlayer($player)) {
                return $party;
            }
        }

        return null;
    }

    /**
     * @param Party $party
     */
    public static function destroyParty(Party $party) {
        QueryQueue::submitQuery(new DestroyPartyQuery($party->getOwner()->getName()));
        foreach ($party->getMembers() as $member) {
            $member->sendMessage("§9Party> §6{$party->getOwner()->getName()} has destroyed his party.");
        }

        unset(self::$parties[$party->getOwner()->getName()]);
    }
}