<?php

declare(strict_types = 1);

namespace hachkingtohach1\duels\utils;

use hachkingtohach1\duels\Main;

class SQL_utils {
	
    private $object;
   
    public function __construct(array $object = []){
        $this->object = $object;
    }
   
    public function getAll(): array{
        return $this->object;
    }
   
    public function setAll(array $data): void{
        $this->object = $data;
    }
   
    public function save(): void{
        Main::getInstance()->getDatabase()->saveAll();
    } 
}