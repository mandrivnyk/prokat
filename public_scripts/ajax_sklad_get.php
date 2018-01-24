<?php
$productCode = 0;
$forSelect = 0;
$forCharacterList = 0;

if(isset($_GET['productCode']))
    $productCode = iconv("UTF-8","windows-1251",trim($_GET['productCode']));

if(isset($_GET['forSelect']))
    $forSelect = iconv("UTF-8","windows-1251",$_GET['forSelect']);

if(isset($_GET['forCharacterList']))
    $forCharacterList = iconv("UTF-8","windows-1251",$_GET['forCharacterList']);



require_once('../classes/class.sklad.php');


if($productCode >0) {
    $sklad = new sklad();

    if (strpos($productCode, '/') !== false) {
        $productCodes = explode('/', $productCode);
    } else {
        $productCodes[] = $productCode;
    }

    $result = '';

        if($forSelect >0) {
            foreach ($productCodes as $key=>$productCode) {
                $result .= '<br><b>Артикул: ' . $productCode . '</b>';
                $result .= $sklad->createSkladInSelectTag($sklad->getVariantsFromFile($productCode), $key);
            }
            echo $result.'<br>';
            exit();
        }

        if($forCharacterList >0) {
            $result .= '<b>';
            $result .= '<table class="datatableSkladList"><tr><td>Артикул</td><td>Размер</td><td>Цвет</td></tr>';
            foreach ($productCodes as $productCode) {
                $result .= $sklad->createSkladInList($sklad->getVariantsFromFile($productCode));
            }
            $result .= '</table></b>';
            echo $result.'<br>';
            exit();
        }
}



