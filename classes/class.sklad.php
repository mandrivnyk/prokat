<?php
class sklad
{

    private $dir;
    private $data;
    const IN_SHOP = 'в магазине';
    const IN_SUPPLIER = 'на складе';


    public  function __construct(){
        $this->_setDir($_SERVER['DOCUMENT_ROOT'].'/sklad/');
    }

    public  function getSkladName($number){
        switch ($number) {
            case 1:
                return self::IN_SHOP;
                break;
            case 2:
                return  self::IN_SUPPLIER;
                break;
            }
        }



    public function utf8_fopen_read($fileName) {
        $fc = file_get_contents($fileName);
        $handle=fopen("php://memory", "rw");
        fwrite($handle, $fc);
        fseek($handle, 0);
        return $handle;
    }

    public function checkIsFile($productCode){
        $pathToFile = $this->_getDir().$productCode.".json";
        if(is_file($pathToFile))
            return true;
        return false;
    }


    /**
     * @param $productCode
     * @return array $variants
     */
    public function getVariantsFromFile($productCode)
    {
        $pathToFile = $this->_getDir().$productCode.".json";
        header('Content-Type: text/html; charset=windows-1251');
        $variants = array();
        if(is_file($pathToFile))
        {
            $fp = $this->utf8_fopen_read($pathToFile);

            if ($fp)
            {
                $i=0;
                while (!feof($fp))
                {
                    $variants[$i++]  = fgets($fp, 999);
                }
                $variants =   implode($variants);
                $variants = json_decode($variants, true) ;
            }
        }
        return $variants;
    }

    public function setVariantsToFile($data) {

        $pathToFile = $this->_getDir().$data['productCode'].".json";

        if( !file_exists($pathToFile))
        {

            $content = array();

            $content['productCode'] = $data['productCode'];
            $content['quantity'] = 1;
            $content['price'] = 0;
            $content['barCode'] = "";
            $content['producer'] = "";
            $content['sklad'] = 0;

            if(count($data['sizes']) > 0 && count($data['colors']) > 0) {
                $united = $this->makeParsSizeColors($data['sizes'], $data['colors']);
                $content = $this->uniteArrays($united, $content);
            }
            else {
                if(count($data['sizes']) > 0) {
                    $content = $this->uniteArrays($data['sizes'], $content,'size');
                }

                if(count($data['colors']) > 0){
                    $content = $this->uniteArrays($data['colors'], $content,'color');
                }
            }
            $this->writeFile($this->_getDir().$data['productCode'].'.json', json_encode($content, JSON_UNESCAPED_UNICODE));
         }
    }

        function makeParsSizeColors($sizes, $colors){
            $result = array();
            $i=0;
            foreach ($sizes as $key=>$size) {
                foreach ($colors as $color) {
                    $result[$i]['size'] = $size;
                    $result[$i++]['color'] = $color;
                }
            }
            return $result;

        }


        function uniteArrays($arr1, $arr2, $name = ''){
            $result = array();
            $i=0;
            foreach ($arr1 as $key=>$value) {
                if(!is_array($value)){
                    $value = array($name => $value);
                }

                $result[$i++] = array_merge($value, $arr2);
            }
            return $result;
        }




    function writeFile($filename,$content) {
        $f=fopen($filename,"w");
        # Now UTF-8 - Add byte order mark
        //fwrite($f, pack("CCC",0xef,0xbb,0xbf));
        fwrite($f,$content);
        fclose($f);
    }

    function _convertVariantsTo1251(&$value, $key){

        //if(is_string($value) && !is_array($value) && mb_detect_encoding($value) == 'UTF-8'){
        if(is_string($value) && !is_array($value)){
            $value = iconv("UTF-8","windows-1251",  $value);
        }
    }
    function _convertStringTo1251($value){
        if(is_string($value) && !is_array($value)){
            $value = iconv("UTF-8","windows-1251",  $value);
        }
        return $value;
    }



