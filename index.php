<?php
$cache_enable =1;  //-------------------Включение -  1/отключение -  0 кеширования--------------------------------
//unset( $_SESSION );
//exit();
	
include("./includes/filtrBot.php");
include("./includes/statisticIP.php");

date_default_timezone_set('Europe/Kiev');
include('./resizeimage.inc.php');
session_start();
if ($cache_enable == 1)
{

	if(!isset($_POST['ComparisonHidden1'])
		&& !isset($_POST['ComparisonHidden2'])
		&& !isset($_GET['quick_register'])
		&& !isset($_GET['order2_shipping_quick'])
		&& !isset($_GET['order3_billing_quick'])
		&& !isset($_GET['order4_confirmation_quick'])
		&& !isset($_GET['order4_confirmation'])
		&& !isset($_GET['order3_billing'])
		&& !isset($_GET['order2_shipping'])
		&& !isset($_GET['register'])
		&& !isset($_GET['change_address'])
		&& !isset($_GET['discuss'])
        && !isset($_POST['shopping_cart'])
        && !isset($_POST['addproduct'])
        && !isset($_GET['addproduct'])
		&& !isset($_GET['shopping_cart']))
	{
		if($_SERVER['REQUEST_URI'] == '/')
			$request_uri = '/index.php';
		else
		{
			$request_uri = $_SERVER['REQUEST_URI'] ;
			$request_uri = str_replace('/', '--', $request_uri);
			$request_uri = substr_replace($request_uri, '/', 0, 2);
		}
//echo './cache'.$request_uri.'.cache';
		if (file_exists('./cache'.$request_uri.'.cache'))
		{
//echo '22';
		    // Читаем и выводим файл
			//echo 'PROCHITANO';
			//exit();
		    readfile('./cache'.$request_uri.'.cache');
			exit();
		    
		}
		else 
		{
			//echo './cache'.$request_uri.'.cache<br>';
			//echo 'NE PROCHITANO';
			//exit();
		}	
		ob_start();
		$DebugMode = false;
	}
}
//echo  'test';
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
	include("./core_functions/linkexchange_functions.php" );
	include("./core_functions/affiliate_functions.php" );
	include('./classes/xml2array.php');
	include('./classes/class.virtual.shippingratecalculator.php');
	include('./classes/class.virtual.paymentmodule.php');
	include('./classes/class.virtual.smsmail.php');
	include('./modules/smsmail/class.smsnotify.php');
	include('./classes/class.cart.php');

	MagicQuotesRuntimeSetting();
	//init Smarty
	require 'smarty/smarty.class.php';
	@$smarty = new Smarty; //core smarty object
	$smarty_mail = new Smarty; //for e-mails
	/*echo $smarty->caching;
	$smarty->caching = true;
	$smarty->cache_lifetime = 3600;
    $smarty->cache_dir = './templates_c' ;
	echo $smarty->caching;*/
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
$q = db_query("SET NAMES CP1251");
$q = db_query("SET COLLATION_CONNECTION=CP1251_GENERAL_CI");	
	db_select_db(DB_NAME) or die (db_error());
	settingDefineConstants();
	if ((int)CONF_SMARTY_FORCE_COMPILE) //this forces Smarty to recompile templates each time someone runs index.php
	{
		$smarty->force_compile = true;
		$smarty_mail->force_compile = true;
	}
	//authorized access check
	include("./checklogin.php");
	//# of selected currency
