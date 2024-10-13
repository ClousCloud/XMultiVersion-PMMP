<?php

namespace KnosTx\XMultiVersion\player;

use pocketmine\player\Player;

class PlayerFunction extends Player {
    public function __construct($loader, $server, $description, $dataFolder, $file, $resourceProvider) {
        parent::__construct($loader, $server, $description, $dataFolder, $file, $resourceProvider);
    }

    public function getUsername(): string {
        return $this->username;
    }
}