    /**
     * @param $productCode
     * @return String $variant
     */
    public function getVariantStringFromFile($productCode, $variantNum)
    {
        $result = "";
        $pathToFile = $this->_getDir().$productCode.".json";
        //header('Content-Type: text/html; charset=windows-1251');
        $variants = array();
        if(is_file($pathToFile))
        {
            $fp = $this->utf8_fopen_read($pathToFile);

            if ($fp)
            {
                $i=0;
                while (!feof($fp))
                {
                    $variants[$i++]  = fgets($fp, 999);
                }
                $variants =   implode($variants);
                $variants = json_decode($variants, true) ;
            }

            array_walk($variants[$variantNum], array($this, '_convertVariantsTo1251'));
            if(!isset($variants[$variantNum]['size'])) {
                $sizeString = '';
            }
            else {
                $sizeString = ', размер: '.$variants[$variantNum]['size'];
            }

            if(!isset($variants[$variantNum]['color'])) {
                $colorString = '';
            }
            else {
                $colorString = ', цвет: '.$variants[$variantNum]['color'];
            }

            $result = 'артикул: '.$variants[$variantNum]['productCode'].$sizeString.$colorString;
        }
        return $result;
    }

    public function createSkladInSelectTag($variants, $i, $sizes, $colors){
        $com_str = '';

        if(count($variants) > 0) {
            $com_str .= "<select id='select_variants_".$i."' name='select_variants_".$i."'><option value=''>Выберите варианты</option>";
            $optionArr = array();
            foreach ($variants as $key=>$variant) {
                array_walk($variant, array($this, '_convertVariantsTo1251'));
                $optionString = "";
                if(isset($variant['size']) && $variant['size'] !== '') {
                   // $com_str .=  "размер: ".$variant['size'];
                    $optionString .=  "".$variant['size'];
                } else if(count($sizes) > 0){

                    foreach ($sizes as $size) {
                        if(!empty($size)){
                            $size =  $this->_convertStringTo1251($size);
                            $optionString .=  "".$size;
                        }
                    }
                }

                if(isset($variant['color']) && $variant['color'] !== '') {
                    $optionString .=  " цвет: ".$variant['color'];
                } else if(count($colors) > 0){

                    foreach ($colors as $color) {
                        if(!empty($color)){
                            $color =  $this->_convertStringTo1251($color);
                            $optionString .=  " цвет: ".$color;
                        }
                    }
                }
                $optionString .=  "";
                $optionArr[] = trim($optionString);
            }

            $options = array_unique($optionArr);

            foreach ($options as $key=>$option) {
                $com_str .= "<option value='".$key."'>".$option."</option>";
            }
            $com_str .= "</select><br>";
        }
        return $com_str;
    }

    public function createSkladInList($variants, $sizes, $colors){

        $com_str = '';
        $variantsLength = count($variants);
        if($variantsLength > 0) {

            $com_str .= '<b>';
            $com_str .= '<table class="datatableSkladList">';
//            $com_str .='<tr><td>Артикул</td>';
//            if(!empty($sizes)){
//                $com_str .='<td>Размер</td>';
//            }
//            if(!empty($colors)){
//                $com_str .='<td>Цвет</td>';
//            }
//
//            $com_str .='<td> </td>';
//            $com_str .= '</tr>';
            $i = 0;
            foreach ($variants as $variant) {

                array_walk($variant, array($this, '_convertVariantsTo1251'));
                $com_str .= '<tr>';
                if($i == 0) {
                    $com_str .= '<td rowspan='.$variantsLength.' valign="top">'.$variant['productCode'].'</td>';
                }

                if(isset($variant['size']) && $variant['size'] !== '') {
                    $com_str .=  '<td>'.$variant['size'].'</td>';
                }
                else if(count($sizes) > 0){

                    $com_str .=  '<td>';
                    foreach ($sizes as $size) {
                        if(!empty($size)){
                            $size =  $this->_convertStringTo1251($size);
                            $com_str .= $size;
                        }
                    }
                    $com_str .=  '</td>';
                }

                if(isset($variant['color']) && $variant['color'] !== '') {
                    $com_str .=  '<td>'.$variant['color'].'</td>';
                } else if(count($colors) > 0){

                    $com_str .=  '<td>';
                    foreach ($colors as $color) {
                        //print_r(mb_detect_encoding($color));
                        if(!empty($color)){
                            $color =  $this->_convertStringTo1251($color);
                            $com_str .= $color;
                        }
                    }
                    $com_str .=  '</td>';
                }


                if(isset($variant['sklad'])) {
                    $com_str .=  '<td>';
                    $com_str .= $this->getSkladName($variant['sklad']);
                    $com_str .=  '</td>';
                }
                $com_str .= '</tr>';
                $i++;
            }
        }
        return $com_str;
    }

    public function _setDir($dir)
    {
        $this->dir = $dir;
    }


    public function _getDir()
    {
        return $this->dir;
    }
}