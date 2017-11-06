<?php
	if (!is_dir('./statistic/'.$_SERVER['REMOTE_ADDR']))  // ---- директори€ - ip  
	{
		//---создать директорию
		if (mkdir('./statistic/'.$_SERVER['REMOTE_ADDR'], 0755)) 
		{
   			if( !file_exists('./statistic/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt')) 
   			{
				$fp = fopen('./statistic/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt', "w"); // ("r" - считывать "w" - создавать "a" - добовл€ть к тексту), мы создаем файл
				$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '.$_SERVER['REQUEST_TIME'];
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
		
			if( !file_exists('./statistic/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt')) 
   			{
				$fp = fopen('./statistic/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt', "w"); // ("r" - считывать "w" - создавать "a" - добовл€ть к тексту), мы создаем файл
				$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '.$_SERVER['REQUEST_TIME'];
				$str = $str.' = '.$_SERVER['REQUEST_URI'];
				$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
				$res = fwrite($fp, $str);
				fclose ($fp);
			}	
			else 
			{
				$fp = fopen('./statistic/'.$_SERVER['REMOTE_ADDR'].'/'.date('Y-m-d').'.txt', "a"); // ("r" - считывать "w" - создавать "a" - добовл€ть к тексту), мы создаем файл
				$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '.$_SERVER['REQUEST_TIME'];
				$str = $str.' = '.$_SERVER['REQUEST_URI'];
				$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
				$res = fwrite($fp, $str);
				fclose ($fp);
			}
	}
	
	
$fp = fopen('./incomeip.txt', 'a');
$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '.$_SERVER['REQUEST_TIME'];
$str = $str.' = '.$_SERVER['REQUEST_URI'];
$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
$res = fwrite($fp, $str);
fclose($fp);



if(($_SERVER['REMOTE_ADDR'] == '95.158.10.98') || ($_SERVER['REMOTE_ADDR'] ==  '109.254.49.19'))
{
	$fp = fopen('./kov.txt', 'a');
	$str = $_SERVER['REMOTE_ADDR'].' = '.date('Y-m-d').' = '.$_SERVER['REQUEST_TIME'];
	$str = $str.' = '.$_SERVER['REQUEST_URI'];
	$str = $str.' = '.$_SERVER['HTTP_USER_AGENT']."\r\n";
	$res = fwrite($fp, $str);
	fclose($fp);
}	


$DebugMode = false;
 //main admin module
date_default_timezone_set('Europe/Kiev');
function add_department($admin_dpt)
	//adds new $admin_dpt to departments list
{
	global $admin_departments;

	$i = 0;
	while ($i<count($admin_departments) && $admin_departments[$i]["sort_order"] < $admin_dpt["sort_order"]) $i++;
	for ($j=count($admin_departments)-1; $j>=$i; $j--)
		$admin_departments[$j+1] = $admin_departments[$j];
	$admin_departments[$i] = $admin_dpt;
}

	include("./cfg/connect.inc.php");
	include("./includes/database/".DBMS.".php");
	include("./cfg/language_list.php");
	include("./core_functions/functions.php");
	include("./core_functions/category_functions.php");
	include("./core_functions/product_functions.php");
	include("./core_functions/statistic_functions.php");
	include("./core_functions/custgroup_functions.php"); 
	include("./core_functions/reg_fields_functions.php");
	include("./core_functions/catalog_import_functions.php");
	include("./core_functions/option_functions.php");
	include("./core_functions/country_functions.php" );
	include("./core_functions/zone_functions.php" );
	include("./core_functions/xml_parser.php");
	include("./core_functions/xml_installer/xml_installer.php");
	include("./core_functions/serialization_functions.php" );
	include("./core_functions/registration_functions.php" );
	include("./core_functions/order_status_functions.php" );
	include("./core_functions/discussion_functions.php" );
	include("./core_functions/datetime_functions.php" );
	include("./core_functions/aux_pages_functions.php" );
	include("./core_functions/setting_functions.php" );
	include("./core_functions/picture_functions.php" );
	include("./core_functions/tax_function.php" );
	include("./core_functions/shipping_functions.php" ); 
	include("./core_functions/payment_functions.php" ); 
	include("./core_functions/discount_functions.php" ); 
	include("./core_functions/currency_functions.php" );
	include("./core_functions/order_functions.php" ); 
	include("./core_functions/crypto/crypto_functions.php");
	include("./core_functions/subscribers_functions.php" ); 
	include("./core_functions/cart_functions.php" ); 
	include("./core_functions/report_function.php" ); 
	include("./core_functions/order_amount_functions.php" ); 
	include("./core_functions/linkexchange_functions.php" ); 
	include("./core_functions/affiliate_functions.php" );
	include("./core_functions/module_function.php" );
	include("./cfg/paths.inc.php" );
	
	include('./classes/class.virtual.shippingratecalculator.php');
	include('./classes/class.virtual.paymentmodule.php');
	include_once("./js/fckeditor/fckeditor.php");

	session_start();

	MagicQuotesRuntimeSetting();

	//init Smarty
	require 'smarty/smarty.class.php'; 
	$smarty = new Smarty; //core smarty object
//	$smarty_mail = new Smarty; //for e-mails

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


	//connect to database
	db_connect(DB_HOST,DB_USER,DB_PASS) or die (db_error());
$q = db_query("SET NAMES CP1251");
$q = db_query("SET COLLATION_CONNECTION=CP1251_GENERAL_CI");
	db_select_db(DB_NAME) or die (db_error());

	settingDefineConstants();

	if ((int)CONF_SMARTY_FORCE_COMPILE) //this forces Smarty to recompile templates each time someone runs admin.php
	{
		$smarty->force_compile = true;
//		$smarty_mail->force_compile = true;
	}

	//authorized login check
	include("./checklogin.php");
	if (!isset($_SESSION["log"])){
		
		$_POST = xStripSlashesGPC($_POST);
		$_GET = xStripSlashesGPC($_GET);
		
		if(isset($_POST['fLogin']) && isset($_POST['fPassword'])){
			
			if(regAuthenticate($_POST['fLogin'], $_POST['fPassword'] ))
				Redirect(set_query('&__tt='));
			Redirect(set_query('&login='.urlencode($_POST['fLogin']).'&error=1'));
		}

		if(isset($_GET['error'])){
			
			$smarty->assign('Error', 1);
		}
		if (isset($_GET['login'])) {
			
			$smarty->hassign('Login', urldecode($_GET['login']));
		}
		//set Smarty include files dir
		$smarty->template_dir = "./templates";
		$smarty->display('backend/auth_form.tpl.html');
		die;
		
	}elseif ( CONF_BACKEND_SAFEMODE != 1 && (strcmp($_SESSION["log"],ADMIN_LOGIN))) //unauthorized
	{
		die (ERROR_FORBIDDEN);
	}

	//validate data to avoid SQL injections
	if (isset($_GET["customerID"])) $_GET["customerID"] = (int) $_GET["customerID"];
	if (isset($_GET["settings_groupID"])) $_GET["settings_groupID"] = (int) $_GET["settings_groupID"];
	if (isset($_GET["orderID"])) $_GET["orderID"] = (int) $_GET["orderID"];
	if (isset($_GET["answer"])) $_GET["answer"] = (int) $_GET["answer"];
	if (isset($_GET["productID"])) $_GET["productID"] = (int) $_GET["productID"];
	if (isset($_GET["categoryID"])) $_GET["categoryID"] = (int) $_GET["categoryID"];
	if (isset($_GET["countryID"])) $_GET["countryID"] = (int) $_GET["countryID"];
	if (isset($_GET["delete"])) $_GET["delete"] = (int) $_GET["delete"];
	if (isset($_GET["setting_up"])) $_GET["setting_up"] = (int) $_GET["setting_up"];

	//set Smarty include files dir
	$smarty->template_dir = "./templates";

	// several functions

	function mark_as_selected($a,$b) //required for excel import
	//returns " selected" if $a == $b
	{
		return !strcmp($a,$b) ? " selected" : "";

	} //mark_as_selected


	function get_NOTempty_elements_count($arr) //required for excel import
		//gets how many NOT NULL (not empty strings) elements are there in the $arr
	{
		$n = 0;
		for ($i=0;$i<count($arr);$i++)
			if (trim($arr[$i]) != "") $n++;
		return $n;
	} //get_NOTempty_elements_count


	//end of functions definition

	//define department and subdepartment
	if (!isset($_GET["dpt"]))
	{
		$dpt = isset($_POST["dpt"]) ? $_POST["dpt"] : "";
	}
	else $dpt = $_GET["dpt"];
	if (!isset($_GET["sub"]))
	{
		if (isset($_POST["sub"])) $sub = $_POST["sub"];
	}
	else $sub = $_GET["sub"];

	if (isset($_GET["safemode"])) //show safe mode warning
	{
		$smarty->assign("safemode", ADMIN_SAFEMODE_WARNING);
	}

	//define smarty template
	$smarty->assign("admin_main_content_template", "default.tpl.html");
	$smarty->assign("current_dpt", $dpt);

	//get new orders count
	$q = db_query("select count(*) from ".ORDERS_TABLE) or die (db_error());
	$n = db_fetch_row($q);
	$smarty->assign("new_orders_count", $n[0]);

	$admin_departments = array();

	// includes all .php files from includes/ dir
	$includes_dir = opendir("./includes/admin");
	$file_count = 0;
	while ( ($inc_file = readdir($includes_dir)) != false )
		if (strstr($inc_file,".php"))
		{
			include("./includes/admin/$inc_file");
			$file_count++;
		}

	if (isset($sub)) $smarty->assign("current_sub", $sub);
	$smarty->assign("admin_departments", $admin_departments);
	$smarty->assign("admin_departments_count", $file_count);

	//show Smarty output
	$smarty->display("backend/index.tpl.html"); 

?>