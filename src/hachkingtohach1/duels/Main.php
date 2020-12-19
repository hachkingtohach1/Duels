<?php

namespace hachkingtohach1\duels;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;

use hachkingtohach1\duels\language\Systems as Language;
use hachkingtohach1\duels\duels\Systems;
use hachkingtohach1\duels\duels\mode\OneVsOne;
use hachkingtohach1\duels\duels\mode\ComboFly;
use hachkingtohach1\duels\duels\mode\SkyWars;
use hachkingtohach1\duels\duels\mode\NoDeBuff;
use hachkingtohach1\duels\duels\mode\BuildUHC;
use hachkingtohach1\duels\duels\mode\Classic;
use hachkingtohach1\duels\duels\mode\Bridge;
use hachkingtohach1\duels\provider\DataBase;
use hachkingtohach1\duels\provider\sql\SQL;
use hachkingtohach1\duels\provider\yaml\YAML;

class Main extends PluginBase implements Listener{
	
	/*STATUS*/		
	CONST WAITING = 0;
	CONST READY = 1;
	CONST PLAYING = 2;
	CONST RESTARTING = 3;
	
	/*DUELS*/
	CONST ONEVSONE = 0;
	CONST COMBOFLY = 1;
	CONST SKYWARS = 2;
	CONST NODEBUFF = 3;
	CONST BUILDUHC = 4;
	CONST CLASSIC = 5;
	CONST BRIDGE = 6;
	
	public $armorsColor = [
	    "RED" => [255, 0, 0],
		"BLUE" => [0, 0, 255]
	];	
	
	/*@param array $languages*/
	public $languages = [];

	/*@param array $inGame*/
	public $inGame = [];
	
	/*@param array $duels*/
	public $duels = [];
	
	/*@param array $chestRefill*/
	public $chestRefill = [];
	
	/*@param array $isOnGround*/
	public $isOnGround = [];
	
	/*@param array $killThem*/
	public $killThem = [];
	
	/*@param array $kills*/
	public $kills = [];
	
	/*@param $saveDataPlayer*/
	public $saveDataPlayer = [];
	
	/*@param $events*/
	public $events = null;
	
	/*@param $combofly*/
	public $combofly = null;
	
	/*@param $onevsone*/
	public $onevsone = null;
	
	/*@param $skywars*/
	public $skywars = null;
	
	/*@param $nodebuff*/
	public $nodebuff = null;
	
	/*@param $builduhc*/
	public $builduhc = null;
	
	/*@param $classic*/
	public $classic = null;
	
	/*@param $bridge*/
	public $bridge = null;
	
	/*@param $database*/
	public $database = null;
	
	/*@param $profile*/
	public $profile = null;
	
	/*@param $instance*/
	private static $instance = null;
	
	public function onLoad(){
        self::$instance = $this;
	}
	
	public static function getInstance(): Main{
        return self::$instance;
    }
	
	public function onEnable(){		
		if(!is_dir($this->getDataFolder()."duels")) @mkdir($this->getDataFolder() . "duels");
		if(!is_dir($this->getDataFolder()."saves")) @mkdir($this->getDataFolder() . "saves");
		$this->saveDefaultConfig(); 
		$this->registerModes();
		$this->createDataArenas();
		$this->checkDataBase();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	
	public function createDataArenas(){
		foreach(glob($this->getDataFolder()."duels".DIRECTORY_SEPARATOR."*.yml") as $arenaFile) {
            $config = new Config($arenaFile, Config::YAML);
            $this->duels[basename($arenaFile, ".yml")] = $config->getAll(\false);           
			$level = $config->get('level');
			$class = new Systems($this);
		    $class->loadMap($level);
		}
	}
	
	public function checkDataBase(){
		switch($this->getConfig()->get("database")) {
			case "sql":
			    $this->database = new SQL("mysql");
			break;
			case "yml":
			    $this->database = new YAML("yml");
			break;
			default:
			    $this->getLogger()->warning('[DATABASE] Your database not found!');
			    $this->getServer()->shutDown();	
			break;
		}
	}
	
	public function registerModes(){
		$this->events = new Systems($this);
		$this->combofly = new ComboFly($this);
		$this->onevsone = new OneVsOne($this);
		$this->skywars = new SkyWars($this);
		$this->nodebuff = new NoDeBuff($this);
		$this->builduhc = new BuildUHC($this);
		$this->classic = new Classic($this);
		$this->bridge = new Bridge($this);
	}
	
	public function onPlayerJoinEvent(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		if(!isset($this->languages[$player->getName()])){
		    $this->languages[$player->getName()] = 0;
		}
		$this->getDataBase()->createProfile($player, 0.0, 0.0);
	}
	
	public function getSystems(){
        return new Systems($this);
	}
	
	public function getLangSystems(){
        return new Language($this);
	}
	
	public function getDatabase(): Database{
        return $this->database;
	}
}