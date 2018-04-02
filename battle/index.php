<?php
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});


set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
    // Не выбрасываем исключение если ошибка подавлена с
    // помощью оператора @
    if (!error_reporting()) {
        return;
    }

    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});


try {
    $game = new GameBattleships();
    $game->show();
    $data['position'] = "right";
    $data['startPointX'] = 6;
    $data['startPointY'] = 6;

    $battleship = new Battleship($data);

    echo  '<pre>' ;
    print_r($battleship->getCoords());
    echo '</pre>';

} catch (Exception $e) {
    echo 'Message: ' .$e->getMessage();
}


