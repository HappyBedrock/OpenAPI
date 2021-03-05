# OpenAPI
 API for people who want help with HappyBE development
 
## Documentation:
 
### Economy:
 - Adding coins for player
 ```php
/**
 * @var \pocketmine\Player $player
 * @var int $coins
 */
\happybe\openapi\economy\Economy::addCoins($player, $coins);
```

- Obtaining coins
```php
/** @var \pocketmine\Player $player */
\happybe\openapi\economy\Economy::getCoins($player, function(int $amount) use ($player) {
    $player->sendMessage("§l§o§eACCOUNT§r§f: §bCurrently you have $amount coins!");
});
```

### Multi-Server API:
- Checking if server is online and transferring player here if so
```php
$server = \happybe\openapi\servers\ServerManager::getServer("Lobby-2");
if($server->isOnline()) {
    /** @var \pocketmine\Player $player */
    $server->transferPlayerHere($player);
}
```

### Ranks
- Obtaining player's rank
```php
/** @var \pocketmine\Player $player */
$rank = \happybe\openapi\ranks\RankDatabase::getPlayerRank($player);
$player->sendMessage("§l§o§eACCOUNT§r§f: §bYour rank: " . $rank->getName());
```