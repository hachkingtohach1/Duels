<?php

namespace hachkingtohach1\duels\duels\mode;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\enchantment\{
    Enchantment,
    EnchantmentInstance
};

use hachkingtohach1\duels\Main;

class Classic implements Listener {	
	
	/*@param Main $plugin*/
	public $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}
	
	public static function getNameDuel(){
        return ["clsc", "classic", "slc"];
	}
	
	public function getKit(Player $player){
		$player->setGamemode(2);	    
	    $player->setHealth(20);
        $player->setFood(20);
        $player->setScale(1.0);				
		
		$player->getArmorInventory()->clearAll();
		$player->getInventory()->clearAll();
		
	    $helmet = Item::get(Item::IRON_HELMET, 0, 1);
        $player->getArmorInventory()->setHelmet($helmet);
		
        $chestplate = Item::get(Item::IRON_CHESTPLATE, 0, 1);
        $player->getArmorInventory()->setChestplate($chestplate);
		
        $leggings = Item::get(Item::IRON_LEGGINGS, 0, 1);
        $player->getArmorInventory()->setLeggings($leggings);
		
        $boots = Item::get(Item::IRON_BOOTS, 0, 1);
        $player->getArmorInventory()->setBoots($boots);
		
        $sword = Item::get(Item::IRON_SWORD, 0, 1);
        $player->getInventory()->addItem($sword);

		$player->getInventory()->addItem(Item::get(261,0,1));
        $player->getInventory()->addItem(Item::get(259,0,1));
        $player->getInventory()->addItem(Item::get(346,0,1));
        $player->getInventory()->addItem(Item::get(262,0,5));			
	}
}