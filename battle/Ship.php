<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 30.03.2018
 * Time: 14:00
 */

abstract class  Ship
{
    private $size;
    private $position;
    private $startPointX;
    private $startPointY;
    private $coords;

    /**
     * Ship constructor.
     */
    public function __construct()
    {
//       $this->setSize($data['size']);
//       $this->setPosition($data['position']);
//       $this->setStartPointX($data['startPointX']);
//       $this->setStartPointY($data['startPointY']);
//       $this->setCoords();
//       $this->genCoordinatesOfShip();
    }

    /**
     * @return array
     */
    public function getCoords(): array
    {
        return $this->coords;
    }

    /**
     * @param array $coords
     */
    public function setCoords($coords = array()): void
    {
        $this->coords = $coords;
    }



    public function genCoordinatesOfShip(): void{
        $coords = array();
        switch ($this->getPosition()){
            case "up":
                for ($y = $this->getStartPointY(); $y > ($this->getStartPointY()-$this->getSize()); $y-- ) {
                    $coords[] = $this->getStartPointX().":".$y;
                }
                $this->setCoords($coords);
                break;
            case "down":
                for ($y = $this->getStartPointY(); $y < ($this->getStartPointY()+$this->getSize()); $y++ ) {
                    $coords[] = $this->getStartPointX().":".$y;
                }
                $this->setCoords($coords);
                break;
            case "left":
                for ($x = $this->getStartPointX(); $x > ($this->getStartPointX()-$this->getSize()); $x-- ) {
                    $coords[] = $x.":".$this->getStartPointY();
                }
                $this->setCoords($coords);
                break;
            case "right":
                for ($x = $this->getStartPointX(); $x < ($this->getStartPointX()+$this->getSize()); $x++ ) {
                    $coords[] = $x.":".$this->getStartPointY();
                }
                $this->setCoords($coords);
                break;
        }
    }

    /**
     * @return mixed
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize(int $size)
    {
        $this->size = $size;
    }

    /**
     * @return mixed
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition(string $position)
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getStartPointX(): int
    {
        return $this->startPointX;
    }

    /**
     * @param mixed $startPointX
     */
    public function setStartPointX(int $startPointX)
    {
        $this->startPointX = $startPointX;
    }

    /**
     * @return mixed
     */
    public function getStartPointY(): int
    {
        return $this->startPointY;
    }

    /**
     * @param mixed $startPointY
     */
    public function setStartPointY(int $startPointY)
    {
        $this->startPointY = $startPointY;
    }





}