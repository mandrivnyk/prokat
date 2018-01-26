<?
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
function saveXMLtoFileMiddle($conf,$products, $category, $fileName){
	
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



function GetSelectedAll($strProducer, $categoryId, $fileName, $conf)
{    
    
        $q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID =".$categoryId." AND t1.enabled =1 AND t1.in_stock >0 AND t1.Price >0 AND (".$strProducer." ) GROUP BY  t1.productID") or die (db_error());
        while ($row = db_fetch_row($q1))
		{		
			$products[] = $row;
				/*echo '<pre>';
				echo print_r($row).'<br>';
				echo '</pre>';
				exit();*/
			
		}
		
		$selectedCategory = catGetCategoryById($categoryId);
		saveXMLtoFileMiddle($conf,$products, $selectedCategory, $fileName);
        return $products;		
		
}	

if(isset($_GET['all']))
{
    
    $fileName = saveXMLtoFileHead($conf);
    // Термобелье
    $termobeleArr = GetSelectedAll("t1.producer = 'Craft' OR t1.producer = 'Lasting' OR t1.producer = 'X-Bionic' OR t1.producer = 'Terra Incognita'", 275, $fileName, $conf );
    
    // palatki      
    $palatkiArr = GetSelectedAll("t1.producer = 'Tramp' OR t1.producer = 'SOL' OR t1.producer = 'Terra Incognita'", 252, $fileName, $conf);
    
    // TENTI        
    $tentiArr = GetSelectedAll("t1.producer = 'Tramp' OR t1.producer = 'SOL' OR t1.producer = 'Terra Incognita'", 264, $fileName, $conf);       
    
    //spaljniki 
    $spalnikiArr = GetSelectedAll("t1.producer = 'Tramp' OR t1.producer = 'Travel Extreme' OR t1.producer = 'Terra Incognita'", 267, $fileName, $conf);              
    
    //RUKZAKI        
    $rukzakiArr = GetSelectedAll("t1.producer = 'Tramp' OR t1.producer = 'Travel Extreme' OR t1.producer = 'Terra Incognita' OR t1.producer = 'Osprey' OR t1.producer = 'Commandor (Neve)' OR t1.producer = 'Salewa' OR t1.producer = 'Tatonka'", 312, $fileName, $conf);              
        
    //Бахилы
    $bahiliArr = GetSelectedAll("t1.producer = 'Travel Extreme' OR t1.producer = 'Salewa' OR t1.producer = 'Tatonka'", 419, $fileName, $conf);              
        
    //sumki
    $sumkiArr = GetSelectedAll("t1.producer = 'Tramp' OR t1.producer = 'Travel Extreme' OR t1.producer = 'Terra Incognita' OR t1.producer = 'Osprey' OR t1.producer = 'Commandor (Neve)' OR t1.producer = 'Salewa' OR t1.producer = 'Tatonka'", 393, $fileName, $conf);              
       // 
    //ТРЕК ПАЛКИ
    $trekPalkiArr = GetSelectedAll("t1.producer = 'BLACK DIAMOND' OR t1.producer = 'Tramp' OR t1.producer = 'Salewa'", 350, $fileName, $conf);              
        // 
    // Газовое оборудование
    $gazArr = GetSelectedAll("t1.producer = 'Tramp' OR t1.producer = 'Fire-Maple'", 428, $fileName, $conf);              
        
    // Мебель
    $mebelArr = GetSelectedAll("t1.producer = 'Tramp' OR t1.producer = 'SOL'", 540, $fileName, $conf);              
        // 
    		
    		
  //  $products = array_merge($products, $termobeleArr, $palatkiArr, $tentiArr, $spalnikiArr);
    $products = array_merge($termobeleArr, $palatkiArr, $tentiArr, $spalnikiArr, $rukzakiArr, $bahiliArr, $sumkiArr, $trekPalkiArr, $gazArr,  $mebelArr);
           
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