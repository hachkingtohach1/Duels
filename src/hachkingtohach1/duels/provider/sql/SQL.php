<?php

namespace hachkingtohach1\duels\provider\sql;

use pocketmine\Player;

use hachkingtohach1\duels\Main;
use hachkingtohach1\duels\provider\DataBase;
use hachkingtohach1\duels\utils\SQL_utils;

class SQL implements DataBase{
	
    private $plugin;
    public $db_name;
   
    public function __construct(string $db_name){
        $this->plugin = Main::getInstance();
        $this->db_name = $db_name;
        $config = $this->plugin->getConfig()->getNested("MySQL-Info");

        $this->db = new \mysqli(
			$config["Host"] ?? "127.0.0.1",
			$config["User"] ?? "root",
			$config["Password"] ?? "",
			$config["Database"] ?? "1",
			$config["Port"] ?? 3306
		);
			
		if($this->db->connect_error){
			$this->plugin->getLogger()->critical("Could not connect to MySQL server: ".$this->db->connect_error);
			return;
		}
		if(!$this->db->query("CREATE TABLE IF NOT EXISTS user_profile(
			    username VARCHAR(20) PRIMARY KEY,
			    dailystreak FLOAT,
				bestdailystreak FLOAT
		    );"
		)){
		    $this->plugin->getLogger()->critical("Error creating table: " . $this->db->error);
		    return;
		}		
    } 
	
	public function getDatabaseName(): string{
        return $this->db_name;
    }

    public function getData(): SQL_utils{} 
   
    public function close(): void{}
  
    public function reset(): void{} 	

    public function accountExists($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);

		$result = $this->db->query("SELECT * FROM user_profile WHERE username='".$this->db->real_escape_string($player)."'");
		if($result->num_rows === null) return;
		return $result->num_rows > 0 ? true:false;
	}	
	
	public function createProfile($player, $banned = 0.0, $level = 1.0, $timeBanned = 0.0, $reason = '', $id = ''){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		if(!$this->accountExists($player)){ $this->db->query("INSERT INTO user_profile (username, dailystreak, bestdailystreak) VALUES ('".$this->db->real_escape_string($player)."', 0.0, 0.0);");
			return true;
		}
		return false;
	}
	
	public function setDailyStreak($player, $amount){
	    if($player instanceof Player){
			$player = $player->getName();
		}		
		$player = strtolower($player);
		$amount = (float)$amount;
		$this->db->query("UPDATE user_profile SET dailystreak = $amount WHERE username='".$this->db->real_escape_string($player)."'");
	    return false;
	}
	
	public function setBestDailyStreak($player, $amount){
	    if($player instanceof Player){
			$player = $player->getName();
		}		
		$player = strtolower($player);
		$amount = (float)$amount;
		$this->db->query("UPDATE user_profile SET bestdailystreak = $amount WHERE username='".$this->db->real_escape_string($player)."'");
	    return false;
	}
	
	public function getDailyStreak($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT dailystreak FROM user_profile WHERE username='".$this->db->real_escape_string($player)."'");
		$res->free();
		return $res->fetch_array()[0] ?? false;
	}

    public function getBestDailyStreak($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT bestdailystreak FROM user_profile WHERE username='".$this->db->real_escape_string($player)."'");
		$res->free();
		return $res->fetch_array()[0] ?? false;
	}		
	
	public function getAllBestDailyStreak(){
		$res = $this->db->query("SELECT * FROM user_profile");
		$ret = [];
		foreach($res->fetch_all() as $val){
			$ret[$val[0]] = $val[2];
		}
		$res->free();
		return $ret;
	}
}