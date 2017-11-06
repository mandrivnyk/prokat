<?php
	//core file

	// -------------------------INITIALIZATION-----------------------------//

	//include core files
	include("./cfg/connect.inc.php");
	include("./includes/database/".DBMS.".php");
	include("./cfg/language_list.php");
	include("./core_functions/functions.php");
	include("./core_functions/category_functions.php");
	include("./core_functions/statistic_functions.php");
	include("./core_functions/registration_functions.php" );
	include("./core_functions/product_functions.php");
	include("./core_functions/datetime_functions.php");
	include("./core_functions/aux_pages_functions.php" );
	include("./core_functions/setting_functions.php" );
	include("./core_functions/picture_functions.php" );
	include("./core_functions/crypto/crypto_functions.php");

	session_start();

	MagicQuotesRuntimeSetting();

	//init Smarty
	require 'smarty/smarty.class.php'; 
	$smarty = new Smarty; //core smarty object

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

	//assign core Smarty variables
	//fetch currency types from database
	$q = db_query("select CID, Name, code, currency_value, where2show from ".CURRENCY_TYPES_TABLE." order by sort_order") or die (db_error());
	$currencies = array();
	while ($row = db_fetch_row($q))
	{
		$currencies[] = $row;
	}
	$smarty->assign("currencies", $currencies);
	$smarty->assign("currencies_count", count($currencies));
	if (isset($_SESSION["log"])) $smarty->assign("log", $_SESSION["log"]);



	//specify that this is a popup window
	$printable_version = true;
	$smarty->assign("printable_version", 1);
	
	//include core shopping cart routine
	if (isset($_GET["productID"]))
	{
		$productID = (int)$_GET["productID"];
		unset($categoryID);
		include("./includes/product_detailed.php");
	}
	else if (isset($_GET["show_price"]))
	{
		include("./includes/pricelist.php");
	}
	else if (isset($_GET["show_aux_page"]))
	{
		$show_aux_page = (int)$_GET["show_aux_page"];
		include("./includes/show_aux_page.php");
	}
	
	include("./includes/head.php");


	//show Smarty output
	$smarty->display("printable_version.tpl.html");

?>