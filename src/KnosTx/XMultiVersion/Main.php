<?php

namespace KnosTx\XMultiVersion;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;

class Main extends PluginBase implements Listener {

    private string $serverVersion;

    public function onEnable(): void {
        // Register event listeners
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        // Determine server version
        $this->serverVersion = $this->getServer()->getPocketMineVersion();

        // Ensure compatibility
        if (!$this->isCompatibleVersion()) {
            $this->getLogger()->warning("Warning: Server version $this->serverVersion may not be fully compatible.");
        }
    }

    /**
     * Check if the server version is within the supported range.
     */
    private function isCompatibleVersion(): bool {
        return version_compare($this->serverVersion, "1.20.0", ">=") && version_compare($this->serverVersion, "1.21.30", "<=");
    }

    /**
     * Handle /xmultiversion command
     */
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

    /**
     * Provide a message regarding the server's compatibility status.
     */
    private function getCompatibilityStatus(): string {
        if ($this->isCompatibleVersion()) {
            return "Supported";
        } else {
            return "Not fully compatible";
        }
    }

    /**
     * Prevent players from connecting if the version is not compatible.
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $event): void {
        $packet = $event->getPacket();
        if ($packet instanceof LoginPacket) {
            $playerVersion = $packet->protocol;
            
            // Allow only compatible versions to join
            if (!$this->isPlayerVersionCompatible($playerVersion)) {
                $event->getOrigin()->disconnect("Your client version is not supported by this server.");
            }
        }
    }

    /**
     * Determine player compatibility based on protocol version.
     */
    private function isPlayerVersionCompatible(int $protocolVersion): bool {
        // Define protocol version range for 1.20.x - 1.21.30
        $minProtocol = 685; // Protocol for 1.20.0
        $maxProtocol = 729; // Protocol for 1.21.30
        return $protocolVersion >= $minProtocol && $protocolVersion <= $maxProtocol;
    }
}
