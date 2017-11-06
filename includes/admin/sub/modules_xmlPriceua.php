<?php
//include("./core_functions/picture_functions.php");
//include("./core_functions/category_functions.php");
//include("./core_functions/product_functions.php");

$cats = catGetCategoryCList();

/*echo '<pre>';
print_r($cats);
echo '</pre>';*/

$str = ""; 
$conf = array(
			"firmId"=>23352,
			"firmName"=>"Mandrivnyk.kiev.ua",
			"stock"=>"В наличии");



function saveXMLtoFileHead($conf){

	$fileName = './XML/all-'.date('Y-m-d-H-i-s').'.xml';

	

	$fp = fopen($fileName, 'w');

	

	$str = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>"."\n";

	$str .= "<price>"."\n";

	$str .= "<date>".date('Y-m-d H:m')."</date>"."\n";	

	$str .= "<firmName>".$conf['firmName']."</firmName>"."\n";	

	$str .= "<firmId>".$conf['firmId']."</firmId>"."\n";

	$arr_srch = array("&","&n","&nb","&nbs");	

	$str = str_replace($arr_srch, "", $str);

	$res = fwrite($fp, $str);

	fclose($fp);

	return $fileName;

}			

function saveXMLtoFileMiddle($conf,$products, $category, $fileName)
{	
	$fp = fopen($fileName, 'a');   
	$str = "<rate></rate>"."\n";	
	$str .= "<categories>"."\n";	
	$str .= "<category>"."\n";	
	$str .= "<id>".$category['categoryID']."</id>"."\n";	
	$str .= "<name>".$category['name']."</name>"."\n";	
	$str .= "</category>"."\n";	
	$str .= "</categories>"."\n";	
	$str .= "<items>"."\n";	

	for($i=0;$i<count($products);$i++)
	{   //echo $i.'  '.$products[$i]['productID'].'<br>';
		$str .= "<item>"."\n";        
        $rand = substr(md5($products[$i]['name']), 0, 5);
		$str .= "<id>".$products[$i]['productID'].'_'.$rand."</id>"."\n";
		$str .= "<categoryId>".$category['categoryID']."</categoryId>"."\n";
		$str .= "<code>".$products[$i]['product_code']."</code>"."\n";
		$str .= "<vendor>".$products[$i]['producer']."</vendor>"."\n";
        if(($products[$i]['Price'] > 1500) && ($products[$i]['list_price']<$products[$i]['Price']))
            $gift = " + ПОДАРОК на выбор";
        else
            $gift = "";
		$str .= "<name>".$products[$i]['name'].$gift."</name>"."\n";
		$products[$i]['brief_description'] = $rest = substr(strip_tags($products[$i]['brief_description']), 0, 256);
		$products[$i]['brief_description'] = preg_replace("/&#?[a-z0-9]{2,8};/i","",$products[$i]['brief_description']);
		$str .= "<description>".$products[$i]['brief_description']."</description>"."\n";
		$str .= "<url>http://prokat.ho.com.ua/".$products[$i]['url_name']."</url>"."\n";
		$str .= "<image>http://prokat.ho.com.ua/products_pictures/".$products[$i]['image1']."</image>"."\n";
		$str .= "<priceRUAH>".$products[$i]['Price']."</priceRUAH>"."\n";
		$str .= "<oldprice>".($products[$i]['list_price']>$products[$i]['Price']?$products[$i]['list_price']:'')."</oldprice>"."\n";
		$str .= "<stock>".$conf['stock']."</stock>"."\n";
		$str .= "<guarantee></guarantee>"."\n";
		$str .= "</item>"."\n";
	}

	$str .= "</items>"."\n";	
	$arr_srch = array("&","&n","&nb","&nbs");	
	$str = str_replace($arr_srch, "", $str);
	$res = fwrite($fp, $str);
	fclose($fp);
	return $fileName;
}


