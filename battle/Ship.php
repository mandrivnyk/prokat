<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 30.03.2018
 * Time: 14:00
 */

abstract class  Ship implements iShip
{
    const   POSITIONS_AVAILABLE = array("horizontal", "vertical");
    const   STATUS_LIVE = "live";
    const   STATUS_DAMAGED = "damaged";
    const   STATUS_DEAD = "sunk";
    private $size;
    private $position;
    private $status;
    private $startPointX;
    private $startPointY;
    private $coords;
    private $type;


    /**
     * Ship constructor.
     */
    public function __construct()
    {

    }

    /**
     * @return mixed
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }


    /**
     * @return array
     */
    public static function getPositionsAvailable()
    {
        return self::POSITIONS_AVAILABLE;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
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



    public function genCoordinatesOfShip(): void
    {
        $coords = array();
        if(empty($this->getPosition()) || is_null($this->getStartPointX()) || is_null($this->getStartPointY())){
            throw new Exception("The data of ship is not set");
        }

        switch ($this->getPosition()){
            case "vertical":
                for ($y = $this->getStartPointY(); $y < ($this->getStartPointY()+$this->getSize()); $y++ ) {
                    $coords[] = $this->getStartPointX().":".$y;
                }
                $this->setCoords($coords);
                break;
            case "horizontal":
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


    public function setPosition(string $position)
    {
        if(!in_array($position, self::getPositionsAvailable())){
            throw new Exception("This position is not exist");
        }
        $this->position = $position;
    }

    public function getRandomPosition(): string
    {
        return array_rand(self::POSITIONS_AVAILABLE(), 1);
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