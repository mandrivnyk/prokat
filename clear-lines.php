<?php
/**
 * Created by PhpStorm.
 * User: Easier
 * Date: 19.05.2015
 * Time: 10:21
 */





if($handle=opendir('cache/')):
    while(false!==($file=readdir($handle))):
        file('lines.txt', FILE_SKIP_EMPTY_LINES)
        //убираем лишние элементы
        //file_put_contents('/cache_new/'.$file,file($file, FILE_SKIP_EMPTY_LINES));
        $file = array_filter(array_map("trim", file($file)), "strlen");
        file_put_contents('cache_new/'.$file,$file);
        echo 'ok<br>';
        print_r($file);
       // if ($file!="." && $file!=".."):
          //  print "$file<br>"; // выводим содержимое
            // или print "$file\r\n";

    endwhile;
    closedir($handle);
endif;