function saveXMLtoFileMiddleFashion($conf,$products, $category, $fileName)
{	
	$fp = fopen($fileName, 'a');   
	$str = "<rate></rate>"."\n";	
	$str .= "<categories>"."\n";	
	$str .= "<category>"."\n";	
	$str .= "<id>".$category['categoryID']."</id>"."\n";	
	$str .= "<name>".$category['name']."</name>"."\n";	
	$str .= "</category>"."\n";	
	$str .= "</categories>"."\n";	
	$str .= "<items>"."\n";	

	for($i=0;$i<count($products);$i++)
	{   //echo $i.'  '.$products[$i]['productID'].'<br>';
		$str .= "<item>"."\n";        
        $rand = substr(md5($products[$i]['name']), 0, 5);	
        $str .= "<id>".$products[$i]['productID'].'_'.$rand."</id>"."\n"; 
		$str .= "<categoryId>".$category['categoryID']."</categoryId>"."\n";        
        $str .= "<group_id>".$products[$i]['productID']."</group_id>"."\n";
		$str .= "<code>".$products[$i]['product_code']."</code>"."\n";
		$str .= "<vendor>".$products[$i]['producer']."</vendor>"."\n";
		$str .= "<name>".$products[$i]['name']."</name>"."\n";
		$products[$i]['brief_description'] = $rest = substr(strip_tags($products[$i]['brief_description']), 0, 256);
		$products[$i]['brief_description'] = preg_replace("/&#?[a-z0-9]{2,8};/i","",$products[$i]['brief_description']);
		$str .= "<description>".$products[$i]['brief_description']."</description>"."\n";
		$str .= "<url>http://prokat.ho.com.ua/".$products[$i]['url_name']."</url>"."\n";
        
        //$str .= GetImagesToFileMiddle($str, $products[$i]);
        //echo 'echo $y = '.$y.'<br>';
        $y=1;
        while(isset($products[$i]['image'.$y]))
        {
            $str .= "<image>http://prokat.ho.com.ua/products_pictures/".$products[$i]['image'.$y]."</image>"."\n";
            $y++;
            
        } 
		//$str .= "<image>http://prokat.ho.com.ua/products_pictures/".$products[$i]['image1']."</image>"."\n";
		$str .= "<priceRUAH>".$products[$i]['Price']."</priceRUAH>"."\n";
		$str .= "<oldprice>".($products[$i]['list_price']>$products[$i]['Price']?$products[$i]['list_price']:'')."</oldprice>"."\n";
		$str .= "<stock>".$conf['stock']."</stock>"."\n";
		$str .= "<guarantee></guarantee>"."\n";
        $str .= "<param name='Оригинальность'>Оригинал</param>"."\n";
        $str .= "<param name='Сезон'></param>"."\n";
        $str .= "<param name='Материал'></param>"."\n";
        $str .= "<param name='Пол'></param>"."\n";
       
        if(isset($products[$i]['option_value_one']))
            $str .= "<param name='Размер' unit='EU'>".$products[$i]['option_value_one']."</param>"."\n"; 
        else
            $str .= "<param name='Размер' unit='EU'></param>"."\n"; 
        if(isset($products[$i]['color']))
            $str .= "<param name='Цвет'>".$products[$i]['color']."</param>"."\n"; 
        else
            $str .= "<param name='Цвет'></param>"."\n"; 
        
        
        switch ($products[$i]['producer'])
        {
            case 'Lasting';
            case 'Alpine pro';
                $str .= "<param name='Страна изготовления'>Чехия</param>"."\n"; 
                break;
            case 'X-Socks';
                $str .= "<param name='Страна изготовления'>Швейцария</param>"."\n"; 
                break;
            case 'Norveg';
                $str .= "<param name='Страна изготовления'>Германия</param>"."\n"; 
                break;     
            case 'Norwegian';
                $str .= "<param name='Страна изготовления'>Польша</param>"."\n"; 
                break;            
            case 'Tramp';
                $str .= "<param name='Страна изготовления'>Китай</param>"."\n"; 
                break;             
            case 'Commandor (Neve)';
                $str .= "<param name='Страна изготовления'>Украина</param>"."\n"; 
                break;      
            default;
                $str .= "<param name='Страна изготовления'></param>"."\n"; 
                break;
        }   
                
        //<param name="Размер" unit="RU">44</param>
		$str .= "</item>"."\n";
	}

	$str .= "</items>"."\n";	
	$arr_srch = array("&","&n","&nb","&nbs");	
	$str = str_replace($arr_srch, "", $str);
	$res = fwrite($fp, $str);
	fclose($fp);
	return $fileName;
}
function saveXMLtoFileFoot($fileName){

	

	$fp = fopen($fileName, 'a');

	$str = "</price>"."\n";

	$arr_srch = array("&","&n","&nb","&nbs");	

	$str = str_replace($arr_srch, "", $str);

	$res = fwrite($fp, $str);

	fclose($fp);

	return $fileName;

}

