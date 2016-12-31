<?php

namespace awzaw\roleplay;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener {

    public $asp;

    public function onEnable() {
        $this->enabled = array();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->asp = $this->getServer()->getPluginManager()->getPlugin("AntiSpamPro");
        if (!$this->asp) {
            $this->getLogger()->info("Unable to find AntiSpamPro");
        }
    }

    public function onCommand(CommandSender $issuer, Command $cmd, $label, array $args) {

        if (strtolower($cmd->getName()) !== "rp")
            return;

        if (!(isset($args[0])) && ($issuer instanceof Player)) {
            if (isset($this->enabled[strtolower($issuer->getName())])) {
                unset($this->enabled[strtolower($issuer->getName())]);
            } else {
                $this->enabled[strtolower($issuer->getName())] = strtolower($issuer->getName());
            }

            if (isset($this->enabled[strtolower($issuer->getName())])) {
                $issuer->sendMessage(TEXTFORMAT::GREEN . "You joined roleplay! Leave with /rp");
            } else {
                $issuer->sendMessage(TEXTFORMAT::GREEN . "You left roleplay! Join with /rp");
            }
            return true;
        } else {
            if ((isset($args[0])) && strtolower($args[0]) === "list") {

                if (count($this->enabled) === 0) {
                    $issuer->sendMessage(TEXTFORMAT::RED . "RolePlay is Empty");
                    return true;
                }
                $issuer->sendMessage(TEXTFORMAT::YELLOW . "List Of Players in RolePlay");
                foreach ($this->enabled as $rpplayer) {
                    $issuer->sendMessage(TEXTFORMAT::GREEN . $rpplayer);
                }
            }
            return true;
        }
    }

    /**
     * @param PlayerCommandPreprocessEvent $event
     *
     * @priority MONITOR
     */
    public function onPlayerChat(PlayerChatEvent $event) {

        if (!isset($this->enabled[strtolower($event->getPlayer()->getName())])) {
            return true;
        }

        $message = $event->getMessage();
        if (!isset($message) || $message == "")
            return;

        if ($this->asp && $this->asp->getProfanityFilter()->hasProfanity($message)) {
            $event->setCancelled(true);
            $event->getPlayer()->sendMessage(TEXTFORMAT::RED . "No Swearing");
            return true;
        }

        $sender = $event->getPlayer();
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            if (isset($this->enabled[strtolower($player->getName())])) {
                $player->sendMessage("* " . $sender->getName() . " " . $message);
            }
            $event->setCancelled(true);
        }
    }

    public function onQuit(PlayerQuitEvent $e) {
        if (isset($this->enabled[strtolower($e->getPlayer()->getName())])) {
            unset($this->enabled[strtolower($e->getPlayer()->getName())]);
        }
    }

}
