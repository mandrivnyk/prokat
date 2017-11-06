<?	
	include_once('js/simplehtmldom_1_5/simple_html_dom.php');
	include_once('./resizeimage.inc.php');	
	include_once("./cfg/connect.inc.php");
	include_once("./includes/database/".DBMS.".php");
	include_once("./core_functions/category_functions.php");
	include_once("./core_functions/product_functions.php");
	include_once("./core_functions/picture_functions.php");
	include_once("./core_functions/configurator_functions.php");
	include_once("./core_functions/datetime_functions.php");
	include_once("./core_functions/tax_function.php");
	include_once("./core_functions/setting_functions.php" );
	include_once( "./core_functions/functions.php" );
	include_once( "./core_functions/url_function.php" );
	include_once( "./core_functions/parsing_functions.php" );
	
	$dir_name = './products_pictures/';
	$dir_name_main = './products_pictures/main/';
	$dir_name_plus = 'gor_';
	$file_name = "serialized_arr_to_file.txt";	
	$insert = iconv("UTF-8","windows-1251",$_POST['insert']);
    $info = '';
switch ($insert)
{	
	case 0:	 //====================================== CASE 0  =========================================================
	
		$info .='$insert = '.$insert;
		$url_http = iconv("UTF-8","windows-1251",$_POST['utpf']);	
		
		$product_info['categoryID'] = iconv("UTF-8","windows-1251",$_POST['cid']);
		$product_info['title_one'] = iconv("UTF-8","windows-1251",$_POST['ti']);
		$product_info['title_two'] = iconv("UTF-8","windows-1251",$_POST['tw']);	
		$product_info['producer'] = iconv("UTF-8","windows-1251",$_POST['pr']);
		$product_info['meta_description'] = iconv("UTF-8","windows-1251",$_POST['md']);
		$product_info['meta_keywords'] = iconv("UTF-8","windows-1251",$_POST['mk']);
		$product_info['list_price'] = '';
		
		/*$info .='<pre>';
		$info .=print_r($product_info);
		$info .= '</pre>';*/
		//$info .= '$url_to_pars_from = '.$url_to_pars_from.'<br>';


	


	
		$products_arr =  GetLinksToProducts_REUSCH($url_http, $dir_name, $file_name, $dir_name_main, $product_info);
		MakeSmallImages($dir_name, $dir_name_main, 100);
	 
	  
						 $info .='<pre>';
						 $info .= print_r($products_arr);
						 $info .= '</pre>';
		//echo '<script language="javascript"> var pars_info_div = document.getElementById("pars_info"); var all_pars_info = pars_info_div.innerHTML; pars_info_div.innerHTML ="'.$info.'"+all_pars_info;</script>';
		//echo '<script language="javascript">parsINSERT(0);</script>';
		break;
	
	case 1:    //====================================== CASE 1   =========================================================
		
		$num = iconv("UTF-8","windows-1251",$_POST['num']);
		$info .='$num = '.$num.'<br>';
		$num_next = $num +1;
		
		MagicQuotesRuntimeSetting();
		//connect 2 database
		db_connect(DB_HOST,DB_USER,DB_PASS) or die (db_error());
		db_select_db(DB_NAME) or die (db_error());
		settingDefineConstants();		
		
		$products_arr = UNserialize_arr_in_file($dir_name,  $file_name);
				
		$info .= $products_arr[$num]['name'].'<br>';
		
		


		
		
		$productID = AddProduct_from_another_site($products_arr[$num]);		
		if($productID == 0)
		{
			$info .=  '$productID = Уже есть такой продукт<br><hr>';
		}	
		else 
		{ 
			$resultAddIMG = AddProductPicturesSHORT($productID, $products_arr[$num]['ProductMainImage'], $products_arr[$num]['ProductMainImage_100'],$products_arr[$num]['ProductMainImage']);			 
			$info .=  '$productID = '.$productID.'<br><hr>';
		}
		if ($num_next < count($products_arr))
		{
			echo '<script language="javascript"> var pars_info_div = document.getElementById("pars_info"); var all_pars_info = pars_info_div.innerHTML; pars_info_div.innerHTML ="'.$info.'"+all_pars_info;</script>';
			echo '<script language="javascript">parsINSERT('.$num_next.');</script>';
		}
		break;	

}


?>