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

class ComboFly implements Listener {
	
	/*@param Main $plugin*/
	public $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}
	
	public static function getNameDuel(){
        return ["cf", "combofly"];
	}
	
	public function getKit(Player $player){
		$player->setGamemode(2);	    
	    $player->setHealth(20);
        $player->setFood(20);
		$player->setScale(1.0);		
		
		$player->getArmorInventory()->clearAll();
		$player->getInventory()->clearAll();
		
	    $helmet = Item::get(Item::DIAMOND_HELMET, 0, 1);
        $enchantment = Enchantment::getEnchantment(Enchantment::PROTECTION);
        $helmet->addEnchantment(new EnchantmentInstance($enchantment, 1));
		$enchantment = Enchantment::getEnchantment(Enchantment::UNBREAKING);
		$helmet->addEnchantment(new EnchantmentInstance($enchantment, 1));
        $player->getArmorInventory()->setHelmet($helmet);
		
        $chestplate = Item::get(Item::DIAMOND_CHESTPLATE, 0, 1);
        $enchantment = Enchantment::getEnchantment(Enchantment::PROTECTION);
        $chestplate->addEnchantment(new EnchantmentInstance($enchantment, 1));
		$enchantment = Enchantment::getEnchantment(Enchantment::UNBREAKING);
		$chestplate->addEnchantment(new EnchantmentInstance($enchantment, 1));
        $player->getArmorInventory()->setChestplate($chestplate);
		
        $leggings = Item::get(Item::DIAMOND_LEGGINGS, 0, 1);
        $enchantment = Enchantment::getEnchantment(Enchantment::PROTECTION);
        $leggings->addEnchantment(new EnchantmentInstance($enchantment, 1));
		$enchantment = Enchantment::getEnchantment(Enchantment::UNBREAKING);
		$leggings->addEnchantment(new EnchantmentInstance($enchantment, 1));
        $player->getArmorInventory()->setLeggings($leggings);
		
        $boots = Item::get(Item::DIAMOND_BOOTS, 0, 1);
        $enchantment = Enchantment::getEnchantment(Enchantment::PROTECTION);
        $boots->addEnchantment(new EnchantmentInstance($enchantment, 1));
		$enchantment = Enchantment::getEnchantment(Enchantment::UNBREAKING);
		$boots->addEnchantment(new EnchantmentInstance($enchantment, 1));
        $player->getArmorInventory()->setBoots($boots);
		
        $sword = Item::get(Item::DIAMOND_SWORD, 0, 1);
        $enchantment = Enchantment::getEnchantment(Enchantment::SHARPNESS);
        $sword->addEnchantment(new EnchantmentInstance($enchantment, 0));
        $player->getInventory()->addItem($sword);
		
        $player->getInventory()->addItem(Item::get(466,0,3));							         						
        $player->getInventory()->addItem(Item::get(373,33,2));
        $player->getInventory()->addItem(Item::get(373,16,2));     		
		$player->getInventory()->addItem(Item::get(438,22,20));			
	}
}