function saveXMLtoFile_OLD_VERSION($conf,$products, $category){

    // echo '<br>1';

    /*

    <price>

    <date>2010-05-25 17:00</date>

    <firmName>Название магазина</firmName>

    <firmId>1234</firmId>

    <rate>8.12</rate>

    <categories>

    <category>

    <id>1</id>

    <name>Электроника</name>

    </category>

    <category>

    <id>2</id>

    <parentId>1</parentId>

    <name>Мобильные телефоны</name>

    </category>

    </categories>

    <items>

    <item>

    <id>3278</id>

    <categoryId>2</categoryId>

    <code>n456-5300em-2010</code>

    <vendor>Nokia</vendor>

    <name>5300 ExpressMusic</name>

    <description>Мобильный телефон.</description>

    <url>http://shop.ua/1/2/123.html</url>

    <image>http://shop.ua/img/1/2/123.jpg</image>

    <priceRUAH>1000</priceRUAH>

    <oldprice>1200</oldprice>

    <priceRUSD>200</priceRUSD>

    <stock>На складе</stock>

    <guarantee>180</guarantee>

    </item>

    <item>...</item>

    </items>

    </price>
     */

	$fileName = './XML/CID-'.$category['categoryID'].'-'.$category['url_name'].'-'.date('Y-m-d-H-i-s').'.xml';

	//$fileName = './XML/CID-'.$category['categoryID'].'-'.date('Y-m-d-H-i-s').'.xml';

	$fp = fopen($fileName, 'a+');

	

	$str = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>"."\n";

	$str .= "<price>"."\n";

	$str .= "<date>".date('Y-m-d H:m')."</date>"."\n";	

	$str .= "<firmName>".$conf['firmName']."</firmName>"."\n";	

	$str .= "<firmId>".$conf['firmId']."</firmId>"."\n";	

	$str .= "<rate></rate>"."\n";	

	$str .= "<categories>"."\n";	

	$str .= "<category>"."\n";	

	$str .= "<id>".$category['categoryID']."</id>"."\n";	

	$str .= "<name>".$category['name']."</name>"."\n";	

	$str .= "</category>"."\n";	

	$str .= "</categories>"."\n";	

	$str .= "<items>"."\n";	

	

	for($i=0;$i<count($products);$i++)

	{   //echo $i.'  '.$products[$i]['productID'].'<br>';

		$str .= "<item>"."\n";	

		$str .= "<id>".$products[$i]['productID']."</id>"."\n";		

		$str .= "<categoryId>".$category['categoryID']."</categoryId>"."\n";		

		$str .= "<code>".$products[$i]['product_code']."</code>"."\n";		

		$str .= "<vendor>".$products[$i]['producer']."</vendor>"."\n";		

		$str .= "<name>".$products[$i]['name']."</name>"."\n";		

		$products[$i]['brief_description'] = $rest = substr(strip_tags($products[$i]['brief_description']), 0, 256);

		$products[$i]['brief_description'] = preg_replace("/&#?[a-z0-9]{2,8};/i","",$products[$i]['brief_description']);

		$str .= "<description>".$products[$i]['brief_description']."</description>"."\n";		

		$str .= "<url>http://prokat.ho.com.ua/".$products[$i]['url_name']."</url>"."\n";		

		$str .= "<image>http://prokat.ho.com.ua/products_pictures/".$products[$i]['enlarged']."</image>"."\n";		

		$str .= "<priceRUAH>".$products[$i]['Price']."</priceRUAH>"."\n";		

		$str .= "<oldprice>".($products[$i]['list_price']>$products[$i]['Price']?$products[$i]['list_price']:'')."</oldprice>"."\n";

		$str .= "<stock>".$conf['stock']."</stock>"."\n";				

		$str .= "<guarantee></guarantee>"."\n";				

		$str .= "</item>"."\n";	

	}

	$str .= "</items>"."\n";	

	$str .= "</price>"."\n";

	$arr_srch = array("&","&n","&nb","&nbs");	

	$str = str_replace($arr_srch, "", $str);

	$res = fwrite($fp, $str);

	fclose($fp);

	return $fileName;

}

