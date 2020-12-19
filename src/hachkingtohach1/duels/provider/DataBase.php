<?php

namespace hachkingtohach1\duels\provider;

interface DataBase{

    /**
     * @return string
     */
    public function getDatabaseName(): string;

    /**
     *
     */
    public function close(): void;

    /**
     *
     */
    public function reset(): void;
}