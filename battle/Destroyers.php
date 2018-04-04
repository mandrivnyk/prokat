<?php
/**
 * Created by PhpStorm.
 * User: W520
 * Date: 30.03.2018
 * Time: 14:03
 */

class Destroyers extends Ship
{

    /**
     * Destroyers constructor.
     */
    public function __construct()
    {
        $this->setSize(4);
        $this->setType("Destroyers");
    }
}