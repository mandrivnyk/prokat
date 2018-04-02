<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 30.03.2018
 * Time: 14:09
 */



class GameBattleships implements iGame
{
    private $grid;

    public function __construct()
    {
        //$this->grid = new GridBattleships($maxX, $maxY);

        $data['name'] = "USER";
        $data['ships'][] = array('Battleship', 1);
        $data['ships'][] = array('Destroyers', 2);
        $data['maxX'] = 10;
        $data['maxY'] = 10;
        $player = new Player($data);
    }



    public function show(): void
    {
        $this->grid->show();
    }
}