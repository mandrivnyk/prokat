<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 30.03.2018
 * Time: 14:58
 */

class Cell implements iCell
{
    private $status;
    private $filling;
    private $x;
    private $y;

    public  function __construct(int $x, int $y)
    {
        $this->setStatus(new StatusCell());
        $this->fillOut();
        $this->setX($x);
        $this->setY($y);
    }

    /**
     * @return StatusCell
     */
    public function getStatus(): StatusCell
    {
        return $this->status;
    }

    /**
     * @param StatusCell $status
     */
    public function setStatus(StatusCell $status)
    {
        $this->status = $status;
    }



    /**
     * @return mixed
     */
    public function getFilling(): int
    {
        return $this->filling;
    }


    public function fill(): void
    {
        $this->filling = 1;
    }

    public function fillOutHistory(): void
    {
        $this->filling = 2;
    }


    public function fillOut(): void
    {
        $this->filling = 0;
    }

    /**
     * @return mixed
     */
    public function getX(): int
    {
        return $this->x;
    }

    /**
     * @param mixed $x
     */
    public function setX(int $x)
    {
        $this->x = $x;
    }

    /**
     * @return mixed
     */
    public function getY(): int
    {
        return $this->y;
    }

    /**
     * @param mixed $y
     */
    public function setY(int $y)
    {
        $this->y = $y;
    }
}