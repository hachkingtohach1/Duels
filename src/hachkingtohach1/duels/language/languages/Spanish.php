<?php

namespace hachkingtohach1\duels\language\languages;

class Spanish{
	
	public static $translates = array(
	    // Join, leave and started
	    "JOINED" => "§aTe has unido al juego!",
		"LEAVE" => "",
		"STARTED" => "§a¡Empezar!",
		"FULL" => "§c¡La arena tiene una cantidad limitada!",
		
		// Broadcast 
		"RED_WON" => "§l§cRED gana!",
		"BLUE_WON" => "§l§bBLUE gana!",
        "DEFEATED" => "§f{arg1} §aderrotado §f{arg2} §aen el duelo: §c{arg3}",
		
		// Messages
		"NOT_INGAME" => "§c¡No estás en el juego!",
		"FELL_VOID" => "{arg1} §7cayendo por el pozo.",
		"FELL_BY" => "{arg1} §7cae a la fosa por {arg2}"
	);
}