function GetSelectedCID()

{    

    //		$q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID =".$_GET['selectedCID']." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0  GROUP BY  t1.productID") or die (db_error());

    //		$q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID =".$_GET['selectedCID']." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0 AND ( t1.producer = 'Tramp' OR t1.producer = 'Terra Incognita') GROUP BY  t1.productID") or die (db_error());

    // Термобелье

    // $q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID =".$_GET['selectedCID']." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0 AND ( t1.producer = 'Craft' OR t1.producer = 'Lasting' OR t1.producer = 'X-Bionic' OR t1.producer = 'Terra Incognita') GROUP BY  t1.productID") or die (db_error());

    // palatki

    //$q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID =".$_GET['selectedCID']." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0 AND ( t1.producer = 'Tramp' OR t1.producer = 'SOL' OR t1.producer = 'Terra Incognita') GROUP BY  t1.productID") or die (db_error());

    // TENTI        

    //$q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID =".$_GET['selectedCID']." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0 AND ( t1.producer = 'Tramp' OR t1.producer = 'SOL' OR t1.producer = 'Terra Incognita') GROUP BY  t1.productID") or die (db_error());

    //spaljniki        

    //  $q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID =".$_GET['selectedCID']." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0 AND ( t1.producer = 'Tramp' OR t1.producer = 'Travel Extreme' OR t1.producer = 'Terra Incognita') GROUP BY  t1.productID") or die (db_error());

    //RUKZAKI        

    //$q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID =".$_GET['selectedCID']." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0 AND ( t1.producer = 'Tramp' OR t1.producer = 'Travel Extreme' OR t1.producer = 'Terra Incognita' OR t1.producer = 'Osprey' OR t1.producer = 'Commandor (Neve)' OR t1.producer = 'Salewa' OR t1.producer = 'Tatonka') GROUP BY  t1.productID") or die (db_error());

    //Бахилы       

    //  $q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID =".$_GET['selectedCID']." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0 AND ( t1.producer = 'Travel Extreme' OR t1.producer = 'Salewa' OR t1.producer = 'Tatonka') GROUP BY  t1.productID") or die (db_error());

    //sumki

    //$q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID =".$_GET['selectedCID']." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0 AND ( t1.producer = 'Tramp' OR t1.producer = 'Travel Extreme' OR t1.producer = 'Terra Incognita' OR t1.producer = 'Osprey' OR t1.producer = 'Commandor (Neve)' OR t1.producer = 'Salewa' OR t1.producer = 'Tatonka') GROUP BY  t1.productID") or die (db_error());

    //ТРЕК ПАЛКИ

    //   $q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID =".$_GET['selectedCID']." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0 AND ( t1.producer = 'BLACK DIAMOND' OR t1.producer = 'Tramp' OR t1.producer = 'Salewa') GROUP BY  t1.productID") or die (db_error());

    // Газовое оборудование

    //$q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID =".$_GET['selectedCID']." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0 AND ( t1.producer = 'Tramp' OR t1.producer = 'Fire-Maple') GROUP BY  t1.productID") or die (db_error());

    // Мебель

    $q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID =".$_GET['selectedCID']." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0 AND ( t1.producer = 'Tramp' OR t1.producer = 'SOL') GROUP BY  t1.productID") or die (db_error());

    

    while ($row = db_fetch_row($q1))

    {		

        $products[] = $row;

        /*echo '<pre>';

        echo print_r($row).'<br>';

        echo '</pre>';

        exit();*/

        

    }

    return $products;

    

}

function GetMoreImages($products)
{
    
    // if there are more images for product
    for($i=0;$i<count($products);$i++)
    {
        $sql3 = "SELECT * FROM ".PRODUCT_PICTURES." WHERE productID =".$products[$i]['productID']."";
        // echo '<br><hr>'.$sql3;
        $q3 = db_query($sql3) or die (db_error());
        $imgNum = 1;
        while ($row = db_fetch_row($q3))
        {
            
            $products[$i]['image'.$imgNum]  = $row['enlarged'];
            $imgNum++;
        }
    }
    return $products;
}

