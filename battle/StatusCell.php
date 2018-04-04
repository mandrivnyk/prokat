<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 30.03.2018
 * Time: 15:08
 */

class StatusCell
{
    private $status;
    const STATUS_NO_SHOT = "noShot";
    const STATUS_MISS = "miss";
    const STATUS_HIT = "hit";

    public function __construct()
    {
        $this->set(self::STATUS_NO_SHOT);
    }

    public function set(string $status): void{
        switch ($status) {
            case self::STATUS_NO_SHOT:
                $this->status = ".";
                break;
            case self::STATUS_MISS:
                $this->status = "-";
                break;
            case self::STATUS_HIT:
                $this->status = "x";
        }
    }

    public function get(): string {
        return $this->status;
    }
}