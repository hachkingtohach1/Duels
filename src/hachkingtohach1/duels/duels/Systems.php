<?php

namespace hachkingtohach1\duels\duels;

use http\Exception\InvalidArgumentException;
use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3 as VT3PMMP;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\inventory\ChestInventory;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerExhaustEvent;

use hachkingtohach1\duels\Main;
use hachkingtohach1\duels\math\Vector3;
use hachkingtohach1\duels\task\ArenaScheduler;
use hachkingtohach1\duels\language\Systems as SystemsLang;
use hachkingtohach1\duels\duels\mode\OneVsOne;
use hachkingtohach1\duels\duels\mode\ComboFly;
use hachkingtohach1\duels\duels\mode\SkyWars;
use hachkingtohach1\duels\duels\mode\NoDeBuff;
use hachkingtohach1\duels\duels\mode\BuildUHC;
use hachkingtohach1\duels\duels\mode\Classic;
use hachkingtohach1\duels\duels\mode\Bridge;
use hachkingtohach1\duels\utils\BlockUtils;
use ZipArchive;

class Systems implements Listener {	
	
	/*@param Main $plugin*/
	public $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
		$this->plugin->getScheduler()->scheduleRepeatingTask(new ArenaScheduler($this), 20);
	    $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}
	
	public function joinRandom(Player $player, string $mode){			
        if(isset($this->plugin->inGame[$player->getName()])) return;
        switch($mode){
			case in_array($mode, OneVsOne::getNameDuel()): $MODE = 0; $STRINGMODE = "onevsone"; break;
			case in_array($mode, ComboFly::getNameDuel()): $MODE = 1; $STRINGMODE = "combofly"; break;
			case in_array($mode, SkyWars::getNameDuel()): $MODE = 2; $STRINGMODE = "skywars"; break;
			case in_array($mode, NoDeBuff::getNameDuel()): $MODE = 3; $STRINGMODE = "nodebuff"; break;
			case in_array($mode, BuildUHC::getNameDuel()): $MODE = 4; $STRINGMODE = "builduhc"; break;
			case in_array($mode, Classic::getNameDuel()): $MODE = 5; $STRINGMODE = "classic"; break;
			case in_array($mode, Bridge::getNameDuel()): $MODE = 6; $STRINGMODE = "bridge"; break;
			default: throw new \InvalidArgumentException("Invalid duels mode $mode");
		}
        $cancelled = false;
		foreach($this->plugin->duels as $duel){
			if($this->plugin->duels[$duel['id']]['mode'] === $STRINGMODE){
                if($cancelled === false and count($this->plugin->duels[$duel['id']]['players']) === 1){
					$this->joinDuel($player, $MODE, $duel['id']);
					$cancelled = true;
				}elseif($cancelled === false and count($this->plugin->duels[$duel['id']]['players']) === 0){
					$this->joinDuel($player, $MODE, $duel['id']);
					$cancelled = true;
				}					
			}
		}		
	}
	
	public function getCountPlayers(string $mode){
		$count = 0;
		switch($mode){
			case in_array($mode, OneVsOne::getNameDuel()): $getMode = "onevsone"; break;
			case in_array($mode, ComboFly::getNameDuel()): $getMode = "combofly"; break;
			case in_array($mode, SkyWars::getNameDuel()): $getMode = "skywars"; break;
			case in_array($mode, NoDeBuff::getNameDuel()): $getMode = "nodebuff"; break;
			case in_array($mode, BuildUHC::getNameDuel()): $getMode = "builduhc"; break;
			case in_array($mode, Classic::getNameDuel()): $getMode = "classic"; break;
			case in_array($mode, Bridge::getNameDuel()): $getMode = "bridge"; break;
		    default: throw new \InvalidArgumentException("Invalid duels mode $mode");
		}
		foreach($this->plugin->duels as $duel){
			if(empty($this->plugin->duels[$duel['id']]['mode'])) return 0;
			if($this->plugin->duels[$duel['id']]['mode'] === $getMode){
				$count += count($this->plugin->duels[$duel['id']]['players']);
			}
		}
		return $count;
	}
    
	public function joinDuel(Player $player, int $duel, string $id_duel){      
		if($this->plugin->duels[$id_duel]['status'] === Main::RESTARTING){
			return;
		}
		if(count($this->plugin->duels[$id_duel]['players']) >= 2){
			$player->sendMessage($this->getLang()->translate($player, "FULL"));
			return;
		}			
		if(count($this->plugin->duels[$id_duel]['players']) >= 1){	
			$this->plugin->duels[$id_duel]['status'] = Main::READY;                
		}
		// Register 
		$this->plugin->duels[$id_duel]['players'][$player->getName()] = $player;        		
		// Setup 
		$player->setGamemode(2);	    
	    $player->setHealth(20);
        $player->setFood(20);		
		$player->getArmorInventory()->clearAll();
		$player->getInventory()->clearAll();
		$normal = true;
		if($this->plugin->duels[$id_duel]['mode'] === "bridge"){
			if(isset($this->plugin->kills[$player->getName()])){
				$this->plugin->kills[$player->getName()] = 0;
			}else{
				$this->plugin->kills[$player->getName()] = 0;
			}
			$cancelledT = false;
			if($this->plugin->duels[$id_duel]['teams']['RED'] === 0){
				$this->plugin->duels[$id_duel]['teams']['RED'] = $player->getName();
				$cancelledT = true;
		    }
			if($cancelledT === false and $this->plugin->duels[$id_duel]['teams']['BLUE'] === 0){
				$this->plugin->duels[$id_duel]['teams']['BLUE'] = $player->getName();
			}
			if($this->plugin->duels[$id_duel]['slots']['spawn1'] === 0 and $this->plugin->duels[$id_duel]['teams']['BLUE'] === $player->getName()){
			    $player->teleport(Position::fromObject(Vector3::fromString(
			            $this->plugin->duels[$id_duel]['spawn1']
				    )->add(0.5, 0, 0.5),
				    $this->plugin->getServer()->getLevelByName($this->plugin->duels[$id_duel]['level']))
			    ); 
				$this->plugin->duels[$id_duel]['slots']['spawn1'] = $player->getName();			
			}
			if($this->plugin->duels[$id_duel]['slots']['spawn2'] === 0 and $this->plugin->duels[$id_duel]['teams']['RED'] === $player->getName()){
			    $player->teleport(Position::fromObject(Vector3::fromString(
			            $this->plugin->duels[$id_duel]['spawn2']
				    )->add(0.5, 0, 0.5),
				    $this->plugin->getServer()->getLevelByName($this->plugin->duels[$id_duel]['level']))
			    ); 
				$this->plugin->duels[$id_duel]['slots']['spawn2'] = $player->getName();							
		    }
			$normal = false;
		}
		if($this->plugin->duels[$id_duel]['mode'] === "skywars"){            				    
			$cancelled = false;	
			if($this->plugin->duels[$id_duel]['slots']['spawn1'] === 0){
			    $player->teleport(Position::fromObject(Vector3::fromString(
			            $this->plugin->duels[$id_duel]['spawn1']
				    )->add(0.5, 0, 0.5),
				    $this->plugin->getServer()->getLevelByName($this->plugin->duels[$id_duel]['level']))
			    ); 
				$this->plugin->duels[$id_duel]['slots']['spawn1'] = $player->getName();			
			    $cancelled = true;
			}
			if($cancelled === false and $this->plugin->duels[$id_duel]['slots']['spawn2'] === 0){
			    $player->teleport(Position::fromObject(Vector3::fromString(
			            $this->plugin->duels[$id_duel]['spawn2']
				    )->add(0.5, 0, 0.5),
				    $this->plugin->getServer()->getLevelByName($this->plugin->duels[$id_duel]['level']))
			    ); 
				$this->plugin->duels[$id_duel]['slots']['spawn2'] = $player->getName();							
		    }
			if($this->plugin->duels[$id_duel]['mode'] === "skywars"){
				BlockUtils::cageGlass($player);	
			}
			$normal = false;
		}
		if($normal === true){
			$player->teleport(Position::fromObject(Vector3::fromString(
			        $this->plugin->duels[$id_duel]['spawnlobby']
			    )->add(0.5, 1, 0.5),
			    $this->plugin->getServer()->getLevelByName($this->plugin->duels[$id_duel]['level']))
		    ); 
		}
		$this->plugin->inGame[$player->getName()] = [
			"PLAYER" => $player,
			"DUEL" => $duel,
			"ID_DUEL" => $id_duel
		];
		
		$player->sendMessage($this->getLang()->translate($player, "JOINED"));		
	}	
	
	public function leaveDuel(Player $player){
        if(!isset($this->plugin->inGame[$player->getName()])) return;	
		$id_duel = $this->plugin->inGame[$player->getName()]['ID_DUEL'];
        if(!empty($this->plugin->duels[$id_duel]['slots'])){ 
			$this->unsetSlots($player);
		}
        if(!empty($this->plugin->duels[$id_duel]['teams'])){ 
			$this->unsetTeams($player);
		}
        if($this->plugin->duels[$id_duel]['status'] === Main::READY){ 
			$this->plugin->duels[$id_duel]['status'] = Main::WAITING;
		}  		
        unset($this->plugin->duels[$id_duel]['players'][$player->getName()]);
        unset($this->plugin->inGame[$player->getName()]);				
       	$player->sendMessage($this->getLang()->translate($player, "LEAVE"));
		// Setup 
        $player->setGamemode($this->plugin->getServer()->getDefaultGamemode());	    
	    $player->setHealth(20);
        $player->setFood(20);
		$player->setScale(1.0);		
		$player->getArmorInventory()->clearAll();
		$player->getInventory()->clearAll();		
	}
	
	public function startDuel(string $id_duel){
		$this->plugin->duels[$id_duel]['status'] = Main::PLAYING;
        foreach($this->plugin->duels[$id_duel]['players'] as $player){
			$DUEL = $this->plugin->inGame[$player->getName()]['DUEL'];						            
			$player->sendMessage($this->getLang()->translate($player, "STARTED"));			
            if(in_array($DUEL, [Main::SKYWARS, Main::BRIDGE])){              				
				return;
			}				
            if(!empty($this->plugin->duels[$id_duel]['spawn1'])){
			    $player->teleport(Position::fromObject(Vector3::fromString(
			            $this->plugin->duels[$id_duel]['spawn1']
				    )->add(0.5, 1, 0.5),
				    $this->plugin->getServer()->getLevelByName($this->plugin->duels[$id_duel]['level']))
			    ); 
				unset($this->plugin->duels[$id_duel]['spawn1']);               				
			}
			if(!empty($this->plugin->duels[$id_duel]['spawn2'])){
			    $player->teleport(Position::fromObject(Vector3::fromString(
			            $this->plugin->duels[$id_duel]['spawn2']
				    )->add(0.5, 1, 0.5),
				    $this->plugin->getServer()->getLevelByName($this->plugin->duels[$id_duel]['level']))
			    ); 
				unset($this->plugin->duels[$id_duel]['spawn2']);				
		    }				
		}
	}
	
	public function restartDuel(string $id_duel){
		//$this->plugin->duels[$id_duel]['status'] = Main::RESTARTING;
		// Teleport all players in duel return to lobby		
		foreach($this->plugin->duels[$id_duel]['players'] as $player){
			$player->teleport($this->plugin->getServer()->getDefaultLevel()->getSpawnLocation());
		}
		$level = $this->plugin->duels[$id_duel]['level'];		
		$this->loadMap($level);
		// Reload data for duel
        $config = new Config($this->plugin->getDataFolder()."duels".DIRECTORY_SEPARATOR.$id_duel.".yml", Config::YAML);
        $this->plugin->duels[$id_duel] = $config->getAll();       
	}
	
	public function scoringBridge(string $id_duel, Player $player, $id, $meta){
		$continue = false;
		$red = false;
		$blue = false;
		if($continue !== true and $id === 159 and $meta === 14 and $this->plugin->duels[$id_duel]['teams']['BLUE'] === $player->getName()){
			$blue = true;
			$continue = true;
		}
		if($continue !== true and $id === 159 and $meta === 11 and $this->plugin->duels[$id_duel]['teams']['RED'] === $player->getName()){
			$red = true;
			$continue = true;
		}
		if($id === 159 and $meta === 14 and $this->plugin->duels[$id_duel]['teams']['RED'] === $player->getName()){
			if($this->plugin->duels[$id_duel]['slots']['spawn1'] === $player->getName()){
			    $pos = Vector3::fromString($this->plugin->duels[$id_duel]['spawn1']);
				$player->teleport($pos);				    
			}	
            if($this->plugin->duels[$id_duel]['slots']['spawn2'] === $player->getName()){
				$pos = Vector3::fromString($this->plugin->duels[$id_duel]['spawn2']);
				$player->teleport($pos);
			}
			return;
		}
		if($id === 159 and $meta === 11 and $this->plugin->duels[$id_duel]['teams']['BLUE'] === $player->getName()){
			if($this->plugin->duels[$id_duel]['slots']['spawn1'] === $player->getName()){
			    $pos = Vector3::fromString($this->plugin->duels[$id_duel]['spawn1']);
				$player->teleport($pos);				    
		    }	
            if($this->plugin->duels[$id_duel]['slots']['spawn2'] === $player->getName()){
				$pos = Vector3::fromString($this->plugin->duels[$id_duel]['spawn2']);
				$player->teleport($pos);
			}
			return;
		}
		if($continue === true){			
			foreach($this->plugin->duels[$id_duel]['players'] as $p){			    
				if($this->plugin->duels[$id_duel]['slots']['spawn1'] === $p->getName()){
			        $pos = Vector3::fromString($this->plugin->duels[$id_duel]['spawn1']);
				    $p->teleport($pos);				    
				}	
                if($this->plugin->duels[$id_duel]['slots']['spawn2'] === $p->getName()){
				    $pos = Vector3::fromString($this->plugin->duels[$id_duel]['spawn2']);
				    $p->teleport($pos);
				}
				$DUEL = $this->plugin->inGame[$player->getName()]['DUEL'];	
				$this->getKit($DUEL, $p);
			}
			if($blue === true){
				$this->plugin->duels[$id_duel]['points']['BLUE'] += 1;
			}
			if($red === true){
				$this->plugin->duels[$id_duel]['points']['RED'] += 1;
		    }
		}
	}
	
	public function checkEnd(string $id_duel) :bool{
		if($this->plugin->duels[$id_duel]['mode'] === "bridge"){
			if($this->plugin->duels[$id_duel]['points']['BLUE'] >= 5 and $this->plugin->duels[$id_duel]['points']['RED'] < 5){
				foreach($this->plugin->duels[$id_duel]['players'] as $player){
					$player->addTitle($this->getLang()->translate($player, "BLUE_WON"));
				}
				return true;
			}
			if($this->plugin->duels[$id_duel]['points']['RED'] >= 5 and $this->plugin->duels[$id_duel]['points']['BLUE'] < 5){
				foreach($this->plugin->duels[$id_duel]['players'] as $player){
					$player->addTitle($this->getLang()->translate($player, "RED_WON"));
				}
				return true;
			}
		}
		if(count($this->plugin->duels[$id_duel]['players']) <= 1){
			return true;
		}			
		return false;
	}

    public function getKit($DUEL, Player $player){
		if(!isset($this->plugin->inGame[$player->getName()])) return;
		switch($DUEL){
			case Main::ONEVSONE: $main = new OneVsOne($this->plugin); $main->getKit($player); break;
		    case Main::COMBOFLY: $main = new ComboFly($this->plugin); $main->getKit($player); break;
			case Main::SKYWARS: $main = new SkyWars($this->plugin); $main->getKit($player); break;
			case Main::NODEBUFF: $main = new NoDeBuff($this->plugin); $main->getKit($player); break;
			case Main::BUILDUHC: $main = new BuildUHC($this->plugin); $main->getKit($player); break;
	        case Main::CLASSIC: $main = new Classic($this->plugin); $main->getKit($player); break;
			case Main::BRIDGE:
			    $main = new Bridge($this->plugin);
			    $id_duel = $this->plugin->inGame[$player->getName()]['ID_DUEL'];
		        $RED = $this->plugin->armorsColor['RED'];
				$BLUE = $this->plugin->armorsColor['BLUE'];
				if($this->plugin->duels[$id_duel]['teams']['BLUE'] === $player->getName()){
			        $main->getKit($player, $BLUE[0], $BLUE[1], $BLUE[2]);
				}
		        if($this->plugin->duels[$id_duel]['teams']['RED'] === $player->getName()){
			        $main->getKit($player, $RED[0], $RED[1], $RED[2]);
				}
			break;
		}
	}	

    public function loadMap(string $folderName) :?Level{
		$DS = DIRECTORY_SEPARATOR;
		$path = $this->plugin->getServer()->getDataPath();
		
        if(!file_exists($path."worlds".$DS.$folderName)) return null;    
        if(!$this->plugin->getServer()->isLevelGenerated($folderName)) return null;
		
        if($this->plugin->getServer()->isLevelLoaded($folderName)) {
            $this->plugin->getServer()->getLevelByName($folderName)->unload(true);
        }
        $zipPath = $this->plugin->getDataFolder()."saves".$DS.$folderName.".zip";
        $zipArchive = new ZipArchive();
        $zipArchive->open($zipPath);
        $zipArchive->extractTo($path."worlds");
        $zipArchive->close();
        $this->plugin->getServer()->loadLevel($folderName);
        return $this->plugin->getServer()->getLevelByName($folderName);
    }	
	
	public function getLang(){
        return new SystemsLang($this->plugin);
	}
	
	/*****************
	 * EVENTS
	 *****************/
	public function onEntityLevelChangeEvent(EntityLevelChangeEvent $event){
		$entity = $event->getEntity();
		if($entity instanceof Player){
			if(isset($this->plugin->inGame[$entity->getName()])){
				$this->leaveDuel($entity);
			}
		}
	}
	
	/**
     * @param PlayerMoveEvent $event
     */
	public function onPlayerMoveEvent(PlayerMoveEvent $event){
		$player = $event->getPlayer();		
		if(isset($this->plugin->inGame[$player->getName()])){
			$blockDown = $player->getLevel()->getBlock(new VT3PMMP($player->getX(), $player->getY() - 1, $player->getZ()));
			$id_duel = $this->plugin->inGame[$player->getName()]['ID_DUEL'];
			if($this->plugin->duels[$id_duel]['mode'] === "bridge"){    
				if($blockDown->getId() === 159){
				    $this->scoringBridge($id_duel, $player, $blockDown->getId(), $blockDown->getDamage()); // Scoring
			    }
				if($this->plugin->duels[$id_duel]['status'] === Main::PLAYING){
				    $item = Item::get(262, 0, 1);
			        if(!$player->getInventory()->contains($item)){
				        $player->getInventory()->addItem($item);					
					}
				}
			}
			if($player->isOnGround()){
			    if(!isset($this->plugin->isOnGround[$player->getName()])){
				    $this->plugin->isOnGround[$player->getName()] = [$player->getX(), $player->getY(), $player->getZ()];
				}else{
				    $this->plugin->isOnGround[$player->getName()] = [$player->getX(), $player->getY(), $player->getZ()];
				}
			}
			if($this->plugin->duels[$id_duel]['status'] === Main::WAITING ?? Main::READY ?? Main::RESTARTING){
			    if($player->getY() <= -3){
					if(!isset($this->plugin->isOnGround[$player->getName()])) return;
					$pos = $this->plugin->isOnGround[$player->getName()];
					$player->teleport(new VT3PMMP($pos[0], $pos[1], $pos[2]));
					$player->setHealth($player->getMaxHealth());
				}
			}else{
				if($player->getY() <= -5){
					foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
						if(isset($this->plugin->inGame[$p->getName()])){
							if($this->plugin->inGame[$p->getName()]["ID_DUEL"] === $id_duel){
							    if(!isset($this->plugin->killThem[$player->getName()])){	
									$message = $this->getLang()->translate($p, "FELL_VOID");
					                $p->sendMessage(str_replace("{arg1}", $player->getName(), $message));
								}else{
									$killBy = $this->plugin->killThem[$player->getName()];
								    $message = $this->getLang()->translate($p, "FELL_BY");
					                $array_1 = ["{arg1}", "{arg2}"];
					                $array_2 = [$killBy[1]->getName(), $killBy[0]->getName()];
					                $p->sendMessage(str_replace($array_1, $array_2, $message));
								    if(isset($this->plugin->kills[$killBy[0]->getName()])){
									    $this->plugin->kills[$killBy[0]->getName()] += 1;
									}
								}
							    if(isset($this->plugin->killThem[$player->getName()])){
							        unset($this->plugin->killThem[$player->getName()]);
                                }
							}
						}
					}
					if($this->plugin->duels[$id_duel]['mode'] === "bridge"){
						if($this->plugin->duels[$id_duel]['slots']['spawn1'] === $player->getName()){
							$pos = Vector3::fromString($this->plugin->duels[$id_duel]['spawn1']);
						    $player->teleport($pos);
						}		
                        if($this->plugin->duels[$id_duel]['slots']['spawn2'] === $player->getName()){
							$pos = Vector3::fromString($this->plugin->duels[$id_duel]['spawn2']);
							$player->teleport($pos);
						}  
                        $player->setHealth($player->getMaxHealth());						
						return;
					}
					if(!isset($this->plugin->killThem[$player->getName()])){	
 					    if($this->plugin->duels[$id_duel]['mode'] === "bridge"){
							return;
						}
						$this->leaveDuel($player);
						return;
					}
					$killBy = $this->plugin->killThem[$player->getName()];
					$name_arena = $this->plugin->duels[$id_duel]['name'];
					
					foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
					    $message = $this->getLang()->translate($p, "DEFEATED");
					    $array_1 = ["{arg1}", "{arg2}", "{arg3}"];
					    $array_2 = [$killBy[1]->getName(), $killBy[0]->getName(), $name_arena];
					    $p->sendMessage(str_replace($array_1, $array_2, $message));
				    }
					$this->leaveDuel($player);
				}
			}
		}
	}
	
	/**
	 * It will calls when player open chest
	 * 
	 * @param InventoryOpenEvent $event
	 */
	public function onInventoryOpenEvent(InventoryOpenEvent $event){
		$inv = $event->getInventory();
		$player = $event->getPlayer();		
		if(!($inv instanceof ChestInventory)){
			return;
		}
		if(isset($this->plugin->inGame[$player->getName()])){	
		    $id_duel = $this->plugin->inGame[$player->getName()]['ID_DUEL'];
		    $this->refillChest($id_duel, $event);
		}
	}
	
	public function refillChest(string $id_duel, $chest){
		if($this->plugin->duels[$id_duel]['mode'] !== "skywars") return;
		$block = $chest->getInventory()->getHolder();
		$data = $block->getX().",".$block->getY().",".$block->getZ().",".$block->getLevel()->getName();
		if (isset($this->plugin->chestRefill[$id_duel][$data])) { 
		    return; 
		}
		$itemsChest = new ChestItems();
		$this->plugin->chestRefill[$id_duel][$data] = $data;
		$chest->getInventory()->clearAll();
		$inv = $chest->getInventory();
		$inv->clearAll();        	
		$x = (int)$block->getX(); $y = (int)$block->getY(); $z = (int)$block->getZ();
	    foreach($this->plugin->duels[$id_duel]['chests']['island'] as $a){
			foreach($this->plugin->duels[$id_duel]['chests']['mid'] as $b){
				if("$x,$y,$z" == $a){
			        $items = [];
                    $items[] = $itemsChest->blocks[array_rand($itemsChest->blocks, 1)];
                    $items[] = $itemsChest->weapons[array_rand($itemsChest->weapons, 1)];
                    $items[] = $itemsChest->armors[array_rand($itemsChest->armors, 1)];
                    $items[] = $itemsChest->potions[array_rand($itemsChest->potions, 1)];
                    $items[] = $itemsChest->others[array_rand($itemsChest->others, 1)];
                    $items[] = $itemsChest->specials[array_rand($itemsChest->specials, 1)];						                    
					foreach($items as $i){	
					    $slot = rand(0, 26);							
                        if(count($inv->getContents()) < 5 and $inv->getItem($slot)->getId() === 0){
							for($m = 0; $m <= 26; $m++){
							    if($inv->getItem($slot)->getId() === $i[0]){
									return;
								}
							}
							$item = Item::get($i[0], $i[1], $i[2]);											
						    $inv->setItem($slot, $item);
						}						
					}
			    }
				if("$x,$y,$z" == $b){
			        $items = [];
                    $items[] = $itemsChest->blocks[array_rand($itemsChest->blocks, 1)];
                    //$items[] = $itemsChest->weapons[array_rand($itemsChest->weapons, 1)];
                    $items[] = $itemsChest->armors[array_rand($itemsChest->armors, 1)];
                    $items[] = $itemsChest->potions[array_rand($itemsChest->potions, 1)];
                    $items[] = $itemsChest->others[array_rand($itemsChest->others, 1)];
                    $items[] = $itemsChest->specials[array_rand($itemsChest->specials, 1)];
                    $items[] = $itemsChest->mid[array_rand($itemsChest->mid, 1)];					
					foreach($items as $i){	
					    $slot = rand(0, 26);	
                        if(count($inv->getContents()) < rand(6, 8) and $inv->getItem($slot)->getId() === 0){
							for($m = 0; $m <= 26; $m++){
							    if($inv->getItem($slot)->getId() === $i[0]){
									return;
								}
							}
							$item = Item::get($i[0], $i[1], $i[2]);								
							if($i[3] === true){
								foreach($i[4] as $enchant){
								    $enchantment = Enchantment::getEnchantment($enchant[0]);
		                            $item->addEnchantment(new EnchantmentInstance($enchantment, $enchant[1]));
								}
							}	
                            if($i[0] === 261){
								$inv->addItem(Item::get(262, 0, 16));
							}								
						    $inv->setItem($slot, $item);
						}						
					}
				}
			}
		}
	}
	
	public function onEntityDamageEvent(EntityDamageEvent $event){
		$entity = $event->getEntity();
		$cause = $event->getCause();
		if($entity instanceof Player){
		    if(isset($this->plugin->inGame[$entity->getName()]) and $cause === EntityDamageEvent::CAUSE_FALL){
			    $event->setCancelled(true);
			}
		}
		if($event instanceof EntityDamageByEntityEvent){
			$damager = $event->getDamager();			
			if($entity instanceof Player and $damager instanceof Player){               				
				if(!isset($this->plugin->killThem[$entity->getName()])){
					$this->plugin->killThem[$entity->getName()] = [$entity, $damager];
				}else{
					$this->plugin->killThem[$entity->getName()] = [$entity, $damager];
				}				
				if(isset($this->plugin->inGame[$entity->getName()]) and isset($this->plugin->inGame[$damager->getName()])){				    
					$id_duel = $this->plugin->inGame[$entity->getName()]['ID_DUEL'];
                    $status = $this->plugin->duels[$id_duel]['status'];
					if(in_array($status, [Main::WAITING, Main::READY])){
						$event->setCancelled(true);
					}
					if(in_array($status, [Main::PLAYING, Main::RESTARTING])){
						if($this->plugin->duels[$id_duel]['mode'] === "combofly"){
							$event->setKnockBack(0.28);
						}
						$event->setAttackCooldown(10);
					}					
					if($event->getFinalDamage() >= $entity->getHealth()){
						if(isset($this->plugin->inGame[$entity->getName()])){							
			                $id_duel = $this->plugin->inGame[$entity->getName()]['ID_DUEL'];
                            $name_arena = $this->plugin->duels[$id_duel]['name'];							            
							if($this->plugin->duels[$id_duel]['mode'] === "bridge"){
								$event->setCancelled(true);
								if(isset($this->plugin->kills[$damager->getName()])){
									$this->plugin->kills[$damager->getName()] += 1;
								}
								if(isset($this->plugin->killThem[$entity->getName()])){
									unset($this->plugin->killThem[$entity->getName()]);
								}
								if($this->plugin->duels[$id_duel]['slots']['spawn1'] === $entity->getName()){
							        $pos = Vector3::fromString($this->plugin->duels[$id_duel]['spawn1']);
						            $entity->teleport($pos);
								}	
                                if($this->plugin->duels[$id_duel]['slots']['spawn2'] === $entity->getName()){
							        $pos = Vector3::fromString($this->plugin->duels[$id_duel]['spawn2']);
							        $entity->teleport($pos);
								}
								$entity->setHealth($entity->getMaxHealth());
								return;
							}
							foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
								$message = $this->getLang()->translate($player, "DEFEATED");
					            $array_1 = ["{arg1}", "{arg2}", "{arg3}"];
					            $array_2 = [$damager->getName(), $entity->getName(), $name_arena];
					            $player->sendMessage(str_replace($array_1, $array_2, $message));			                
							}
				            if($this->getDailyStreak($entity) > 0){
					            $this->setBestDailyStreak($entity, $this->getDailyStreak($entity));
					            $this->setDailyStreak($entity, 0.0);
					        }
				            $this->setDailyStreak($damager, $this->getDailyStreak($damager) + 1);
			                $this->leaveDuel($entity);
						}
					}
				}
			}
		}
	}
	
	public function onPlayerQuitEvent(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		if(isset($this->plugin->inGame[$player->getName()])){
			$this->leaveDuel($player);
		}
	}
	
	public function onPlayerExhaustEvent(PlayerExhaustEvent $event){
		$player = $event->getPlayer();
		if(isset($this->plugin->inGame[$player->getName()])){
			$event->setCancelled(true);
		}
	}
	
	public function unsetTeams(Player $player){
		if(isset($this->plugin->inGame[$player->getName()])){
			$id_duel = $this->plugin->inGame[$player->getName()]['ID_DUEL'];
			if($this->plugin->duels[$id_duel]['teams']['RED'] === $player->getName()){
				$this->plugin->duels[$id_duel]['teams']['RED'] = 0;
			}
			if($this->plugin->duels[$id_duel]['teams']['BLUE'] === $player->getName()){
				$this->plugin->duels[$id_duel]['teams']['BLUE'] = 0;
			}
		}
	}
	
	public function unsetSlots(Player $player){
		if(isset($this->plugin->inGame[$player->getName()])){
			$id_duel = $this->plugin->inGame[$player->getName()]['ID_DUEL'];
			if($this->plugin->duels[$id_duel]['slots']['spawn1'] === $player->getName()){
				$this->plugin->duels[$id_duel]['slots']['spawn1'] = 0;
			}
			if($this->plugin->duels[$id_duel]['slots']['spawn2'] === $player->getName()){
				$this->plugin->duels[$id_duel]['slots']['spawn2'] = 0;
			}
		}
	}
	
	public function setDailyStreak(Player $player, float $amount){
		return $this->plugin->getDatabase()->setDailyStreak($player, $amount);
	}
	
	public function setBestDailyStreak(Player $player, float $amount){
		return $this->plugin->getDatabase()->setBestDailyStreak($player, $amount);
	}
	
	public function getDailyStreak(Player $player){
		return $this->plugin->getDatabase()->getDailyStreak($player);
	}
	
	public function getBestDailyStreak(Player $player){
		return $this->plugin->getDatabase()->getBestDailyStreak($player);
	}
	
	public function getTipBridge(Player $player, string $id_duel, $time){
		$status = "";
	    $statusOriginal = $this->plugin->duels[$id_duel]['status'];
		switch($statusOriginal){
			case Main::WAITING: 
			    $status = "§aWaiting..";			
			break;
			case Main::READY: 
				$status = "§aStarting in: ".$time;			
			break;
            case Main::PLAYING: 
                $status = "§aTime Up: ".gmdate("i:s", $time);						
			break;
			case Main::RESTARTING: 
			    $status = "§5Restarting.."; 
			break;			
		}
		$scoreboard = $this->plugin->getConfig()->get("scoreboard_bridge");
		$mode = $this->plugin->duels[$id_duel]['mode'];
		$pointsred = $this->plugin->duels[$id_duel]['points']['RED'];
		$pointsblue = $this->plugin->duels[$id_duel]['points']['BLUE'];	
		$kills = $this->plugin->kills[$player->getName()];
		if($this->plugin->duels[$id_duel]['teams']['RED'] === $player->getName()){
			$goals = $this->plugin->duels[$id_duel]['points']['RED'];	
		}elseif($this->plugin->duels[$id_duel]['teams']['BLUE'] === $player->getName()){
			$goals = $this->plugin->duels[$id_duel]['points']['BLUE'];
		}else{
			$goals = 0;
		}
		$array_1 = [
		    "{day/moth/year}",
			"{status}",
			"{mode}",
			"{pointsblue}",
			"{pointsred}",
			"{kills}",
			"{goals}"
		];
		$array_2 = [
		    date("d/m/Y"),
			"$status",
			"$mode",
			"$pointsblue",
			"$pointsred",
			"$kills",
			"$goals"
		];
        $player->sendTip(str_replace($array_1, $array_2, $scoreboard['text']));
	}
	
	public function getTip(Player $player, string $id_duel, $time){
		$status = "";
	    $statusOriginal = $this->plugin->duels[$id_duel]['status'];
		switch($statusOriginal){
			case Main::WAITING: 
			    $status = "§aWaiting..";			
			break;
			case Main::READY: 
				$status = "§aStarting in: ".$time;			
			break;
            case Main::PLAYING: 
                $status = "§aTime Up: ".$time;						
			break;
			case Main::RESTARTING: 
			    $status = "§5Restarting.."; 
			break;			
		}
		$scoreboard = $this->plugin->getConfig()->get("scoreboard");
		$mode = $this->plugin->duels[$id_duel]['mode'];
		$dailystreak = $this->getDailyStreak($player);
		$bestdailystreak = $this->getBestDailyStreak($player);
		$array_1 = [
		    "{day/moth/year}",
			"{status}",
			"{mode}",
			"{dailystreak}",
			"{bestdailystreak}"
		];
		$array_2 = [
		    date("d/m/Y"),
			"$status",
			"$mode",
			"$dailystreak",
			"$bestdailystreak"
		];
		$player->sendTip(str_replace($array_1, $array_2, $scoreboard['text']));
	}
}