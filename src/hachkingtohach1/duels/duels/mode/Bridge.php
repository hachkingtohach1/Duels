<?php

namespace hachkingtohach1\duels\duels\mode;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\utils\Color;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\entity\{Effect, EffectInstance};
use pocketmine\item\enchantment\{
    Enchantment,
    EnchantmentInstance
};

use hachkingtohach1\duels\Main;

class Bridge implements Listener {	
	
	/*@param Main $plugin*/
	public $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}
	
	public static function getNameDuel(){
        return ["bridge", "bgr"];
	}
	
	public function getKit(Player $player, int $red, int $green, int $blue){
		$player->setGamemode(0);	    
	    $player->setHealth(20);
        $player->setFood(20);
		$player->setScale(1.0);	
		
		$player->getArmorInventory()->clearAll();
		$player->getInventory()->clearAll();
		
		$helmet = Item::get(Item::LEATHER_HELMET, 0, 1);
		$helmet->setCustomColor(new Color($red, $green, $blue));
        $player->getArmorInventory()->setHelmet($helmet);
		
        $chestplate = Item::get(Item::LEATHER_CHESTPLATE, 0, 1);
		$chestplate->setCustomColor(new Color($red, $green, $blue));
        $player->getArmorInventory()->setChestplate($chestplate);
		
        $leggings = Item::get(Item::LEATHER_LEGGINGS, 0, 1);
		$leggings->setCustomColor(new Color($red, $green, $blue));
        $player->getArmorInventory()->setLeggings($leggings);
		
        $boots = Item::get(Item::LEATHER_BOOTS, 0, 1);
		$boots->setCustomColor(new Color($red, $green, $blue));
        $player->getArmorInventory()->setBoots($boots);
		
        $sword = Item::get(Item::IRON_SWORD, 0, 1);
        $player->getInventory()->addItem($sword);
		
		$player->getInventory()->addItem(Item::get(261,0,1));
		
        $pickaxe = Item::get(Item::DIAMOND_PICKAXE, 0, 1);
		$enchantment = Enchantment::getEnchantment(Enchantment::UNBREAKING);
		$pickaxe->addEnchantment(new EnchantmentInstance($enchantment, 1));
        $player->getInventory()->addItem($pickaxe);		
		
		if($red === 255){
            $player->getInventory()->addItem(Item::get(236,14,128));						
        }
		if($blue === 255){
            $player->getInventory()->addItem(Item::get(236,11,128));					
        }   
		$player->getInventory()->addItem(Item::get(322,0,5));
        $player->getInventory()->addItem(Item::get(0,0,1));	
        $player->getInventory()->addItem(Item::get(264,0,1));
        $player->getInventory()->addItem(Item::get(262,0,1));		
	}
}