<?
	include_once('js/simplehtmldom_1_5/simple_html_dom.php');
	require_once('./resizeimage.inc.php');

	
	include("./cfg/connect.inc.php");
	include("./includes/database/".DBMS.".php");
	include("./core_functions/category_functions.php");
	include("./core_functions/product_functions.php");
	include("./core_functions/picture_functions.php");
	include("./core_functions/configurator_functions.php");
	include("./core_functions/datetime_functions.php");
	include("./core_functions/tax_function.php");
	include("./core_functions/setting_functions.php" );
	include( "./core_functions/functions.php" );
	include_once( "./core_functions/url_function.php" );
	include_once( "./core_functions/parsing_functions.php" );
	
	

	
$current = iconv("UTF-8","windows-1251",$_GET['current']);
$all = iconv("UTF-8","windows-1251",$_GET['all']);
$info = "PARCING<br>";
//get_BIG_GOOGLE_imgs_txt_file_list($current, $all);
//=======================================
//$url_http1 = 'http://www.gorgany.com/product_info.php/products_id/3970/language/ru?osCsid=';

///MakeSmallImages('./images/gor/main/', 150);
//exit();
$url_http = 'http://www.gorgany.com/index.php/cPath/325_23/ml/115?osCsid=';
$dir_name = './products_pictures/';
$dir_name_main = './products_pictures/main/';
$dir_name_plus = 'gor_';
$file_name = "serialized_arr_to_file.txt";

					 



//============================================================= 

		MagicQuotesRuntimeSetting();
	//connect 2 database
	db_connect(DB_HOST,DB_USER,DB_PASS) or die (db_error());
	db_select_db(DB_NAME) or die (db_error());
	settingDefineConstants();

$products_arr = UNserialize_arr_in_file($dir_name,  $file_name);
foreach ($products_arr as $product)
{
	 $product['categoryID'] = 248;
	 $product['title_one'] = 'title_one';
	 $product['title_two'] = 'title_two';
	 $product['producer'] = 'producer';
	 $product['meta_description']= 'meta_description';
	 $product['meta_keywords'] ='meta_keywords';
	 $product['list_price'] = '';
	 $productID = AddProduct_from_another_site($product);
	 
	 AddProductPicturesSHORT($productID, $product['ProductMainImage'], $product['ProductMainImage_100'],$product['ProductMainImage']);
	 
	 echo '$productID = '.$productID.'<br>';
	
}



//===============================================

$products_arr =  GetLinksToProducts($url_http, $dir_name, $file_name, $dir_name_main);
MakeSmallImages($dir_name, $dir_name_main, 100);
 
  
					 echo '<pre>';
						 print_r($products_arr);
					 echo '</pre>';
exit();
					 
	


?>

