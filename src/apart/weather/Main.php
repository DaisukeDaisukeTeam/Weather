<?php

namespace apart\weather;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener
{

	private Config $config2;

	public function onEnable() : void
	{

		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->config2 = new Config($this->getDataFolder() . "weather.yml", Config::YAML, array(
			"weather" => "clear",));


	}

	public function onjoin(PlayerJoinEvent $event)
	{
		$player = $event->getPlayer();
		$level = $player->getWorld()->getFolderName();
		$data = $this->config2->get("weather");
		if ($data === "clear") {

		} elseif ($data === "rain") {
			$pk = LevelEventPacket::create(LevelEvent::START_RAIN, 110000, $player->getPosition()->asVector3());
			$player->getNetworkSession()->sendDataPacket($pk);
		} elseif ($data === "thunder") {
			$pk = LevelEventPacket::create(LevelEvent::START_RAIN, 40000, $player->getPosition()->asVector3());
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}


	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
	{

		$data = $this->config2->get("weather");
		if (!$sender instanceof Player) {
			$sender->sendMessage("§cゲーム内で実行してください");
			return true;
		}

		switch ($label) {

			case 'weather':
				if ($this->getServer()->isOp($sender->getName())) {
					if (!isset($args[0])) {
						$sender->sendMessage("§a[weatherSystem] usage: /weather clear|rain|thunder");
					} elseif ($args[0] === "rain"||$args[0] === "r") {
						$this->config2->set("weather", "rain");
						$pk = LevelEventPacket::create(LevelEvent::START_RAIN, 110000, $sender->getPosition()->asVector3());
						$sender->getNetworkSession()->sendDataPacket($pk);
					} elseif ($args[0] === "thunder"||$args[0] === "t") {
						$this->config2->set("weather", "thunder");
						$pk = LevelEventPacket::create(LevelEvent::START_RAIN, 40000, $sender->getPosition()->asVector3());
						$sender->getNetworkSession()->sendDataPacket($pk);
					} elseif ($args[0] === "clear"||$args[0] === "c") {
						if ($data === "clear") {
							# code...
						}
						if ($data === "rain") {
							$pk = LevelEventPacket::create(LevelEvent::STOP_RAIN, 110000, $sender->getPosition()->asVector3());
							$sender->getNetworkSession()->sendDataPacket($pk);

							$this->config2->set("weather", "clear");
							return true;
						}
						if ($data === "thunder") {
							$pk = LevelEventPacket::create(LevelEvent::STOP_RAIN, 40000, $sender->getPosition()->asVector3());
							$sender->getNetworkSession()->sendDataPacket($pk);
							$this->config2->set("weather", "clear");
							return true;
						}
					}else{
						$sender->sendMessage("§a[weatherSystem] usage: /weather clear|rain|thunder");
						return true;
					}
				}else{
					$sender->sendMessage("§4このコマンドを実行する権限がありません。");
				}
				break;

		}
		return true;

	}

	public function onDisable() : void
    {
      $this->config2->save();
    }
}
