<?php
class sklad
{

    private $dir;
    private $data;


    public  function __construct(){
        $this->_setDir($_SERVER['DOCUMENT_ROOT'].'/sklad/');
    }

//
//    public function getVariants($productCode)
//    {
//        if($productCode >0) {
//            $variants = $this->getVariantsFromFile($productCode);
//        }
//    }

//    public function _convertVariantsTo1251($variant)
//    {
//       // print_r($variant) ;
//        $variant['size'] = iconv("UTF-8","windows-1251",$variant['size']);
//        $variant['color'] = iconv("UTF-8","windows-1251",$variant['color']);
//        $variant['price'] = iconv("UTF-8","windows-1251",$variant['price']);
//        $variant['sklad'] = iconv("UTF-8","windows-1251",$variant['sklad']);
//        return $variant;
//    }

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
                    $content = $this->uniteArrays($data['sizes'], $content);
                }

                if(count($data['colors']) > 0){
                    $content = $this->uniteArrays($data['colors'], $content);
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


        function uniteArrays($arr1, $arr2){
            $result = array();
            $i=0;
            foreach ($arr1 as $key=>$value) {
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
        if(is_string($value) && !is_array($value)){
            $value = iconv("UTF-8","windows-1251",  $value);
        }
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
            $result = 'артикул: '.$variants[$variantNum]['productCode'].', размер: '.$variants[$variantNum]['size'].', цвет: '.$variants[$variantNum]['color'];
        }
        return $result;
    }

    public function createSkladInSelectTag($variants, $i){
        $com_str = '';
        if(count($variants) > 0) {
            $com_str .= "<select id='select_variants_".$i."' name='select_variants_".$i."'><option value=''>Выберите варианты</option>";
            foreach ($variants as $key=>$variant) {
                //$variant =   $this->_convertVariantsTo1251($variant);
                array_walk($variant, array($this, '_convertVariantsTo1251'));
                $com_str .= "<option value='".$key."'>".'размер: '.$variant['size'].' цвет: '.$variant['color']."</option>";
                //$com_str .= "<option value='".$variant['size']." ".$variant['color']."'>".''.$variant['productCode'].' размер: '.$variant['size'].' '.$variant['color']."</option>";
            }
            $com_str .= "</select><br>";
        }
        return $com_str;
    }

    public function createSkladInList($variants){
        $com_str = '';
        if(count($variants) > 0) {
            $com_str = '';
            foreach ($variants as $variant) {
                //$variant =   $this->_convertVariantsTo1251($variant);
                array_walk($variant, array($this, '_convertVariantsTo1251'));
                //$com_str .= '<tr><td>'.$variant['productCode'].'</td><td>'.$variant['size'].'</td><td>'.$variant['color'].'</td></tr>';
                $com_str .= '<tr><td>'.$variant['size'].'</td><td>'.$variant['color'].'</td></tr>';
            }
            //$com_str = substr($com_str, 0, -4);
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