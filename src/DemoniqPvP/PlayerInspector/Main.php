<?php

declare(strict_types=1);

namespace DemoniqPvP\PlayerInspector;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
        switch ($cmd->getName()) {
            case "inspect": {
                if(count($ssargs) != 1) {
                    $sender->sendMessage($cmd->getUsage());
                    return false;
                }
                $online = true;
                $player = $this->getServer()->getPlayer($args[0]);
                if ($player == null) {
                    $player = $this->getServer()->getOfflinePlayer($args[0]);
                    $online = false;
                    if ($player == null) {
                        $sender->sendMessage("Failed to get player.");
                        return false;
                    }
                }
                $playerName = $player->getName();
                if ($online) {
                    $ip = $player->getAddress();
                    $ping = $player->getPing();
                    $uuid = $player->getUniqueId();
                } else {
                    $ip = "Not available while player is offline.";
                    $ping = "Not available while player is offline.";
                    $uuid = "Not available while player is offline.";
                }
                $op = $this->displayBool($player->isOp());
                $banned = $this->displayBool($player->isBanned());
                $whitelisted = $this->displayBool($player->isWhitelisted());     
                $onlineFmt = $this->displayBool($online);           
                
                $stuffToLog = "Online: $onlineFmt\nName: $playerName\nIP Address: $ip\nPing: $ping\nUUID: $uuid\n\nOP: $op\nBanned: $banned\nWhitelisted: $whitelisted";
                if ($sender instanceof Player) {
                    $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
                    $form = $api->createSimpleForm(function( Player $recipient, int $result = null) use ($playerName, $player, $sender, $api) {
                        if($result === null) {
                            return true;
                        }
                        switch ($result) {
                            case 0: {
                                $sender->chat("/ban \"$playerName\"");
                                break;
                            }
                            case 1: {
                                $sender->chat("/kick \"$playerName\"");
                                break;
                            }
                            case 2: {
                                $sender->chat("/unban \"$playerName\"");
                                break;
                            }
                            case 3: {
                                $sender->chat("/kill \"$playerName\"");
                                break;
                            }
                            case 4: {
                                $sender->chat("/tp \"$playerName\"");
                                break;
                            }
                            case 5: {
                                $name = $sender->getName();
                                $sender->chat("/tp \"$name\" \"$playerName\"");
                                break;
                            }
                            case 6: {
                                $innerForm = $api->createCustomForm(function( Player $recipient, array $result = null) use ($playerName, $player, $sender, $api) {
                                    if($result == null) {
                                        return true;
                                    }
                                    $player->chat($result[0]);
                                });
                                $innerForm->setTitle("Chatting as $playerName...");
                                $innerForm->addInput("Enter message to be sent...", "Stop it or I will shlt yourself.");
                                $innerForm->addLabel("§aTip: You can use §c/§a to run commands as other players!");
                                $innerForm->sendToPlayer($player);
                                break;
                            }
                        }
                    });
                    $form->setTitle("Inspecting $playerName...");
                    $form->setContent($stuffToLog);
                    $form->addButton("Ban player");
                    $form->addButton("Kick player");
                    $form->addButton("Unban player");
                    $form->addButton("Kill player");
                    $form->addButton("Teleport to player");
                    $form->addButton("Teleport player to you");
                    $form->addButton("Send message as player");
                    $form->sendToPlayer($player);
                } else {
                    $sender->sendMessage($stuffToLog);
                }
                
                return true;
            }
        }
    }

    public function displayBool($bool): string
    {
        if ($bool == false || $bool == 0) {
            return "false";
        } else {
            return "true";
        }
    }
}
