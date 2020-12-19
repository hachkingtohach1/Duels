<?php

namespace hachkingtohach1\duels\language;

use pocketmine\Player;

use hachkingtohach1\duels\Main;
use hachkingtohach1\duels\language\languages\English;
use hachkingtohach1\duels\language\languages\VietNamese;
use hachkingtohach1\duels\language\languages\Spanish;

class Systems{
	
	/* LANGS */
	CONST ENGLISH = 0;
	CONST VIETNAMESE = 1;
	CONST SPANISH = 2;
	
	/*@param Main $plugin*/
	public $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	public function setLanguage(Player $player, string $language){		
		$english = array("en", "eng", "english");
		$vietnamese = array("viet", "vietnamese", "vn");
		$spanish = array("spa", "spanish", "sp");
		if(in_array($language, $english)){
			$this->plugin->languages[$player->getName()] = self::ENGLISH;
		}
		if(in_array($language, $vietnamese)){
			$this->plugin->languages[$player->getName()] = self::VIETNAMESE;
		}
        if(in_array($language, $spanish)){
			$this->plugin->languages[$player->getName()] = self::SPANISH;
		}		
	}
	
	public function translate(Player $player, string $need){		
        switch($this->plugin->languages[$player->getName()]){
			case self::ENGLISH: 
			    $translate = English::$translates[$need]; 
			break;
			case self::VIETNAMESE: 
			    $translate = VietNamese::$translates[$need]; 
			break;
			case self::SPANISH: 
			    $translate = Spanish::$translates[$need]; 
			break;
		}
        return $translate;		
	}
}