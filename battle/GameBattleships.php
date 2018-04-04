<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 30.03.2018
 * Time: 14:09
 */



class GameBattleships implements iGame
{

    private $user;
    private $computer;
    private $inputX;
    private $inputY;

    public function start(): void
    {
        $data['name'] = "USER";
        $data['ships'][] = array('type' => 'Battleship', 'num' => 1);
        $data['ships'][] = array('type' => 'Destroyers', 'num' => 2);
        $data['maxX'] = 10;
        $data['maxY'] = 10;

        $this->setUser(new Player($data));
        $data['name'] = "COMPUTER";
        $this->setComputer(new Player($data));
        $this->getUser()->getGrid()->placeShipsRandom();
        $this->getComputer()->getGrid()->placeShipsRandom();

        do {
            $this->display();
            print_r("Enter coordinates (row, col), e.g. A5 = ");
            $handle = fopen ("php://stdin","r");
            $input = fgets($handle);
            if(!$this->checkInput($input))
            {
                print_r("Input data is incorrect, try again \n");
                continue;
            }


            $this->getUser()->shotOut();
            $this->getComputer()->shotIn($this->getInputX(), $this->getInputY());
            $this->getComputer()->shotOut();
            $this->getUser()->shotInRandom();
            $userStatus =  $this->getUser()->getGrid()->checkShipsStatus();
            if(!$userStatus) {
                $this->display();
                print_r("Sorry, but you lost. Try again :) \n");
            }
            $computerStatus = $this->getComputer()->getGrid()->checkShipsStatus();
            if(!$computerStatus) {
                $this->display();
                print_r("Well done! You completed the game in ". $this->getUser()->getShots()." shots \n");
            }
        } while($userStatus && $computerStatus);

    }


    public function display(): void
    {
        $this->getUser()->getGrid()->show();

        print_r("______________________ \n");
        print_r($this->getUser()->getName()." \n");

        $i = 1;
        foreach ($this->getUser()->getGrid()->getShips() as $ship) {
            print_r("Ship ".$i++." Status:".$ship->getStatus()."\n");
        }

        print_r("Shots: ".$this->getUser()->getShots()." Last:".$this->getUser()->getShotCoord()." Status:".$this->getUser()->getShotStatus()."\n");
        $this->getUser()->getGrid()->display();
        print_r("______________________ \n");
        print_r($this->getComputer()->getName()." \n");

        $i = 1;
        foreach ($this->getComputer()->getGrid()->getShips() as $ship) {
            print_r("Ship ".$i++." Status:".$ship->getStatus()."\n");
        }

        print_r("Shots: ".$this->getComputer()->getShots()." Last:".$this->getComputer()->getShotCoord()." Status:".$this->getComputer()->getShotStatus()."\n");
        $this->getComputer()->getGrid()->display();
    }

    public function checkInput($input): bool
    {
        $result = preg_match("/^[A-J][0-9]{1,2}$/",$input);
        if($result)
        {
            $y = substr($input, 0, 1);
            $x = (int) substr($input, 1) - 1;
            if($x < $this->getUser()->getGrid()->getX()){
                $y = $this->getUser()->getGrid()->getKeyByChar($y);
                $this->setInputX((int) $x);
                $this->setInputY((int) $y);
                return true;
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getInputX()
    {
        return $this->inputX;
    }

    /**
     * @param mixed $inputX
     */
    public function setInputX($inputX): void
    {
        $this->inputX = $inputX;
    }

    /**
     * @return mixed
     */
    public function getInputY()
    {
        return $this->inputY;
    }

    /**
     * @param mixed $inputY
     */
    public function setInputY($inputY): void
    {
        $this->inputY = $inputY;
    }

    /**
     * @return mixed
     */
    public function getUser(): Player
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser(Player $user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getComputer(): Player
    {
        return $this->computer;
    }

    /**
     * @param mixed $computer
     */
    public function setComputer(Player $computer): void
    {
        $this->computer = $computer;
    }







}