<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 30.03.2018
 * Time: 14:58
 */

interface iCell
{
    public  function __construct(int $x, int $y);
    public function getStatus(): StatusCell;
    public function setStatus(StatusCell $status);
    public function getFilling(): int;
    public function fill(): void;
    public function fillOutHistory(): void;
    public function fillOut(): void;
    public function getX(): int;
    public function setX(int $x);
    public function getY(): int;
    public function setY(int $y);
}