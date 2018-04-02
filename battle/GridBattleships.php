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

        for ($x=0; $x < $this->getX(); $x++) {
            for ($y=0; $y< $this->getY(); $y++){
                $this->fieldArr[$x][$y] = new Cell($x,$y);
            }
        }
    }

    public function show(): void{

        $this->showLineX();

        for ($y=0; $y< $this->getY(); $y++){
            print_r($this->alphabet[$y]);
            for ($x=0; $x < $this->getX(); $x++){
                print_r(" ".$this->fieldArr[$x][$y]->getStatus()->get());
            }
            print_r("\n");
        }
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