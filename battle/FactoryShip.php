<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 02.04.2018
 * Time: 16:49
 */

class FactoryShip
{
    public function create($type)
    {
        switch ($type) {
            case 'Battleship':
                return new Battleship();
                break;
            case 'Destroyers':
                return new Destroyers();
                break;
        }
    }
}