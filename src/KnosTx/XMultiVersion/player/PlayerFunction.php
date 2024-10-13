<?php

namespace KnosTx\XMultiVersion\player;

use pocketmine\player\Player;

class PlayerFunction extends Player {
    public function getUsername(): string {
        return $this->username;
    }
}