///	echo  ' $_SESSION[current_currency] ='.$_SESSION["current_currency"].'<br>' ;
	//echo  ' CONF_DEFAULT_CURRENCY = '.CONF_DEFAULT_CURRENCY.'<br>' ;
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
/*	echo '<pre>';
print_r($selected_currency_details);
echo '</pre>';*/
	//load all categories to array $cats to avoid multiple DB queries (frequently used in future - but not always!)
	$cats = array();
	$i=0;
	$q = db_query("SELECT categoryID, name, parent, products_count, description, picture FROM ".
			CATEGORIES_TABLE." where categoryID<>0 ORDER BY sort_order, name") or die (db_error());
	while ($row = db_fetch_row($q))
	{
		$cats[$i++] = $row;
	}
	//set $categoryID
	if (isset($_GET["categoryID"]) || isset($_POST["categoryID"]))
		$categoryID = isset($_GET["categoryID"]) ? (int)$_GET["categoryID"] : (int)$_POST["categoryID"];
	//else $categoryID = 79;
	//$productID
	if (!isset($_GET["productID"]))
	{
		if (isset($_POST["productID"])) $productID = (int) $_POST["productID"];
	}
	else $productID = (int) $_GET["productID"];
	//and different vars...
	if (isset($_GET["register"]) || isset($_POST["register"]))
		$register = isset($_GET["register"]) ? $_GET["register"] : $_POST["register"];
	if (isset($_GET["update_details"]) || isset($_POST["update_details"]))
		$update_details = isset($_GET["update_details"]) ? $_GET["update_details"] : $_POST["update_details"];
	if (isset($_GET["order"]) || isset($_POST["order"]))
		$order = isset($_GET["order"]) ? $_GET["order"] : $_POST["order"];
	if (isset($_GET["order_without_billing_address"]) || isset($_POST["order_without_billing_address"]))
		$order_without_billing_address = isset($_GET["order_without_billing_address"])?
				$_GET["order_without_billing_address"]:$_POST["order_without_billing_address"];
	if (isset($_GET["check_order"]) || isset($_POST["check_order"]))
		$check_order = isset($_GET["check_order"]) ? $_GET["check_order"] : $_POST["check_order"];
	if (isset($_GET["proceed_ordering"]) || isset($_POST["proceed_ordering"]))
		$proceed_ordering = isset($_GET["proceed_ordering"]) ? $_GET["proceed_ordering"] : $_POST["proceed_ordering"];
	if ( isset($_GET["update_customer_info"]) || isset($_POST["update_customer_info"]) )
		$update_customer_info = isset($_GET["update_customer_info"]) ? $_GET["update_customer_info"] : $_POST["update_customer_info"];
	if ( isset($_GET["show_aux_page"]) || isset($_POST["show_aux_page"]) )
		$show_aux_page = isset($_GET["show_aux_page"]) ? $_GET["show_aux_page"] : $_POST["show_aux_page"];
	if ( isset($_GET["visit_history"]) || isset($_POST["visit_history"]) )
		$visit_history = 1;
	if ( isset($_GET["order_history"]) || isset($_POST["order_history"]) )
		$order_history = 1;
	if ( isset($_GET["address_book"]) || isset($_POST["address_book"]) )
		$address_book = 1;
	if ( isset($_GET["address_editor"]) || isset($_POST["address_editor"]) )
		$address_editor = isset($_GET["address_editor"]) ? $_GET["address_editor"] : $_POST["address_editor"];
	if ( isset($_GET["add_new_address"]) || isset($_POST["add_new_address"]) )
		$add_new_address = isset($_GET["add_new_address"]) ? $_GET["add_new_address"] : $_POST["add_new_address"];
	if ( isset($_GET["contact_info"]) || isset($_POST["contact_info"])  )
		$contact_info = isset($_GET["contact_info"]) ? $_GET["contact_info"] : $_POST["contact_info"];
	if ( isset($_GET["comparison_products"]) || isset($_POST["comparison_products"]) )
		$comparison_products = 1;
	if ( isset($_GET["register_authorization"]) || isset($_POST["register_authorization"]) )
		$register_authorization = 1;
	if ( isset($_GET["page_not_found"]) || isset($_POST["page_not_found"])  )
		$page_not_found = 1;
	if ( isset($_GET["newes"]) || isset($_GET["newes"]) )
		$news = 1;
	if( isset($_GET["new"])  )
		$new = 1;
	if ( isset($_GET["quick_register"]) )
		$quick_register = 1;
	if ( isset($_GET["order2_shipping_quick"]) )
		$order2_shipping_quick = 1;
	if ( isset($_GET["order3_billing_quick"]) )
		$order3_billing_quick = 1;
	if ( isset($_GET["order2_shipping"]) || isset($_POST["order2_shipping"]) )
		$order2_shipping = 1;
	if ( isset($_GET["order3_billing"]) || isset($_POST["order3_billing"]) )
		$order3_billing = 1;
	if ( isset($_GET["change_address"]) || isset($_POST["change_address"]) )
		$change_address = 1;
	if ( isset($_GET["order4_confirmation"]) || isset($_POST["order4_confirmation"]) )
		$order4_confirmation = 1;
	if ( isset($_GET["order4_confirmation_quick"]) || isset($_POST["order4_confirmation_quick"]) )
		$order4_confirmation_quick = 1;
	if ( isset($_GET["order_detailed"]) || isset($_POST["order_detailed"]) )
		$order_detailed = isset($_GET["order_detailed"])?$_GET["order_detailed"]:$_POST["order_detailed"];
	if (!isset($_SESSION["vote_completed"])) $_SESSION["vote_completed"] = array();
	//checking for proper $offset init
	$offset = isset($_GET["offset"]) ? $_GET["offset"] : 0;
	if ($offset<0 || $offset % CONF_PRODUCTS_PER_PAGE) $offset = 0;
	// -------------SET SMARTY VARS AND INCLUDE SOURCE FILES------------//
	if (isset($productID)) //to rollout categories navigation table
	{
		$q = db_query("SELECT categoryID FROM ".PRODUCTS_TABLE." WHERE productID='$productID'") or die (db_error());
		$r = db_fetch_row($q);
		if ($r) $categoryID = $r[0];
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
	/*echo '<pre>';
	print_r($_SERVER);
	echo '</pre>';*/
	$smarty->assign("REQUEST_URI", $_SERVER['REQUEST_URI']);
	$smarty->assign("currencies", $currencies);
	$smarty->assign("currencies_count", count($currencies));
	$smarty->assign("lang_list", $lang_list);
	if (isset($_SESSION["current_language"])) $smarty->assign("current_language", $_SESSION["current_language"]);
	if (isset($_SESSION["log"])) $smarty->assign("log", $_SESSION["log"]);
	// - following vars are used as hidden in the customer survey form
	if ( isset($categoryID) )
		$smarty->assign("categoryID", $categoryID);
	if (isset($productID)) $smarty->assign("productID", $productID);
	if (isset($_GET["currency"])) $smarty->assign("currency", $_GET["currency"]);
	if (isset($_GET["user_details"])) $smarty->assign("user_details", $_GET["user_details"]);
	if (isset($_GET["aux_page"])) $smarty->assign("aux_page", $_GET["aux_page"]);
	if (isset($_GET["show_price"])) $smarty->assign("show_price", $_GET["show_price"]);
	
	if (isset($_GET['error']) && ($_GET['error'] == 404))$smarty->assign("error", 404);
	if (isset($_GET["adv_search"])) $smarty->assign("adv_search", $_GET["adv_search"]);
	if (isset($_GET["searchstring"])) $smarty->assign("searchstring", $_GET["searchstring"]);
	if (isset($register)) $smarty->assign("register", $register);
	if (isset($order)) $smarty->assign("order", $order);
	if (isset($check_order)) $smarty->assign("check_order", $check_order);
	//set defualt main_content template to homepage
	$smarty->assign("main_content_template", "home.tpl.html");
	//include all .php files from includes/ dir
	$includes_dir = opendir("./includes");
	$files = array();
	while ( ($inc_file = readdir($includes_dir)) != false )
		if (strstr($inc_file,".php"))
		{
			$files[] = $inc_file;
		}
	sort($files);
	foreach ($files as $fl)
	{
		include("./includes/".$fl);
	}
	//wrong password page
	if (isset($_GET["logging"]) || isset($show_password_form) || isset($wrongLoginOrPw))
	{
		if (isset($wrongLoginOrPw))
			$smarty->assign("wrongLoginOrPw", 1);
		$smarty->assign("main_content_template", "password.tpl.html");
	}
	// output:
	//security warnings!
	if (file_exists("./install.php"))
	{
		echo WARNING_DELETE_INSTALL_PHP;
	}
/*	else if (get_magic_quotes_gpc() == 0)
	{
		echo WARNING_MAGIC_QUOTES_GPC;
	}*/
	if (!is_writable("./temp") || !is_writable("./products_files") || !is_writable("./products_pictures") || !is_writable("./templates_c"))
	{
		echo WARNING_WRONG_CHMOD;
	}
	//show admin a administrative mode link
	//if (isset($_SESSION["log"]) && !strcmp($_SESSION["log"], ADMIN_LOGIN))
		//echo "<br><center><a href=\"admin.php\"><font color=red>".ADMINISTRATE_LINK."</font></a></center><p>";
	$aux_pages = auxpgGetAllPageAttributes();
	if ( count($aux_pages) != 0 )
		$smarty->assign( "aux_page1", $aux_pages[0] );
	if ( count($aux_pages) > 1 )
		{
			$smarty->assign( "aux_page2", $aux_pages[1] );
			$smarty->assign( "aux_page3", $aux_pages[2] );
			$smarty->assign( "aux_page4", $aux_pages[3] );
		}



		if(isset($_POST["shopping_cart"]) || isset($_GET["shopping_cart"])) {
	     $cart = new Cart($smarty, $smarty_mail);
         $cart->shoppingCart();
        }
/*$a2 = getmicrotime();
$diff = $a2 - $a1;
echo "shop-script core: ".$diff;
*/
/*echo $offset;
echo '<pre>';
	print_r($_GET);
echo '</pre>';*/
//echo $offset;
//------------------------------ Вытягиваем товары для модуля превь товаров на главной - ТЕРМОБЕЛЬЕ ---------------------------
		/*$q = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID = 275 AND t1.enabled =1 ORDER BY RAND() LIMIT 0 , 6" ) or die (db_error());
		while ($row = db_fetch_row($q))
		{
			$termobelie[] = $row;
		}
		$smarty->assign( "CloudTags", $CloudTags);*/
		/*echo '<pre>';
			print_r($termobelie);
		echo '</pre>';*/
		//echo '11';
//-------------------------------Убираем ошибку кодировки------------------------------------------------------------------------


//-------------------------------------------------------------------------------------------------------
		/*$q = db_query("SELECT * FROM ".PRODUCT_OPTIONS_VALUES_TABLE." ") or die (db_error());
					
					while ($row = db_fetch_row($q))
					{
					   // $pos = substr_count("?", $row['option_value']);
					   if(preg_match('/[\?]/ui', $row['option_value']))
					    {
					        echo '<pre>';
                    			print_r($row);
                    		echo '</pre>';
					        $q1 = db_query("SELECT option_value FROM ".PRODUCT_OPTIONS_VALUES_TABLE1." WHERE 	optionID=".$row['optionID']." AND 	productID=".$row['productID']."" ) or die (db_error());
					        $row1 = db_fetch_row($q1);
					        echo '<pre>';
                    			print_r($row1);
                    		echo '</pre>';
					        
					        //UPDATE `tatonka`.`SS_product_options_values` SET `option_value` = '91% полиэстер, 9% лайкра' WHERE `SS_product_options_values`.`optionID` =45 AND `SS_product_options_values`.`productID` =2579;
                            if(isset($row1['option_value']))
                            {
                               // echo 'NOVAYA ZAPISJ';
					          $q2 = db_query("UPDATE ".PRODUCT_OPTIONS_VALUES_TABLE." SET  `option_value`='".$row1['option_value']."' WHERE 	optionID=".$row['optionID']." AND 	productID=".$row['productID']."" ) or die (db_error());
					         
                            }
    						
					       $q3 = db_query("SELECT * FROM ".PRODUCT_OPTIONS_VALUES_TABLE." WHERE 	optionID=".$row['optionID']." AND 	productID=".$row['productID']."" ) or die (db_error());
					        $row3 = db_fetch_row($q3);
					        echo '<pre>';
                    			print_r($row3);
                    		echo '</pre>';
					       
					       echo '<hr>';
					    }
					}*/
					/*echo '<pre>';
			print_r($CloudTags1);
		echo '</pre>';*/
		

//-------------------------------------------------------------------------------------------------------
		$q = db_query("SELECT productID, producer, title_one,url_name, name, enabled FROM ".PRODUCTS_TABLE." WHERE  enabled <> 0 ORDER BY RAND() LIMIT 0,50") or die (db_error());
		$CloudTags1 = array();
					while ($rowCloud = db_fetch_row($q))
					{
						$CloudTags1[] = $rowCloud;
					}
					/*echo '<pre>';
			print_r($CloudTags1);
		echo '</pre>';*/
		$smarty->assign("CloudTags1", $CloudTags1);
//------------------------------ Вытягиваем товары для модуля Случайный товар ---------------------------
		/*$q = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID <> 283 AND t1.enabled =1 ORDER BY RAND() LIMIT 0 , 4" ) or die (db_error());
		//echo "SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID  ORDER BY RAND() LIMIT 0 , 4";
		while ($row = db_fetch_row($q))
		{
			$rand_prod[] = $row;
		}
		$smarty->assign( "rand_prod", $rand_prod);*/
		/*echo '<pre>';
			print_r($rand_prod);
		echo '</pre>';*/
		//echo '11';
//-------------------------------------------------------------------------------------------------------
//------------------------------ Вытягиваем товары для модуля Акционный товар ---------------------------
		$q = db_query("SELECT productID FROM ".CATEGORIY_PRODUCT_TABLE." t1 WHERE t1.categoryID =345 ORDER BY RAND() LIMIT 0,4");
		//t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID <> 283 AND t1.enabled =1 ORDER BY RAND() LIMIT 0 , 4" ) or die (db_error());
		//$q = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID <> 283 AND t1.enabled =1 ORDER BY RAND() LIMIT 0 , 4" ) or die (db_error());
		//echo "SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID  ORDER BY RAND() LIMIT 0 , 4";
		while ($row = db_fetch_row($q))
		{
			$q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.productID =".$row['productID']." AND t1.enabled =1 "  ) or die (db_error());
			$rand_prod_action[] = db_fetch_row($q1);
			//echo $row['productID'].'<br>';
			/*echo '<pre>';
			print_r($row);
			echo '</pre>';*/
		}
if(isset($rand_prod_action)){
		$smarty->assign( "discont", $rand_prod_action);
}

		/*echo '<pre>';
			print_r($rand_prod_action);
		echo '</pre>';*/
		//echo '11';
//-------------------------------------------------------------------------------------------------------
//------------------------------ Вытягиваем товары Новые предложения ---------------------------
		$q = db_query("SELECT productID FROM ".CATEGORIY_PRODUCT_TABLE." t1 WHERE t1.categoryID =433 ORDER BY RAND() LIMIT 0,4");
		//t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID <> 283 AND t1.enabled =1 ORDER BY RAND() LIMIT 0 , 4" ) or die (db_error());
		//$q = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.categoryID <> 283 AND t1.enabled =1 ORDER BY RAND() LIMIT 0 , 4" ) or die (db_error());
		//echo "SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID  ORDER BY RAND() LIMIT 0 , 4";
		$new_pred = array();
		while ($row = db_fetch_row($q))
		{
			$q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.productID =".$row['productID']." AND t1.enabled =1"  ) or die (db_error());
			$new_pred[] = db_fetch_row($q1);
			//echo $row['productID'].'<br>';
			/*echo '<pre>';
			print_r($row);
			echo '</pre>';*/
		}
		$smarty->assign( "termobelie", $new_pred);
		/*echo '<pre>';
			print_r($rand_prod_action);
		echo '</pre>';*/
		//echo '11';
//-------------Определяем является ли страница не первая из пейджера или это страница "пакзать все", если да, то взводим флажок и далее в head.tpl.html добавляем мета тек nofollow, noindex------
$offset_present = substr_count($_SERVER['REQUEST_URI'], 'offset');
//echo '$offset_present = '.$offset_present.'<br>';
$show_all_present = substr_count($_SERVER['REQUEST_URI'], 'show_all');
$sort_present = substr_count($_SERVER['REQUEST_URI'], 'sort=');
//echo '$show_all_present = '.$show_all_present.'<br>';
if(($offset_present >0) || ($show_all_present >0) || ($sort_present >0))
{
	$nofolow = 1;
	//echo 'offsett or show_all';
	$smarty->assign( "nofollow", $nofolow );
	
}
//-------------------------------------------------------------------------------------------------------
if($offset == '0')
	$offset = '';
if(isset($_GET['sort']))
	$offset .=' '.$_GET['sort'].' '.$_GET['direction'];
	$smarty->assign( "offset", $offset );
if($_SERVER['REQUEST_URI'] == '/index.php' || $_SERVER['REQUEST_URI'] == '/')
{
	$smarty->assign( "first", 1 );
}
if(isset($_GET['discuss']) && isset($_GET['discuss']) == 'yes' )
{
	$smarty->assign( "discuss_part", ' обсуждение ' );
}
	//show Smarty output
if(isset($_POST['ComparisonHidden1']) || isset($_POST['ComparisonHidden2']))
{
	$smarty->display("comparison_products.tpl.html");
}
else
	$smarty->display("index.tpl.html");
/*
$a3 = getmicrotime();
$diff = $a3 - $a2;
echo "smarty->display: ".$diff;
*/
//------------------------------- Подтягиваем страницу из кеша---------------------------------------
if ($cache_enable == 1)
{
	if(!isset($_POST['ComparisonHidden1'])
		&& !isset($_POST['ComparisonHidden2'])
		&& !isset($_GET['quick_register'])
		&& !isset($_GET['order2_shipping_quick'])
		&& !isset($_GET['order3_billing_quick'])
		&& !isset($_GET['order4_confirmation_quick'])
		&& !isset($_GET['order4_confirmation'])
		&& !isset($_GET['order3_billing'])
		&& !isset($_GET['order2_shipping'])
		&& !isset($_GET['register'])
		&& !isset($_GET['change_address'])
		&& !isset($_GET['discuss']))
	{
		$buffer = ob_get_contents();
		$request_uri = str_replace('/', '--', $request_uri); //--- замена во всей строке
		$request_uri = substr_replace($request_uri, '/', 0, 2); //----pervie 2 simvola pomenyaet na /
//echo '$request_uri ='.$request_uri;
//exit();
		$fp = fopen('./cache'.$request_uri.'.cache', 'w');
		fwrite($fp, $buffer);
		fclose($fp);
		ob_end_clean();
		echo $buffer;
	} 
}
//--------------------------------------------------------------------------------------------------------
 
?>
