<?php

declare(strict_types=1);

namespace happybe\openapi\friends;

use happybe\openapi\form\ModalForm;
use happybe\openapi\mysql\query\AddFriendQuery;
use happybe\openapi\mysql\QueryQueue;
use pocketmine\Player;

/**
 * Class FriendsManager
 * @package happybe\openapi\friends
 */
class FriendsManager {

    /**
     * @param Player $player
     * @param Player $friend
     * @param callable<Player, AddFriendQuery> $handleUpdate
     */
    public function setFriends(Player $player, Player $friend, callable $handleUpdate) {
        QueryQueue::submitQuery(new AddFriendQuery($player->getName(), $friend->getName()), function (AddFriendQuery $query) use ($player, $handleUpdate) {
            $handleUpdate($player, $query);
        });
    }

    /**
     * @param Player $player
     * @param Player $newFriend
     */
    public function sendFriendRequest(Player $player, Player $newFriend) {
        $form = new ModalForm("Friend Request", "{$player->getName()} sent you a friend request.");
        $form->setFirstButton("§aAccept");
        $form->setSecondButton("§cDecline");

        $form->setCustomData($player->getName());
        $form->setAdvancedCallable(function (Player $player, $result, ModalForm $form) {
            var_dump($form->data);
        });

        $newFriend->sendForm($form);
    }
}