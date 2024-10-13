<?php

namespace KnosTx\XMultiVersion;

use KnosTx\XMultiVersion\player\PlayerFunction;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {

    private string $serverVersion;
    private array $supportedProtocols = [649, 662, 685, 686, 712, 729];
    private Config $playerData;
    private PlayerFunction $playerFunction;

    public function __construct($loader, $server, $description, $dataFolder, $file, $resourceProvider) {
        parent::__construct($loader, $server, $description, $dataFolder, $file, $resourceProvider);
        $this->playerFunction = new PlayerFunction();
    }

    public function onEnable() : void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->serverVersion = $this->getServer()->getPocketMineVersion();

        $this->playerData = new Config($this->getDataFolder() . "playerData.yml", Config::YAML);

        if (!$this->isCompatibleVersion()) {
            $this->getLogger()->warning("Warning: Server version $this->serverVersion may not be fully compatible with XMultiVersion.");
        }
    }

    private function isCompatibleVersion() : bool {
        return version_compare($this->serverVersion, "1.20.0", ">=") && version_compare($this->serverVersion, "1.21.30", "<=");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "xmultiversion") {
            if ($sender instanceof Player) {
                if (!$sender->hasPermission("xmultiversion.use")) {
                    $sender->sendMessage(TextFormat::RED . "You don't have permission to use this command.");
                    return false;
                }
                $sender->sendMessage(TextFormat::GREEN . "XMultiVersion Plugin is active. Server version compatibility: " . $this->getCompatibilityStatus());
            } else {
                $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            }
            return true;
        }
        return false;
    }

    private function getCompatibilityStatus() : string {
        return $this->isCompatibleVersion() ? "Supported" : "Not fully compatible";
    }

    private function onDataPacketReceive(DataPacketReceiveEvent $event, Player $player, PlayerFunction $playerFunction) : void {
        $packet = $event->getPacket();
        if ($packet instanceof LoginPacket) {
            $playerProtocol = $packet->protocol;
            $playerName = $this->playerFunction->getUsername();

            $this->logPlayerInfo($playerName, $playerProtocol);

            if (!$this->isProtocolSupported($playerProtocol)) {
                $event->getOrigin()->disconnect("Your client version is not supported by this server. Please update your game.");
            } else {
                $player->sendMessage("Welcome, $playerName! You are using protocol version $playerProtocol.");
            }
        }
    }

    private function isProtocolSupported(int $protocol) : bool {
        return in_array($protocol, $this->supportedProtocols, true);
    }

    private function logPlayerInfo(string $playerName, int $protocol) : void {
        $this->playerData->set($playerName, [
            "protocol" => $protocol,
            "last_login" => date("Y-m-d H:i:s")
        ]);
        $this->playerData->save();
    }

    public function onDisable() : void {
        $this->playerData->save();
    }
}
