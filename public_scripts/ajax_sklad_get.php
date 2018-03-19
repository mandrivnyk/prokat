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



function convert(&$value, $key){
    $value = iconv("UTF-8","windows-1251",  $value);
}

require_once('../classes/class.sklad.php');

if(isset($productCode)) {

    $sklad = new sklad();

//    $skipWords = ['б\у','б/у', 'Б/У', 'Б\У'];
//    $res = in_array($productCode, $skipWords);
//    print_r($productCode);
//    print_r($skipWords);
//    print_r($res);
//    print_r("-------------");
//exit();

//    print_r($productCode);
    if ((strpos($productCode, '/') !== false)){
        $productCodes = explode('/', $productCode);
    } else {
        $productCodes[] = $productCode;
    }
//    print_r($productCodes);
//exit();
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
                    $productCode1251 = iconv("UTF-8","windows-1251",trim($productCode));
                    $result .= '<br><b>Артикул: ' . $productCode1251 . '</b><br>';
                    $result .= $sklad->createSkladInSelectTag($sklad->getVariantsFromFile($productCode), $key);
                }
            }
            echo $result.'<br>';
            exit();
        }

        if($forCharacterList >0) {
            $result .= '<b>';
            $result .= '<table class="datatableSkladList"><tr><td>Артикул</td>';
            if(!empty($sizes)){
                $result .='<td>Размер</td>';
            }
            if(!empty($colors)){
                $result .='<td>Цвет</td>';
            }

            $result .= '</tr>';

            foreach ($productCodes as $productCode) {
                $result .= $sklad->createSkladInList($sklad->getVariantsFromFile($productCode));
            }
            $result .= '</table></b>';
            echo $result.'<br>';
            exit();
        }
}





