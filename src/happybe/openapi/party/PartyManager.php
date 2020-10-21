<?php

declare(strict_types=1);

namespace happybe\openapi\party;

use happybe\openapi\form\CustomForm;
use happybe\openapi\form\ModalForm;
use happybe\openapi\mysql\query\CreatePartyQuery;
use happybe\openapi\mysql\query\DestroyPartyQuery;
use happybe\openapi\mysql\query\FetchFriendsQuery;
use happybe\openapi\mysql\query\LazyRegisterQuery;
use happybe\openapi\mysql\QueryQueue;
use happybe\openapi\OpenAPI;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

/**
 * Class PartyManager
 * @package happybe\openapi\party
 */
class PartyManager {

    /** @var Party[] $parties */
    private static $parties = [];
    /** @var array $unloggedPartySessions */
    private static $unloggedPartySessions = [];

    /** @var callable[] $offlineSessionHandlers */
    private static $offlineSessionHandlers = [];

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

    /**
     * @param Party $party
     */
    public static function removeParty(Party $party) {
        unset(self::$parties[$party->getOwner()->getName()]);
    }

    /**
     * @param Player $player
     * @param LazyRegisterQuery $query
     */
    public static function handleLoginQuery(Player $player, LazyRegisterQuery $query) {
        if(empty($query->partiesRow)) {
            return;
        }

        $owner = $query->partiesRow["Owner"];
        if($owner === $player) {
            $party = new Party($player);

            /** @var Player[] $otherMembers */
            $otherMembers = self::$unloggedPartySessions[$owner] ?? [];
            foreach ($otherMembers as $member) {
                $party->addMember($player, false);
                if(!$member->isOnline()) {
                    $party->removeMember($player);
                }
            }

            return;
        }

        if(isset(self::$parties[$owner])) {
            self::$parties[$owner]->addMember($player, false);
            return;
        }

        if(!isset(self::$unloggedPartySessions[$owner])) {
            OpenAPI::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $i) use ($owner) {
                if(!isset(self::$unloggedPartySessions[$owner])) {
                    return;
                }

                /** @var Player $player */
                foreach (self::$unloggedPartySessions[$owner] as $player) {
                    $player->sendMessage("§9Party> §cParty was destroyed (owner of the party has left the server while transferring)");
                }

                $handler = self::$offlineSessionHandlers[$owner] ?? null;
                if(is_callable($handler)) {
                    $handler(array_values(self::$unloggedPartySessions[$owner]));
                }

                QueryQueue::submitQuery(new DestroyPartyQuery($owner));

                unset(self::$unloggedPartySessions[$owner]);
            }), 40);
        }

        self::$unloggedPartySessions[$owner][] = $player;
    }

    /**
     * @param Player $player
     */
    public static function handleQuit(Player $player) {
        $party = self::getPartyByPlayer($player);
        if($party === null) {
            return;
        }

        if(!$party->isOnline()) {
            return;
        }

        if($party->getOwner()->getName() == $player->getName()) {
            $party->broadcastMessage("§9Party> §c{$player->getName()} left the server.");
            self::destroyParty($party);
            return;
        }

        $party->removeMember($player);
    }

    /**
     * @param string $owner
     * @param callable $handler
     */
    public static function setHandlerForDestroyingOfflineSession(string $owner, callable $handler) {
        self::$offlineSessionHandlers[$owner] = $handler;
    }
}