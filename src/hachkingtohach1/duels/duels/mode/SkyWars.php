<?php

namespace hachkingtohach1\duels\duels\mode;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\entity\{Effect, EffectInstance};
use pocketmine\item\enchantment\{
    Enchantment,
    EnchantmentInstance
};

use hachkingtohach1\duels\Main;

class SkyWars implements Listener {	
	
	/*@param Main $plugin*/
	public $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}
	
	public static function getNameDuel(){
        return ["sw", "skywars"];
	}
	
	public function getKit(Player $player){
		$player->setGamemode(0);	    
	    $player->setHealth(20);
        $player->setFood(20);
		$player->setScale(1.0);	
		
		$player->getArmorInventory()->clearAll();
		$player->getInventory()->clearAll();
	    
        $player->getInventory()->addItem(Item::get(274,0,1));						
        $player->getInventory()->addItem(Item::get(275,0,1));		
        $player->getInventory()->addItem(Item::get(273,0,1));		
	}
}