function GetWithOptionID($strProducer, $categoryId, $fileName, $conf, $optionID, $optionID2=0)
{
    $sql = "SELECT t1.productID, t1.categoryID, t1.product_code, t1.title_two, t1.url_name, t1.name, t1.producer, t1.description, t1.Price, t1.in_stock, t1.brief_description, t1.list_price,  t3.optionID, t3.option_value, t4.name option_name
            FROM ".PRODUCTS_TABLE." t1             
            LEFT JOIN ".PRODUCT_OPTIONS_VALUES_TABLE." t3 ON t1.productID= t3.productID 
            LEFT JOIN ".PRODUCT_OPTIONS_TABLE." t4 ON t4.optionID = t3.optionID
            WHERE t1.categoryID =".$categoryId." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0 AND  t3.optionID=".$optionID." AND (".$strProducer." ) GROUP BY  t1.productID";
    
    $q1 = db_query($sql) or die (db_error());
    
    //echo '<br><hr>'.$sql;
    $k=0;
    $products = array();
    while ($row = db_fetch_row($q1))
    {	
        if($optionID2>0)
        {
            $sql2 = "SELECT t3.optionID, t3.option_value, t4.name option_name FROM ".PRODUCT_OPTIONS_VALUES_TABLE." t3 LEFT JOIN ".PRODUCT_OPTIONS_TABLE." t4 ON t4.optionID = t3.optionID WHERE t3.productID=".$row['productID']." AND t3.optionID=".$optionID2." ";        
            $q2 = db_query($sql2) or die (db_error());
            $row1 = db_fetch_row($q2);
            
            //echo '<br>'.$sql2;
            //echo '<hr><pre>';
            //echo print_r($row1).'<br>';
            //echo '</pre><hr>';  
            //echo '<br>count $row1 = '.count($row1);
            
            if(count($row1) >1)
            {
                $option_variants1 = explode(",", $row1['option_value']);
                for($y=0; $y<count($option_variants1); $y++)
                {
                    if($row['option_value'] !== '')
                    {
                        $option_variants = explode(",", $row['option_value']);
                        
                        for($i=0; $i<count($option_variants); $i++)
                        {
                            $row['option_value_one']   = $option_variants[$i];
                            $row['color'] = $option_variants1[$y];
                            $products[$k] = $row;
                            $products[$k++]['name'] .= ' '.$row['option_name'].': '.$row['option_value_one'].' '.$row1['option_name'].': '.$option_variants1[$y];
                        }
                    }
                    else
                        $products[] = $row;
                }
                

            }
            else
                $products[] = $row;
        }
        else
        {
            if($row['option_value'] !== '')
            {
                $option_variants = explode(",", $row['option_value']);
                
                for($i=0; $i<count($option_variants); $i++)
                {
                    $row['option_value_one'] = $option_variants[$i];
                    $products[$k] = $row;
                    $products[$k++]['name'] .= ' '.$row['option_name'].': '.$row['option_value_one'].'';
                }
            }
            else
                $products[] = $row;
        }    
    }

    return $products;
}

