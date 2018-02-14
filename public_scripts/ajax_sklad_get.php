<?php
$productCode = 0;
$forSelect = 0;
$forCharacterList = 0;

if(isset($_POST['productCode']))
//    $productCode = iconv("UTF-8","windows-1251",trim($_POST['productCode']));
    $productCode = trim($_POST['productCode']);

if(isset($_POST['forSelect']))
//    $forSelect = iconv("UTF-8","windows-1251",$_POST['forSelect']);
    $forSelect = $_POST['forSelect'];

if(isset($_POST['forCharacterList']))
//    $forCharacterList = iconv("UTF-8","windows-1251",$_POST['forCharacterList']);
    $forCharacterList = $_POST['forCharacterList'];

$sizes = array();
if(isset($_POST['size_arr'])){
    $sizes = $_POST['size_arr'];
    //array_walk($sizes, 'convert');
}

//print_r($sizes);
$colors = array();
if(isset($_POST['colors_arr'])) {
    $colors = $_POST['colors_arr'];
//    array_walk($colors, 'convert');
}

//print_r($colors);

function convert(&$value, $key){
    $value = iconv("UTF-8","windows-1251",  $value);
}

require_once('../classes/class.sklad.php');

if(isset($productCode)) {

    $sklad = new sklad();

    if (strpos($productCode, '/') !== false) {
        $productCodes = explode('/', $productCode);
    } else {
        $productCodes[] = $productCode;
    }

    $result = '';

        if($forSelect >0) {

            foreach ($productCodes as $key=>$productCode) {

                if(!$sklad->checkIsFile($productCode) && (!empty($sizes) || !empty($colors))) {

                    $data = array(
                        'sizes' => $sizes,
                        'colors' => $colors,
                        'productCode' => $productCode
                    );
                    $sklad->setVariantsToFile($data);
                }

                if($sklad->checkIsFile($productCode)) {
                    $result .= '<br><b>Артикул: ' . $productCode . '</b>';
                    $result .= $sklad->createSkladInSelectTag($sklad->getVariantsFromFile($productCode), 1);
                }
            }
            echo $result.'<br>';
            exit();
        }

        if($forCharacterList >0) {
            $result .= '<b>';
            //$result .= '<table class="datatableSkladList"><tr><td>Артикул</td><td>Размер</td><td>Цвет</td></tr>';
            $result .= '<table class="datatableSkladList"><tr><td>Размер</td><td>Цвет</td></tr>';
            foreach ($productCodes as $productCode) {
                $result .= $sklad->createSkladInList($sklad->getVariantsFromFile($productCode));
            }
            $result .= '</table></b>';
            echo $result.'<br>';
            exit();
        }
}





