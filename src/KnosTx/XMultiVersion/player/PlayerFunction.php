<?php

namespace KnosTx\XMultiVersion;

use pocketmine\player\Player;

class PlayerFunction extends Player {
    public function getUsername(): string {
        return $this->username;
    }
}
