<?php

declare(strict_types=1);

namespace hachkingtohach1\duels\duels;

use pocketmine\Player;

class ChestItems {

    // ISLAND CHEST
    public $blocks = [
	    [5, 0, 32, false, []]       	
	];
	
	public $weapons = [
	    [276, 0, 1, false, []],
		[346, 0, 1, false, []],
		[259, 0, 1, false, []]		
	];
	
	public $armors = [
	    [310, 0, 1, false, []],
		[311, 0, 1, false, []]
	];
	
	public $potions = [
	    [438, 28, 1, false, []],
		[438, 12, 1, false, []],
		[384, 0, 1, false, []]
	];
	
	public $others = [
	    [332, 0, 8, false, []],
		[344, 0, 8, false, []]
	];
	
	public $specials = [
	    [322, 0, 1, false, []]
	];
	
	// MID ISLAND CHEST
	public $mid = [
	    [17, 0, 32, false, []],
		[261, 0, 1, true, [[19, 2]]],
		[276, 0, 1, true, [[13, 1]]],
		[310, 0, 1, false, [[1, 1]]],
		[311, 0, 1, false, [[0, 1]]],
		[322, 0, 2, false, []]
	];	
}