<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 30.03.2018
 * Time: 13:57
 */

class Battleship extends Ship
{
    /**
     * Battleship constructor.
     */
    public function __construct()
    {
        $this->setSize(5);
        $this->setType("Battleship");
    }
}