<?php

declare(strict_types=1);

namespace happybe\openapi\friends;

use happybe\openapi\form\ModalForm;
use happybe\openapi\mysql\query\AddFriendQuery;
use happybe\openapi\mysql\QueryQueue;
use pocketmine\Player;
use pocketmine\Server;

/**
 * Class FriendsManager
 * @package happybe\openapi\friends
 */
class FriendsManager {

    /**
     * @param Player $player
     * @param Player $friend
     * @param callable<Player, AddFriendQuery>|null $handleUpdate
     */
    public static function setFriends(Player $player, Player $friend, ?callable $handleUpdate = null) {
        QueryQueue::submitQuery(new AddFriendQuery($player->getName(), $friend->getName()), function (AddFriendQuery $query) use ($player, $handleUpdate) {
            if(is_callable($handleUpdate)) {
                $handleUpdate($player, $query);
            }
        });
    }

    /**
     * @param Player $player
     * @param Player $newFriend
     */
    public static function sendFriendRequest(Player $player, Player $newFriend) {
        $form = new ModalForm("Friend Request", "{$player->getName()} sent you a friend request.");
        $form->setFirstButton("§aAccept");
        $form->setSecondButton("§cDecline");

        $form->setCustomData($player->getName());
        $form->setAdvancedCallable(function (Player $friend, $data, ModalForm $form) {
            if($data !== true) {
                $friend->sendMessage("§l§o§eFRIENDS§r§f: §bFriend request cancelled!");
                return;
            }

            $player = Server::getInstance()->getPlayerExact($form->getCustomData());
            if($player === null) {
                $friend->sendMessage("§l§o§eFRIENDS§r§f: §bFriend request expired (player left the game).");
                return;
            }

            $player->sendMessage("§l§o§eFRIENDS§r§f: §b{$friend->getName()} accepted your friend request!");
            $friend->sendMessage("§l§o§eFRIENDS§r§f: §bFriend request accepted!");

            FriendsManager::setFriends($player, $friend);
        });

        $newFriend->sendForm($form);
    }
}