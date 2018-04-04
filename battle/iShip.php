<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 30.03.2018
 * Time: 13:56
 */

interface iShip
{
    public function __construct();
    public function getType(): string;
    public function setType(string $type): void;
    public static function getPositionsAvailable();
    public function getStatus();
    public function setStatus($status): void;
    public function getCoords(): array;
    public function setCoords($coords = array()): void;
    public function genCoordinatesOfShip(): void;
    public function getSize(): int;
    public function setSize(int $size);
    public function getPosition(): string;
    public function setPosition(string $position);
    public function getRandomPosition(): string;
    public function getStartPointX(): int;
    public function setStartPointX(int $startPointX);
    public function getStartPointY(): int;
    public function setStartPointY(int $startPointY);
}