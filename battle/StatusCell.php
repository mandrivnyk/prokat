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
    public function __construct()
    {
        $this->set("noShot");
    }

    public function set(string $status): void{
        switch ($status) {
            case "noShot":
                $this->status = ".";
                break;
            case "miss":
                $this->status = "-";
            case "hit":
                $this->status = "x";
        }
    }

    public function get(): string {
        return $this->status;
    }
}