function GetWithOptionS($strProducer, $categoryId, $fileName, $conf, $optionID, $optionID2=0, $optionID3 =0)
{
    $products = array();
    $sql = "SELECT t1.productID, t1.categoryID, t1.product_code, t1.title_two, t1.url_name, t1.name, t1.producer, t1.description, t1.Price, t1.in_stock, t1.brief_description, t1.list_price
    FROM ".PRODUCTS_TABLE." t1 
    WHERE t1.categoryID =".$categoryId." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0  AND (".$strProducer." ) GROUP BY  t1.productID";
    echo $sql;
    $q1 = db_query($sql) or die (db_error());
    // if($categoryId == 404)
     
     
    
    while ($row = db_fetch_row($q1))
    {	   
        $sql2 = "SELECT * FROM ".PRODUCT_OPTIONS_VALUES_TABLE." t3 LEFT JOIN ".PRODUCT_OPTIONS_TABLE." t4 ON t4.optionID = t3.optionID WHERE t3.productID=".$row['productID']." ";        
        $q2 = db_query($sql2) or die (db_error());
        echo '<br>'.$sql2;
        $row1 = db_fetch_row($q2);
        $product_options = array();
        while ($row1 = db_fetch_row($q2))
        {
            $product_options[]=$row1;
        }
        echo '<hr><pre>';
        echo print_r($product_options).'<br>';
        echo '</pre><hr>'; 
        //exit();
        $products[] = $row;
    }
    return $products;
    
    
    
}
function GetWithOutOptionID($strProducer, $categoryId, $fileName, $conf)
{
    $products = array();
    $sql = "SELECT t1.productID, t1.categoryID, t1.product_code, t1.title_two, t1.url_name, t1.name, t1.producer, t1.description, t1.Price, t1.in_stock, t1.brief_description, t1.list_price
    FROM ".PRODUCTS_TABLE." t1 
    WHERE t1.categoryID =".$categoryId." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0  AND (".$strProducer." ) GROUP BY  t1.productID";
    $q1 = db_query($sql) or die (db_error());
   // if($categoryId == 404)
   // echo $sql;
   
    while ($row = db_fetch_row($q1))
    {	        
        $products[] = $row;
    }
    return $products;
}

function GetSelectedAll($strProducer, $categoryId, $fileName, $conf, $optionID =0, $optionID2=0, $fashion = 0)
{    
    // formiruem stroku  s brendami iz massiva
    if(count($strProducer)>=1)
    {
        $strProducer_str = '';
        for($i=0;$i<count($strProducer);$i++)
        {
            
            if($i==0)
                $strProducer_str .= "t1.producer ='".$strProducer[$i]."' ";
            else
                $strProducer_str .= "OR t1.producer ='".$strProducer[$i]."' ";
        }
    }
    
    //echo '<br>$optionID3 = '.$optionID3;
   if($optionID >0)
    {
        //echo '<br>TYT $optionID ='.$optionID;
        $products = GetWithOptionID($strProducer_str, $categoryId, $fileName, $conf, $optionID, $optionID2);        
    }         
    else
        $products = GetWithOutOptionID($strProducer_str, $categoryId, $fileName, $conf);    
    $products = GetMoreImages($products);
    //echo '<pre>';
    //echo print_r($products).'<br>';
    //echo '</pre>';    
    //exit();
    $selectedCategory = catGetCategoryById($categoryId);
    
    if($fashion == 0)
        saveXMLtoFileMiddle($conf,$products, $selectedCategory, $fileName);
    else    
        saveXMLtoFileMiddleFashion($conf,$products, $selectedCategory, $fileName);

    return $products;
}	



