<?php
/*echo '<pre>';
	print_r($_GET);
echo '</pre>';*/

	if (!is_dir('. /statistic_from_030713/'.$_SERVER['REMOTE_ADDR']))  // ---- директори€ - ip  
	{
		//---создать директорию
		if (@mkdir('. /statistic_from_030713/'.$_SERVER['REMOTE_ADDR'], 0755)) 
		{
   			if( !file_exists('. /statistic_from_030713/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt')) 
   			{
				$fp = fopen('. /statistic_from_030713/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt', "w"); // ("r" - считывать "w" - создавать "a" - добовл€ть к тексту), мы создаем файл
				$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '. date('H:m:s');
				$str = $str.' = '.$_SERVER['REQUEST_URI'];
				$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
				$res = fwrite($fp, $str);
				fclose ($fp);
			}
		}
	  //else die('Ќе удалось создать директории...');
	}
	else   // ------------------------------------файл txt - дата
	{
		
			if( !file_exists('. /statistic_from_030713/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt')) 
   			{
				$fp = fopen('. /statistic_from_030713/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt', "w"); // ("r" - считывать "w" - создавать "a" - добовл€ть к тексту), мы создаем файл
				$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '. date('H:m:s');
				$str = $str.' = '.$_SERVER['REQUEST_URI'];
				$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
				$res = fwrite($fp, $str);
				fclose ($fp);
			}	
			else 
			{
				$fp = fopen('. /statistic_from_030713/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt', "a"); // ("r" - считывать "w" - создавать "a" - добовл€ть к тексту), мы создаем файл
				$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '.date('H:m:s');
				$str = $str.' = '.$_SERVER['REQUEST_URI'];
				$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
				$res = fwrite($fp, $str);
				fclose ($fp);
			}
	}
	
	
	
$fp = fopen('./incomeip.txt', 'a');
$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '. date('H:m:s');
$str = $str.' = '.$_SERVER['REQUEST_URI'];
$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
$res = fwrite($fp, $str);
fclose($fp);
	
