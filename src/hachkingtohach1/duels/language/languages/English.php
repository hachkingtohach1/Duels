<?php

namespace hachkingtohach1\duels\language\languages;

class English{
	
	public static $translates = array(
	    // Join, leave and started
	    "JOINED" => "§aYou has joined the game!",
		"LEAVE" => "",
		"STARTED" => "§aStarted!",
		"FULL" => "§cArena is full!",
		
		// Broadcast 
		"RED_WON" => "§l§cRED WON!",
		"BLUE_WON" => "§l§bBLUE WON!",
        "DEFEATED" => "§f{arg1} §adefeated §f{arg2} §ain duel: §c{arg3}",
		
		// Messages
		"NOT_INGAME" => "§cYou aren't in-game!",
		"FELL_VOID" => "{arg1} §7fell into the void.",
		"FELL_BY" => "{arg1} §7was knocked into the void by {arg2}"
	);
}