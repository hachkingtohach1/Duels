<?php

declare(strict_types=1);

namespace hachkingtohach1\duels\task;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\scheduler\Task;

use hachkingtohach1\duels\Main;
use hachkingtohach1\duels\utils\Scoreboard;
use hachkingtohach1\duels\duels\Systems;
use hachkingtohach1\duels\utils\BlockUtils;

class ArenaScheduler extends Task {

    /** @var Systems $plugin */
    protected $plugin;
	
	/** @var bool $forceStart */
    public $forceStart = false;
	
	/** @var array $timeStart */
	public $timeStart = [];
	
	/** @var array $timeUp */
	public $timeUp = [];
	
	/** @var array $timeRestart */
	public $timeRestart = [];

    public function __construct(Systems $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick) {
		$systems = $this->plugin;
        foreach($systems->plugin->duels as $duel){
			switch($duel['status']){
				case Main::WAITING:
				    $idA = $duel['id'];
                    $this->timeUp[$duel['id']] = 0;
                    $this->timeStart[$duel['id']] = 10;
				    $this->timeRestart[$duel['id']] = 5;				
                    foreach($systems->plugin->getServer()->getOnlinePlayers() as $player){
					    if(isset($systems->plugin->inGame[$player->getName()])){
							if($duel['mode'] === "bridge" and $systems->plugin->inGame[$player->getName()]["ID_DUEL"] === $duel['id']){
								$systems->getTipBridge($player, $duel['id'], 0);
							}
							if($duel['mode'] !== "bridge" and $systems->plugin->inGame[$player->getName()]["ID_DUEL"] === $duel['id']){
								$systems->getTip($player, $duel['id'], 0);
							}
						}
					}						
				break;
				case Main::READY:
				    if(count($systems->plugin->duels[$duel['id']]['players']) >= 2 or $this->forceStart){
				        if(!isset($this->timeStart[$duel['id']])){
							$this->timeStart[$duel['id']] = 10;
							break;
						}						
						if($this->timeStart[$duel['id']] === 0){
						    $this->plugin->startDuel($duel['id']);	
                            foreach($systems->plugin->duels[$duel['id']]['players'] as $player){
							    $DUEL = $systems->plugin->inGame[$player->getName()]['DUEL'];	
								$systems->getKit($DUEL, $player); // GET KIT
			                    BlockUtils::trapPlayerInBox($player);
							}								
					    }					
					    $timeStart = $this->timeStart[$duel['id']];
					    foreach($systems->plugin->getServer()->getOnlinePlayers() as $player){
					        if(isset($systems->plugin->inGame[$player->getName()])){
							    if($duel['mode'] === "bridge" and $systems->plugin->inGame[$player->getName()]["ID_DUEL"] === $duel['id']){
							        $systems->getTipBridge($player, $duel['id'], $timeStart);
								}
							    if($duel['mode'] !== "bridge" and $systems->plugin->inGame[$player->getName()]["ID_DUEL"] === $duel['id']){
							        $systems->getTip($player, $duel['id'], $timeStart);
									
								}
							}
						}	
                        $this->timeStart[$duel['id']]--;						
					}else{
						$systems->plugin->duels['status'] = Main::WAITING;
						$systems->plugin->duels[$duel['id']]['starting'] = 10;
					}
				break;
				case Main::PLAYING:
				    if(!isset($this->timeUp[$duel['id']])){
						$this->timeUp[$duel['id']] = 0;
						break;
					}			    				    
					$timeUp = $this->timeUp[$duel['id']];
					foreach($systems->plugin->getServer()->getOnlinePlayers() as $player){
					    if(isset($systems->plugin->inGame[$player->getName()])){
							if($duel['mode'] === "bridge" and $systems->plugin->inGame[$player->getName()]["ID_DUEL"] === $duel['id']){
							    $systems->getTipBridge($player, $duel['id'], $timeUp);
							}
							if($duel['mode'] !== "bridge" and $systems->plugin->inGame[$player->getName()]["ID_DUEL"] === $duel['id']){
							    $systems->getTip($player, $duel['id'], $timeUp);
							}
						}
					}
					if($systems->checkEnd($duel['id']) === true) {						
						if($systems->plugin->duels[$duel['id']]['mode'] === "skywars"){
							unset($systems->plugin->chestRefill[$duel['id']]);
						}
						$systems->restartDuel($duel['id']);
					}	
                    $this->timeUp[$duel['id']]++;					
				break;
				case Main::RESTARTING:                    				
				break;
            }
		}			
	}
}