if(isset($_GET['all']))
{
    $fileName = saveXMLtoFileHead($conf);
    
    // Термобелье
    $termobeleArr = GetSelectedAll(['Craft','Lasting', 'X-Bionic','Terra Incognita', 'Commandor', 'Norveg'], 275, $fileName, $conf , 62,31 );//62 - размер, 31 - цвет
    // palatki
    $palatkiArr = GetSelectedAll(['Tramp','SOL', 'Terra Incognita'], 252, $fileName, $conf,14); // 14 - цвет внешнего тента    
    // TENTI
    $tentiArr = GetSelectedAll(['Tramp','SOL', 'Terra Incognita'], 264, $fileName, $conf,31);     //   31 - цвет    
    //spaljniki 
    $spalnikiArr = GetSelectedAll(['Tramp','Travel Extreme', 'Terra Incognita'], 267, $fileName, $conf, 31); // 31 - цвет
    //RUKZAKI
    $rukzakiArr = GetSelectedAll(['Tramp','Travel Extreme', 'Terra Incognita','Travel Extreme','Osprey', 'Commandor (Neve)', 'Salewa', 'Tatonka'], 312, $fileName, $conf, 31); // 31 - цвет    
    //Бахилы
    $bahiliArr = GetSelectedAll(['Travel Extreme', 'Salewa', 'Tatonka'], 419, $fileName, $conf, 31); // 31 - цвет    
    //sumki
    $sumkiArr = GetSelectedAll(['Tramp','Travel Extreme', 'Terra Incognita','Travel Extreme','Osprey', 'Commandor (Neve)', 'Salewa', 'Tatonka'], 393, $fileName, $conf, 31);  // 31 - цвет
    // //ТРЕК ПАЛКИ
    $trekPalkiArr = GetSelectedAll(['Tramp','BLACK DIAMOND', 'Salewa'], 350, $fileName, $conf); 
    //// Газовое оборудование
    $gazArr = GetSelectedAll(['Tramp','Fire-Maple', 'Pinguin', 'Kovea'], 428, $fileName, $conf);     
    // Мебель
    $mebelArr = GetSelectedAll(['Tramp','SOL'], 540, $fileName, $conf);         
    // Грелки
    $grelki = GetSelectedAll(['Little Hotties','BlazeWear', 'AlpenHeat', 'Kovea'], 573, $fileName, $conf);    
    // Zaschita dlya kataniya
    $protect = GetSelectedAll(['Destroyer'], 517, $fileName, $conf,62);    
    // Maski dlya kataniya
    $maski = GetSelectedAll(['Carrera', 'Alpine pro', 'Oakley'], 380, $fileName, $conf);    
    // Shlemi dlya kataniya
    $Shlemi = GetSelectedAll(['Elan', 'Alpine pro', 'Salewa', 'Destroyer'], 490, $fileName, $conf, 62);//62 - размер
    // Ligi begovie
    $ligi = GetSelectedAll(['ISG'], 576, $fileName, $conf, 62);//62 - размер    
    // Ligi begovie
    $Botiligi = GetSelectedAll(['ISG'], 577, $fileName, $conf, 62, 31);//62 - размер , 31 - цвет    
    // Krepi dlya Ligi begovie
    $Krepiligi = GetSelectedAll(['ISG'], 578, $fileName, $conf);    
    // Palki Ligi begovie
    $PalkiLigi = GetSelectedAll(['ISG'], 580, $fileName, $conf, 62);//62 - размер    
    // Palki Ligi 
    $PalkiLigiG = GetSelectedAll(['Elan'], 387, $fileName, $conf, 62);//62 - размер    
    // Ledostupi
    $ledostu = GetSelectedAll(['YAKTRAX'], 572, $fileName, $conf, 62);//62 - размер    
    // Snegostupi
    $Snegostupi = GetSelectedAll(['Tramp', 'Salewa'], 479, $fileName, $conf, 62);//62 - размер
    // Chehli
    $Chehli = GetSelectedAll(['Tramp','Travel Extreme', 'Terra Incognita','Elan','Tatonka'], 404, $fileName, $conf, 62,31); // 31 - цвет
    // kovriki
    $kovriki = GetSelectedAll(['Tramp','Trimm', 'Terra Incognita','Ferrino','Verdani', 'Ижевск'], 532, $fileName, $conf); // 31 - цвет
    // poncho
    $poncho = GetSelectedAll(['Terra Incognita'], 330, $fileName, $conf, 62,31); // 31 - цвет
     // fonari
    $fonari = GetSelectedAll(['BLACK DIAMOND','Ferrino','Travel Extreme','Tramp','Silva'], 443, $fileName, $conf); // 31 - цвет
    // slekline
    $slekline = GetSelectedAll(['GIBBON'], 593, $fileName, $conf); // 31 - цвет
    // sistemistrah
    $sistemistrah = GetSelectedAll(['Travel Extreme'], 549, $fileName, $conf, 62); // 31 - цвет
    // $kaski
    $kaski = GetSelectedAll(['Salewa', 'AustriAlpin'], 570, $fileName, $conf); // 31 - цвет
    // $koshki
    $koshki = GetSelectedAll(['Salewa', 'AustriAlpin'], 582, $fileName, $conf); // 31 - цвет
    // $verevka
    $verevka = GetSelectedAll(['Salewa', 'Tendon'], 594, $fileName, $conf); // 31 - цвет
    // $gumar
    $gumar = GetSelectedAll(['BLACK DIAMOND', 'FA'], 587, $fileName, $conf); // 31 - цвет
    // $karabini
    $karabini = GetSelectedAll(['Salewa'], 586, $fileName, $conf); // 31 - цвет
    // $lavinnoe
    $lavinnoe = GetSelectedAll(['Salewa', 'BCA'], 588, $fileName, $conf); // 31 - цвет
    // $ledorubi
    $ledorubi = GetSelectedAll(['Salewa', 'AustriAlpin'], 584, $fileName, $conf); // 31 - цвет
    // $molotki
    $molotki = GetSelectedAll(['Salewa', 'AustriAlpin'], 592, $fileName, $conf); // 31 - цвет
    // $ottyagki
    $ottyagki = GetSelectedAll(['Salewa', 'AustriAlpin'], 585, $fileName, $conf); // 31 - цвет
    // $kruchya
    $kruchya = GetSelectedAll(['Salewa'], 591, $fileName, $conf); // 31 - цвет
    // $spuskovie
    $spuskovie = GetSelectedAll(['Salewa', 'FA', 'AustriAlpin','Singing rock'], 589, $fileName, $conf); // 31 - цвет
    // $aksSkalolaz
    $aksSkalolaz = GetSelectedAll(['Salewa'], 599, $fileName, $conf); // 31 - цвет
    // $magneziya
    $magneziya = GetSelectedAll(['Salewa'], 597, $fileName, $conf); // 31 - цвет
    // $skalniki
    $skalniki = GetSelectedAll(['Salewa', 'Zamberlan'], 596, $fileName, $conf); // 31 - цвет
    // $crossfit
    $crossfit = GetSelectedAll(['Way4you'], 614, $fileName, $conf); 
    // $Termonoski
    $Termonoski = GetSelectedAll(['Lasting', 'X-Socks', 'Norwegian', 'Norveg', 'Craft'], 297, $fileName, $conf, 62,31, $fashion =1); // 
    // $Flis
    $Flis = GetSelectedAll(['Lasting', 'Tramp', 'Commandor (Neve)', 'Norveg', 'Craft', 'Alpine pro'], 357, $fileName, $conf, 62,31, $fashion =1); // 82 - материал  
    
    
    $products = array_merge($termobeleArr, $palatkiArr, $tentiArr, $spalnikiArr, $rukzakiArr, $bahiliArr, $sumkiArr, $trekPalkiArr, $gazArr,  $mebelArr,$grelki, $protect, $maski, $Shlemi,$ligi, $Botiligi, 
                            $Krepiligi,$PalkiLigi, $PalkiLigiG, $ledostu, $Snegostupi, $Chehli,$kovriki, $poncho, $fonari, $slekline, $sistemistrah, $kaski, $koshki, $verevka,$gumar,$karabini, $lavinnoe, 
                            $ledorubi,$molotki,$ottyagki, $kruchya, $spuskovie, $aksSkalolaz, $magneziya, $skalniki, $crossfit, $Termonoski, $Flis);

    

    //echo "count($rand_prod_action) = ".count($rand_prod_action);

	$productsNum = count($products);

	

	if($productsNum== 0 )

	{

		$str = 'В выбранной категории нет товаров<br>';

	}

	else 	

	{

		$smarty->assign( "products", $products);

		$smarty->assign( "productsNum", $productsNum);

		

		saveXMLtoFileFoot($fileName);

	}	

	$smarty->assign( "fileName", $fileName );

	//$smarty->assign( "selectedCID", $_GET['selectedCID'] );

	//$smarty->assign( "selectedCategory", $selectedCategory ); 

    

}



if(isset($_GET['selectedCID']))

{

    

    $products = GetSelectedCID();

    //echo "count($rand_prod_action) = ".count($rand_prod_action);

	$productsNum = count($products);

	

	if($productsNum== 0 )

	{

		$str = 'В выбранной категории нет товаров<br>';

	}

	else 	

	{

		$smarty->assign( "products", $products);

		$smarty->assign( "productsNum", $productsNum);

		$selectedCategory = catGetCategoryById($_GET['selectedCID']);

		/*echo '<pre>';

        echo print_r($selectedCategory);

		echo '</pre>';*/

		$fileName = saveXMLtoFile($conf, $products, $selectedCategory);		

	}	

	$smarty->assign( "fileName", $fileName );

	$smarty->assign( "selectedCID", $_GET['selectedCID'] );

	$smarty->assign( "selectedCategory", $selectedCategory );

}	



$smarty->assign("str", $str);

$smarty->assign("cats", $cats);

$smarty->assign("admin_sub_dpt", "modules_xmlHotline.tpl.html");



?>