$fp = fopen('./cart.txt', 'a');
$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '. date('H:m:s');
$str = $str.' = '.$_SERVER['REQUEST_URI'];
$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
$res = fwrite($fp, $str);
fclose($fp);
	//core file

	// -------------------------INITIALIZATION-----------------------------//

	//include core files
	include("./cfg/connect.inc.php");
	include("./includes/database/".DBMS.".php");
	include("./cfg/language_list.php");
	include("./core_functions/functions.php");
	include("./core_functions/category_functions.php");
	include("./core_functions/cart_functions.php");
	include("./core_functions/product_functions.php");
	include("./core_functions/statistic_functions.php");
	include("./core_functions/reg_fields_functions.php" );
	include("./core_functions/registration_functions.php" );
	include("./core_functions/country_functions.php" );
	include("./core_functions/zone_functions.php" );
	include("./core_functions/datetime_functions.php" );
	include("./core_functions/order_status_functions.php" );
	include("./core_functions/order_functions.php" );
	include("./core_functions/aux_pages_functions.php" );
	include("./core_functions/picture_functions.php" ); 
	include("./core_functions/configurator_functions.php" );
	include("./core_functions/option_functions.php" );
	include("./core_functions/search_function.php" );
	include("./core_functions/discount_functions.php" ); 
	include("./core_functions/custgroup_functions.php" ); 
	include("./core_functions/shipping_functions.php" );
	include("./core_functions/payment_functions.php" );
	include("./core_functions/tax_function.php" ); 
	include("./core_functions/currency_functions.php" );
	include("./core_functions/module_function.php" );
	include("./core_functions/crypto/crypto_functions.php");
	include("./core_functions/quick_order_function.php" ); 
	include("./core_functions/setting_functions.php" );
	include("./core_functions/subscribers_functions.php" );
	include("./core_functions/version_function.php" );
	include("./core_functions/discussion_functions.php" );
	include("./core_functions/order_amount_functions.php" ); 

	session_start();

	MagicQuotesRuntimeSetting();

	//init Smarty
	require 'smarty/smarty.class.php'; 
	$smarty = new Smarty; //core smarty object
	$smarty_mail = new Smarty; //for e-mails

	//select a new language?
	if (isset($_POST["lang"]))
		$_SESSION["current_language"] = $_POST["lang"];

	//current language session variable
	if (!isset($_SESSION["current_language"]) ||
		$_SESSION["current_language"] < 0 || $_SESSION["current_language"] > count($lang_list))
			$_SESSION["current_language"] = 0; //set default language
	//include a language file
	if (isset($lang_list[$_SESSION["current_language"]]) &&
		file_exists("languages/".$lang_list[$_SESSION["current_language"]]->filename))
	{
		//include current language file
		include("languages/".$lang_list[$_SESSION["current_language"]]->filename);
	}
	else
	{
		die("<font color=red><b>ERROR: Couldn't find language file!</b></font>");
	}

	//connect to the database
	db_connect(DB_HOST,DB_USER,DB_PASS) or die (db_error());
	db_select_db(DB_NAME) or die (db_error());

	settingDefineConstants();

	if ((int)CONF_SMARTY_FORCE_COMPILE) //this forces Smarty to recompile templates each time someone runs cart.php
	{
		$smarty->force_compile = true;
		$smarty_mail->force_compile = true;
	}

	//authorized access check
	include("./checklogin.php");

	//# of selected currency
	$current_currency = isset($_SESSION["current_currency"]) ? $_SESSION["current_currency"] : CONF_DEFAULT_CURRENCY;
	$smarty->assign("current_currency", $current_currency);
	$q = db_query("select code, currency_value, where2show, currency_iso_3, Name from ".CURRENCY_TYPES_TABLE." where CID='$current_currency'") or die (db_error());
	if ($row = db_fetch_row($q))
	{
		$smarty->assign("currency_name", $row[0]);
		$selected_currency_details = $row; //for show_price() function
	}
	else //no currency found. In this case check is there any currency type in the database
	{
		$q = db_query("select code, currency_value, where2show from ".CURRENCY_TYPES_TABLE) or die (db_error());
		if ($row = db_fetch_row($q))
		{
			$smarty->assign("currency_name", $row[0]);
			$selected_currency_details = $row; //for show_price() function
		}
	}


	//set Smarty include files dir
	$smarty->template_dir = "./templates/frontend/".$lang_list[$_SESSION["current_language"]]->template_path;
	$smarty_mail->template_dir = "./templates/email";

	if (isset($_SESSION["log"])) $smarty->assign("log", $_SESSION["log"]);

	//should we add products to cart?
	if ( isset($_GET["addproduct"]) )
	{
		$variants=array();
		foreach( $_GET as $key => $val )
		{
			if(strstr($key, "option_select_hidden_"))
				$variants[]=$val;
		}
		
		

if($_GET['size_sel'])
{	$variants_length = count($variants);
	

	$variants[$variants_length] = str_replace(" ","+", ' –азмер: '.$_GET['size_sel']);
	//echo '$variants[$variants_length]= '.$variants[$variants_length];

}
if($_GET['color_sel'])
{	$variants_length = count($variants);
	
	$variants[$variants_length] =  str_replace(" ","+", ' ÷вет: '.$_GET['color_sel']);

}		

/*echo '<pre>';
	print_r($variants);
echo '</pre>';	*/

		unset( $_SESSION["variants"] );
		$_SESSION["variants"]=$variants;
		
/*		echo '<pre>';
	print_r($_SESSION);
echo '</pre>';*/
		header("Location: cart.php?shopping_cart=yes&add2cart=".(int)$_GET["addproduct"] );
	}


	//specify that this is a popup window
	$this_is_a_popup_cart_window = true;
	$smarty->assign("this_is_a_popup_cart_window", 1);
	
	//include core shopping cart routine
	include("./includes/shopping_cart.php");

	//show Smarty output
	$smarty->display("shopping_cart.tpl.html");

?>