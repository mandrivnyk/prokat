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
    private $shotCoord;
    private $shotStatus;


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

    /**
     * @return mixed
     */
    public function getShotCoord(): string
    {
        if(is_null($this->shotCoord)) {
            return "";
        }
        return $this->shotCoord;
    }

    /**
     * @param mixed $shotCoord
     */
    public function setShotCoord(string $shotCoord): void
    {
        $this->shotCoord = $shotCoord;
    }

    /**
     * @return mixed
     */
    public function getShotStatus()
    {
        return $this->shotStatus;
    }

    /**
     * @param mixed $shotStatus
     */
    public function setShotStatus($shotStatus): void
    {
        $this->shotStatus = $shotStatus;
    }



    public function shotOut():void
    {
        $this->setShots($this->getShots()+1);
    }

    public function shotIn(int $x, int $y): void
    {
        $cell = $this->getGrid()->getCellOfField($x, $y);
        $this->setShotCoord($this->getGrid()->getCoordUserStyle($x+1, $y));
        $this->shot($cell);
    }

    public function shotInRandom(): void
    {
        $cell = $this->getGrid()->getRandomCellOfField();
        $this->setShotCoord($this->getGrid()->getCoordUserStyle($cell->getX()+1, $cell->getY()));
        $this->shot($cell);
    }

    public function shot(Cell $cell): void
    {
        if($cell->getFilling() == 1) {
            $cell->getStatus()->set(StatusCell::STATUS_HIT);
            $this->setShotStatus(StatusCell::STATUS_HIT);
            $cell->fillOutHistory();
        }
        else if($cell->getFilling() == 0) {
            $cell->getStatus()->set(StatusCell::STATUS_MISS);
            $this->setShotStatus(StatusCell::STATUS_MISS);
        }
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

    public function createShips(array $ships): void
    {
        $factoryShip = new FactoryShip();
        $shipsObj = array();
        foreach ($ships as $ship) {
            for($i=0; $i< $ship['num']; $i++ ){
                $shipsObj[] = $factoryShip->create($ship['type']);
            }
        }
        $this->setShips($shipsObj);
    }


}