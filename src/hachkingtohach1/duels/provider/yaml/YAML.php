<?php

namespace hachkingtohach1\duels\provider\yaml;

use pocketmine\Player;
use pocketmine\utils\Config;

use hachkingtohach1\duels\Main;
use hachkingtohach1\duels\provider\DataBase;
use hachkingtohach1\duels\utils\SQL_utils;

class YAML implements DataBase{
	
    private $plugin;
    public $db_name; 
    private $db;
    public $data;
   
    public function __construct(string $db_name){
        $this->plugin = Main::getInstance();
        $this->db_name = $db_name;
        $this->data = new SQL_utils();	
		$this->plugin->profile = new Config($this->plugin->getDataFolder()."profile.yml", Config::YAML); 
    }
 
    public function getData(): SQL_utils{
        return $this->data;
    } 
	
	public function getDatabaseName(): string{
        return $this->db_name;
    }  
   
    public function close(): void{}
  
    public function reset(): void{}
   
    public function saveAll(): void{
        $this->plugin->profile->save();
    }
	
	public function createProfile($player, float $dailystreak, float $bestdailystreak){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
        $data = $this->plugin->profile;
		if(!($data->exists($player))){
			$data->set($player, [
			    "dailystreak" => $dailystreak,
				"bestdailystreak" => $bestdailystreak
			]);
			$this->plugin->getDatabase()->saveAll();
		}
	}
	
	public function setDailyStreak(Player $player, float $amount){
        $data = $this->plugin->profile;
		$data->set(strtolower($player->getName()), [
			"dailystreak" => $amount,
		    "bestdailystreak" => $this->getBestdailystreak($player)
		]);
		$this->plugin->getDatabase()->saveAll();
	}
	
	public function setBestDailyStreak(Player $player, float $amount){
        $data = $this->plugin->profile;
		$data->set(strtolower($player->getName()), [
			"dailystreak" => $this->getdailystreak($player),
		    "bestdailystreak" => $amount
		]);
		$this->plugin->getDatabase()->saveAll();
	}		
	
	public function getDailyStreak($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
        $data = $this->plugin->profile;
        $result = $data->get($player)["dailystreak"];
		return $result;
	}
	
	public function getBestDailyStreak($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
        $data = $this->plugin->profile;
        $result = $data->get($player)["bestdailystreak"];
		return $result;
	}
	
	public function getAllBestDailyStreak(){
		$array = [];
		foreach($this->plugin->profile->getAll() as $i){
			$array[$i] = $i["bestdailystreak"];
		}		
		return $array;
	}
}