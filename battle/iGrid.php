<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 30.03.2018
 * Time: 12:38
 */

interface iGrid
{
    public function __construct(int $x, int $y, array $ships);

    public function setX(int $x);
    public function setY(int $y);
    public function getX(): int;
    public function getY(): int;
    public function show(): void;
    public function placeShipsRandom();
    public function getAlphabet(): array;
    public function setAlphabet();
    public function getKeyByChar(string $char): string ;
    public function checkLimits(int $x, int $y): bool;
    public function getCellOfField(int $x, int $y): Cell;
    public function getRandomCellOfField(): Cell;
    public function checkShipsStatus(): bool;
    public function getShips(): array;
    public function getCoordUserStyle(int $x, int $y): string;

}