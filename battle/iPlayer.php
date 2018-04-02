<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 02.04.2018
 * Time: 14:40
 */

interface iPlayer
{
    public function __construct(array $data);

    public function createShips(array $shipsTypes): void;
    public function getShips(): array;
    public function setShips(array $shipsObj): void;

}