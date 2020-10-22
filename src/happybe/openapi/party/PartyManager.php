<?php

declare(strict_types=1);

namespace happybe\openapi\party;

use happybe\openapi\form\CustomForm;
use happybe\openapi\form\ModalForm;
use happybe\openapi\mysql\query\CreatePartyQuery;
use happybe\openapi\mysql\query\DestroyPartyQuery;
use happybe\openapi\mysql\query\FetchFriendsQuery;
use happybe\openapi\mysql\query\LazyRegisterQuery;
use happybe\openapi\mysql\query\RemovePartyMemberQuery;
use happybe\openapi\mysql\QueryQueue;
use happybe\openapi\OpenAPI;
use happybe\openapi\servers\ServerManager;
use pocketmine\item\Bread;
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

                QueryQueue::submitQuery(new CreatePartyQuery($player->getName(), ServerManager::getCurrentServer()->getServerName()));
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
        $members = $query->partiesRow["Members"] == "" ? [] : explode(",", $query->partiesRow["Members"]);

        if(isset(self::$unloggedPartySessions[$owner])) {
            self::$unloggedPartySessions[$owner][] = $player;
            return;
        }

        self::$unloggedPartySessions[$owner][] = $player;

        OpenAPI::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $i) use ($members, $owner): void {
            /** @var Player|null $ownerPlayer */
            $ownerPlayer = null;

            /** @var Player $member */
            foreach (self::$unloggedPartySessions[$owner] as $i => $member) {
                if($member->getName() == $owner) {
                    $ownerPlayer = $member;
                    unset(self::$unloggedPartySessions[$i]);
                    break;
                }
            }

            $callback = self::$offlineSessionHandlers[$owner] ?? null;

            if((!$ownerPlayer->isOnline()) || $ownerPlayer === null) {
                QueryQueue::submitQuery(new DestroyPartyQuery($owner));
                foreach (self::$unloggedPartySessions[$owner] as $member) {
                    $member->sendMessage("§9Party> §cParty destroyed (It's owner left the game)");
                }

                if(is_callable($callback)) {
                    $callback(false, array_filter(array_values(self::$unloggedPartySessions[$owner]), function (Player $player) {return $player->isOnline();}), null);
                }

                unset(self::$unloggedPartySessions[$owner]);
                unset(self::$offlineSessionHandlers[$owner]);
                return;
            }

            $party = new Party($ownerPlayer);
            $members = array_flip($members);
            /** @var Player $player */
            foreach (self::$unloggedPartySessions[$owner] as $player) {
                if($player->isOnline()) {
                    $members[$player->getName()] = $player;
                }
            }

            $whoseLeftTheGame = array_filter($members, function ($val) {
                return !($val instanceof Player);
            });
            $inGame = array_filter($members, function ($val) {
                return $val instanceof Player;
            });

            foreach ($whoseLeftTheGame as $toRemove => $v) {
                QueryQueue::submitQuery(new RemovePartyMemberQuery($owner, $toRemove));
            }
            foreach ($inGame as $player) {
                $party->addMember($player, false);
            }

            if(count($whoseLeftTheGame) > 0) {
                $party->broadcastMessage("§9Party> §c" . count($whoseLeftTheGame) . " party members left the game.");
            }


            if(is_callable($callback)) {
                $callback(true, array_filter(array_values(self::$unloggedPartySessions[$owner]), function (Player $player) {return $player->isOnline();}), $party);
            }

            unset(self::$unloggedPartySessions[$owner]);
            unset(self::$offlineSessionHandlers[$owner]);
        }), 20);
    }

    /**
     * @param Player $player
     * @return string|null
     */
    public static function isInOfflineQueue(Player $player): ?string {
        foreach (self::$unloggedPartySessions as $owner => $session) {
            foreach ($session as $pl) {
                if($pl->getName() == $player->getName()) {
                    return $owner;
                }
            }
        }

        return null;
    }

    /**
     * @param string $owner
     * @param callable(bool,Player[],Party|null) $handler
     */
    public static function addHandlerToOfflineSession(string $owner, callable $handler) {
        if(is_callable($handler)) {
            self::$offlineSessionHandlers[$owner] = $handler;
        }
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
}