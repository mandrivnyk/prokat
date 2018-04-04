<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 30.03.2018
 * Time: 12:43
 */

class GridBattleships implements iGrid
{
    private $x;
    private $y;
    private $fieldArr;
    private $alphabet;
    private $ships;
    private $emptyPoints;


    /**
     * GridBattle constructor.
     * @param int $x
     * @param int $y
     * @param array $ships
     */
    public function __construct(int $x, int $y, array $ships)
    {
        $this->setAlphabet();
        $this->setX($x);
        $this->setY($y);
        $this->fieldArr = array();
        $this->createField();
        $this->setShips($ships);
        $this->genEmptyPoints();
    }



    /**
     * @return array
     */
    public function getFieldArr(): array
    {
        return $this->fieldArr;
    }

    /**
     * @param array $fieldArr
     */
    public function setFieldArr(array $fieldArr): void
    {
        $this->fieldArr = $fieldArr;
    }

    /**
     * @return mixed
     */
    public function getShips(): array
    {
        return $this->ships;
    }

    /**
     * @param mixed $ships
     */
    public function setShips(array $ships): void
    {
        $this->ships = $ships;
    }



    public function placeShipsRandom(): bool
    {
        foreach ($this->getShips() as $ship){
                if(!$ship instanceof Ship){
                    throw new Exception("Value is not Ship type");
                }

               $this->genEmptyPoints();

               if(!$this->checkPlaceShip($ship)){
                    return false;
               }
               $this->placeShip($ship);
            }
            return true;
    }

    /**
     * @return array
     */
    public function getEmptyPoints(): array
    {
        return $this->emptyPoints;
    }

    /**
     * @param mixed $emptyPoints
     */
    public function setEmptyPoints($emptyPoints): void
    {
        $this->emptyPoints = $emptyPoints;
    }


    public function genEmptyPoints(): void
    {
        $emptyPoints = array();
        for ($y = 0; $y < $this->getY(); $y++) {
            for ($x = 0; $x < $this->getX(); $x++) {
                $cell =  $this->getCellOfField($x, $y);
                if(!is_null($cell) && ($cell->getFilling() == 0)) {
                    foreach (Ship::getPositionsAvailable() as $position) {
                        $emptyPoints[] = array($x,$y, $position);
                    }
                }
            }
        }
        $this->setEmptyPoints($emptyPoints);
    }



    public function checkPlaceShip(Ship $ship): bool
    {
        if(empty($this->getEmptyPoints())){
            return false;
        }
        $emptyPoints = $this->getEmptyPoints();
        $randKey = array_rand($emptyPoints, 1);

        $ship->setStartPointX($emptyPoints[$randKey][0]);
        $ship->setStartPointY($emptyPoints[$randKey][1]);
        $ship->setPosition($emptyPoints[$randKey][2]);
        $ship->genCoordinatesOfShip();
        if(!$this->isAvailableToPlaceShip($ship->getCoords())){
            unset($emptyPoints[$randKey]);
            $this->setEmptyPoints($emptyPoints);
            $this->checkPlaceShip($ship);
        }
        return true;
    }



    public function getRandomCellOfField(): Cell
    {
        $randomX = rand(0, $this->getX()-1);
        $randomY = rand(0, $this->getY()-1);
        $field = $this->getFieldArr();
        if(!isset($field[$randomX][$randomY])) {
            throw new Exception("Element of array is out of range");
        }
        else {
            return $field[$randomX][$randomY];
        }
    }

    public function getCellOfField(int $x, int $y): Cell
    {
        $field = $this->getFieldArr();
        if(!isset($field[$x][$y])) {
            throw new Exception("Element of array is out of range");
        }
        else {
            return $field[$x][$y];
        }
    }


    public function placeShip(Ship $ship): void {

            foreach ($ship->getCoords() as $coord){
                $coordXY = explode(":", $coord);
                $cell = $this->getCellOfField($coordXY[0], $coordXY[1]);
                $cell->fill();
                $ship->setStatus(Ship::STATUS_LIVE);
            }
    }

    public function isAvailableToPlaceShip(array $coords): bool {
            foreach ($coords as $coord){
                $coord = explode(":", $coord);

                if($this->checkLimits($coord[0], $coord[1])) {
                    $cell = $this->getCellOfField($coord[0], $coord[1]);
                    if(!is_null($cell) && ($cell->getFilling() == 0)) {
                        continue;
                    }
                }
                return false;
            }
            return true;
    }

    public function checkShipsStatus(): bool
    {   $result  = 0;
        foreach ($this->getShips() as $ship) {
            $i = 0;
            foreach ($ship->getCoords() as $coord){
                $coord = explode(":", $coord);
                    $cell = $this->getCellOfField($coord[0], $coord[1]);
                    if(!is_null($cell) && ($cell->getFilling() == 2)) {
                        $ship->setStatus(Ship::STATUS_DAMAGED);
                        $i++;
                    }
            }
            if($i == count($ship->getCoords())) {
                $ship->setStatus(Ship::STATUS_DEAD);
                $result++;
            }
        }
        if($result == count($this->getShips())) {
            return false;
        }
        else {
            return true;
        }
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    /**
     * @param mixed $x
     */
    public function setX(int $x)
    {
        $this->x = $x;
    }

    /**
     * @param mixed $y
     */
    public function setY(int $y)
    {
        $this->y = $y;
    }

    public function createField(): void{
        $field = array();
        for ($x=0; $x < $this->getX(); $x++) {
            for ($y=0; $y< $this->getY(); $y++){
                $field[$x][$y] = new Cell($x,$y);
            }
        }
        $this->setFieldArr($field);
    }

    public function display(): void
    {
        $this->showLineX();

        for ($y=0; $y< $this->getY(); $y++){
            print_r($this->alphabet[$y]);
            for ($x=0; $x < $this->getX(); $x++){
                print_r(" ".$this->fieldArr[$x][$y]->getStatus()->get());
            }
            print_r("\n");
        }
    }

    public function show(): void
    {
        $this->showLineX();

        for ($y=0; $y< $this->getY(); $y++){
            print_r($this->alphabet[$y]);
            for ($x=0; $x < $this->getX(); $x++){
                if($this->fieldArr[$x][$y]->getFilling() == 0 ){
                    print_r("  ");
                }
                elseif($this->fieldArr[$x][$y]->getFilling() == 2 ) {
                    print_r(" x");
                }
                else {
                    print_r(" *");
                }
            }
            print_r("\n");
        }
    }

    /**
     * @return array
     */
    public function getAlphabet(): array
    {
        return $this->alphabet;
    }

    public function getKeyByChar(string $char): string
    {
        $key = array_search(trim($char), $this->getAlphabet());
        if(isset($key)){
            return $key;
        }
        return "";
    }

    public function getCoordUserStyle(int $x, int $y): string
    {
        $alphabet = $this->getAlphabet();
        return $alphabet[$y].$x;
    }


    public function checkLimits(int $x, int $y): bool
    {
        if($x < $this->getX() && $y < $this->getY()) {
            return true;
        }
        return false;
    }


    public function setAlphabet(): void{
        $this->alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    }

    public function showLineX(): void{
        print(" ");
        for ($x=0; $x < $this->getX(); $x++){
            print_r(" ");
            print_r($x+1);
        }
        print_r("\n");
    }
}