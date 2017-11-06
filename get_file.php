<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
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

	session_start();

	require 'smarty/smarty.class.php'; 
	$smarty_mail = new Smarty; //for e-mails

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
	db_select_db(DB_NAME) or die (db_error());

	settingDefineConstants();

	//get order data
	if ( isset($_GET["getFileParam"]) )
	{
			$getFileParam = cryptFileParamDeCrypt( $_GET["getFileParam"], null );//echo $getFileParam;
			$params = explode( "&", $getFileParam );
			foreach( $params as $param )
			{
				$param_value = explode( "=", $param );

				if ( count($param_value) >= 2 )
				{
					if ( $param_value[0] == "orderID" )
						$orderID = (int)$param_value[1];
					else if ( $param_value[0] == "productID" )
						$productID = (int)$param_value[1];
					else if ( $param_value[0] == "customerID" )
						$customerID = (int)$param_value[1];
					else if ( $param_value[0] == "order_time" )
						$order_time = base64_decode($param_value[1]);
				}
			}

	}

	if ( isset($_POST["remind_password"]) )
	{
		regSendPasswordToUser( $_POST["login_to_remind_password"], $smarty_mail );
	}

	$authenticateError = false;
	if ( isset($_POST["submitLoginAndPassword"]) )
	{
		$authenticateError = !regAuthenticate($_POST["login"],$_POST["password"]);
	}

	//authorized login check
	include("./checklogin.php");

	if ( !isset($customerID) ) $customerID = 0;

	if (!isset($_SESSION["log"]) && $customerID!=-1 && !CONF_BACKEND_SAFEMODE) //unauthorized
	{
?>
		<form name='MainForm' method=POST>
			<table>
	<?php
				if ( $authenticateError )
				{	
	?>
				<tr>
					<td colspan=2>
						<font color=red>
							<b><?php echo ERROR_WRONG_PASSWORD;?></b>
						</font>
					</td>
				</tr>
	<?php
				}
	?>

				<tr>
					<td colspan=2>
						<font class=middle>
							<b><?php echo STRING_AUTHORIZATION;?></b>
						</font>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo CUSTOMER_LOGIN;?>
					</td>
					<td>
						<input type=text name='login'>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo CUSTOMER_PASSWORD;?>
					</td>
					<td>
						<input type=password name='password'>
					</td>
				</tr>
				<tr>
					<td>
						<input type=submit name='submitLoginAndPassword' value='<?php echo OK_BUTTON;?>'>
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
		<?php
				if (  isset($_POST["remind_password"])  )
				{
		?>
					<tr>
						<td colspan=2>
							<b><?php echo STRING_PASSWORD_SENT;?></b>
						</td>
					</tr>
		<?php
				}
		?>
				<?php 
					if ( $authenticateError )
					{	
				?>
						<tr>
							<td colspan=2>
								&nbsp;
							</td>
						</tr>
						<tr>
							<td>
								<?php echo STRING_FORGOT_PASSWORD_FIX;?>
							</td>
							<td>
								<input type=text name='login_to_remind_password' value=''>
							</td>
						</tr>
						<tr>
							<td>
								<input type=submit name='remind_password' value='<?php echo OK_BUTTON;?>'>
							</td>
						</tr>
				<?php
					}
				?>
			</table>
		</form>
<?php
	}
	else
	{

		$fileToDownLoad = "";
		$fileToDownLoadShortName = "";
		$res = 0;

		if ( !isset($_GET["getFileParam"]) )
			die( ERROR_FORBIDDEN );
		else
		{
			$getFileParam = cryptFileParamDeCrypt( $_GET["getFileParam"], null );
	 		if ( $getFileParam == "GetDataBaseSqlScript" )
			{
				if ( CONF_BACKEND_SAFEMODE != 1 && (strcmp($_SESSION["log"],ADMIN_LOGIN) != 0 ))
					die( ERROR_FORBIDDEN );
				else
				{
					$fileToDownLoad = "./temp/database.sql";
					$fileToDownLoadShortName = "database.sql";
				}
			}
			else if ( $getFileParam == "GetCustomerExcelSqlScript" )
			{
				if ( CONF_BACKEND_SAFEMODE != 1 && (strcmp($_SESSION["log"], ADMIN_LOGIN) != 0 ))
					die( ERROR_FORBIDDEN );
				else
				{
					$fileToDownLoad = "./temp/customers.csv";
					$fileToDownLoadShortName = "customers.csv";
				}
			}
			else if ( $getFileParam == "GetFroogleFeed" )
			{
				if ( CONF_BACKEND_SAFEMODE != 1 && (strcmp($_SESSION["log"], ADMIN_LOGIN) != 0 ))
					die( ERROR_FORBIDDEN );
				else
				{
					$fileToDownLoad = "./temp/froogle.txt";
					$fileToDownLoadShortName = "froogle.txt";
				}
			}
			else if ( $getFileParam == "GetCSVCatalog" )
			{
				if ( CONF_BACKEND_SAFEMODE != 1 && (strcmp($_SESSION["log"], ADMIN_LOGIN) != 0 ))
					die( ERROR_FORBIDDEN );
				else
				{
					$fileToDownLoad = "./temp/catalog.csv";
					$fileToDownLoadShortName = "catalog.csv";
				}
			}
			else if ( $getFileParam == "GetSubscriptionsList" )
			{
				if ( CONF_BACKEND_SAFEMODE != 1 && (strcmp($_SESSION["log"], ADMIN_LOGIN) != 0 ))
					die( ERROR_FORBIDDEN );
				else
				{
					$fileToDownLoad = "./temp/subscribers.txt";
					$fileToDownLoadShortName = "subscribers.txt";
				}
			}
			else{
				
				$params = explode( "&", $getFileParam );
				foreach( $params as $param )
				{
					$param_value = explode( "=", $param );

					if ( count($param_value) >= 2 )
					{

						if ( $param_value[0] == "orderID" )
							$orderID = (int)$param_value[1];
						else if ( $param_value[0] == "productID" )
							$productID = (int)$param_value[1];
						else if ( $param_value[0] == "customerID" )
							$customerID = (int)$param_value[1];
						else if ( $param_value[0] == "order_time" )
							{
								for ($k = 2; $k<count($param_value); $k++)
								{
									$param_value[1] .= $param_value[$k]."=";

								}
								$order_time = base64_decode($param_value[1]);
							}

					}

				}

				if (isset($orderID) && isset($productID))
				{
					$res = ordAccessToLoadFile( $orderID, $productID, $pathToProductFile, $fileToDownLoadShortName );
				}
				else
					$res = 4;

				if ($customerID == -1 && isset($order_time)) //verify order time
				{
					$q = db_query("select order_time from ".ORDERS_TABLE." where orderID=$orderID");
					$row = db_fetch_row($q);
					if (!$row || strcmp($row[0],$order_time))
					{
						$res = 4;
					}
				}
				else if ($customerID == -1) $res = 4;

				if ( $res == 0 )
					$fileToDownLoad = $pathToProductFile;
			}
		}

		if ( $res == 0 && strlen($fileToDownLoad)>0 && file_exists($fileToDownLoad) )
		{
			header('Content-type: application/force-download');
			header('Content-Transfer-Encoding: Binary');
			header('Content-length: '.filesize($fileToDownLoad));
			header('Content-disposition: attachment; filename='.basename($fileToDownLoad) );
			readfile($fileToDownLoad);
		}
		else if ( $res == 1 )
			echo( "<font color=red><b>".STRING_COUNT_DOWNLOAD_IS_EXCEEDED_EPRODUCT_DOWNLOAD_TIMES."</b></font>" );
		else if ( $res == 2 )
			echo( "<font color=red><b>".STRING_AVAILABLE_DAYS_ARE_EXHAUSTED_TO_DOWNLOAD_PRODUCT."</b></font>" );
		else if ( $res == 3 )
			echo( "<font color=red><b>".ERROR_FORBIDDEN_TO_ACCESS_FILE_ORDER_IS_NOT_PAYED."</b></font>" );
		else //if ( $res == 4 )
			echo( "<font color=red><b>".ERROR_FORBIDDEN."</b></font>" );
	}

 ?>