<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 02.04.2018
 * Time: 14:41
 */

class Player implements iPlayer
{
    private $grid;
    private $ships;
    private $name;
    private $score;
    private $shots;


    /**
     * Player constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->setName($data['name']);
        $this->setScore(0);
        $this->setShots(0);
        $this->createShips($data['ships']);
        $this->setGrid(new GridBattleships($data['maxX'],$data['maxY'], $this->getShips()));

    }


    public function getShips(): array
    {
        return $this->ships;
    }

    /**
     * @return mixed
     */
    public function getGrid(): iGrid
    {
        return $this->grid;
    }

    /**
     * @param mixed $grid
     */
    public function setGrid(iGrid $grid): void
    {
        $this->grid = $grid;
    }

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * @param mixed $score
     */
    public function setScore($score): void
    {
        $this->score = $score;
    }

    /**
     * @return mixed
     */
    public function getShots(): int
    {
        return $this->shots;
    }

    /**
     * @param mixed $shots
     */
    public function setShots($shots): void
    {
        $this->shots = $shots;
    }



    public function setShips(array $ships): void
    {
        $this->ships = $ships;
    }

    public function createShips(array $shipsTypes): void
    {
        $factoryShip = new FactoryShip();
        $ships = array();
        foreach ($shipsTypes as $shipsType) {
            for($i=0; $i< $shipsType['sum']; $i++ ){
                $ships[] = $factoryShip->create($shipsType['type']);
            }
        }
        $this->setShips($ships);
    }


}