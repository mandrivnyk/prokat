<?php

        spl_autoload_register(function ($class_name) {
            include $class_name . '.php';
        });


        set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
            if (!error_reporting()) {
                return;
            }

            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
        });


        try {
            $game = new GameBattleships();
            $game->start();

        } catch (Exception $e) {
            print_r('Message: ' .$e->getMessage());
        }


