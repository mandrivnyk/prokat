<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
// *****************************************************************************
// Purpose  add administrator login into database and set default address 
// Inputs   $admin_login - administrator login, $admin_pass - administrator password
// Remarks	this function is called by installation
// Returns	this function always returns true
function regRegisterAdmin( $admin_login, $admin_pass, $Update = false, $OldAdminLogin = '' )
{
	if($Update && $OldAdminLogin){
		
		$CustomerInfo = regGetCustomerInfo2($OldAdminLogin);
		if(is_array($CustomerInfo)){
			
			$sql = '
				UPDATE `'.CUSTOMERS_TABLE.'`
				SET Login="'.xEscapeSQLstring($admin_login).'",cust_password="'.cryptPasswordCrypt( $admin_pass, null ).'"
				WHERE customerID="'.xEscapeSQLstring($CustomerInfo['customerID']).'"
			';
			db_query($sql);
			return true;
		}
	}
	$admin_login	= TransformStringToDataBase( $admin_login );
	$admin_pass		= TransformStringToDataBase( $admin_pass );
	// $q_count = db_query( "SELECT COUNT(*) FROM  ".CUSTOMERS_TABLE." WHERE Login='".$admin_login."'" );
	// $count = db_fetch_row( $q_count );
	// $count = $count[0];
	$OldID = 
	db_query( "delete from ".CUSTOMERS_TABLE." where Login='".$admin_login."'" );

	if ( CONF_DEFAULT_CUSTOMER_GROUP=='0' )
		$custgroupID = "NULL";
	else		
		$custgroupID = CONF_DEFAULT_CUSTOMER_GROUP;

	$admin_pass = cryptPasswordCrypt( $admin_pass, null );

	$q = db_query( "select CID from ".CURRENCY_TYPES_TABLE );
	$currencyID = "NULL";
	if ( $currency = db_fetch_row($q) )
		$currencyID = $currency["CID"];

	db_query( "insert into ".CUSTOMERS_TABLE.
		" (Login, cust_password, Email, first_name,  middle_name, last_name, subscribed4news, ". 
		" 	custgroupID, addressID, reg_datetime, CID ) values ".
				"('".$admin_login."','".$admin_pass."', ".
						" '-', '-', '-', 0, $custgroupID, NULL, ".
						" '".get_current_time()."', ".$currencyID." )" );
	$errorCode = 0;
	$zoneID = "1";
	$state	= "";
	$countryID = "223";
	$defaultAddressID = regAddAddress( 
				"-", "-", 
				$countryID, 
				$zoneID, 
				$state, 
				"-", 
				"-", 
				"-", 
				$admin_login, 
				$errorCode );
	regSetDefaultAddressIDByLogin( $admin_login, $defaultAddressID );
	return true;
}


// *****************************************************************************
// Purpose
// Inputs   $login - login
// Remarks	
// Returns	true if login exists in database, false otherwise
function regIsRegister( $login )
{
	$login = TransformStringToDataBase( $login );
	$q=db_query("select count(Login) from ".CUSTOMERS_TABLE." where Login='".$login."'");
	$r = db_fetch_row($q);
	return  ( $r[0] != 0 );
}


// *****************************************************************************
// Purpose
// Inputs   $customerID - custmer ID 
// Remarks	
// Returns	false if customer does not exist, login - otherwise
function regGetLoginById( $customerID )
{
	$customerID = (int) $customerID;
	if ($customerID == 0) return false;

	$q = db_query("select Login from ".CUSTOMERS_TABLE." where customerID=".$customerID);
	if ( ($r=db_fetch_row($q)) )
 		return $r["Login"];
	else
		return false;
}


// *****************************************************************************
// Purpose
// Inputs   $login - login
// Remarks	
// Returns	false if customer does not exist, customer ID - otherwise
function regGetIdByLogin( $login )
{
	$q = db_query("select customerID from ".CUSTOMERS_TABLE.
			" where Login='".xEscapeSQLstring($login)."'");
	if (  ($r=db_fetch_row($q)) )
		return $r["customerID"];
	else
		return NULL;
}



// *****************************************************************************
// Purpose  authenticate user
// Inputs   $login - login, $password - password
// Remarks  if user is authenticated successfully then this function sets sessions variables,
//		update statistic, move cart content into DB 
// Returns	false if authentication failure, true - otherwise
function regAuthenticate($login, $password, $Redirect = true)
{
	$login		= TransformStringToDataBase( $login );
	$password	= TransformStringToDataBase( $password );
	$q = db_query("SELECT cust_password, CID, ActivationCode FROM ".CUSTOMERS_TABLE.
		" WHERE Login='".$login."'") or die (db_error());
	$row = db_fetch_row($q);

	if(CONF_ENABLE_REGCONFIRMATION && $row['ActivationCode']){
		
		if($Redirect)RedirectProtected(set_query('&act_customer=1&notact=1'));
		else return false;
	}
	if ($row && strlen( trim($login) ) > 0)
	{
		if ($row["cust_password"] == cryptPasswordCrypt($password, null) )
		{
			// set session variables
			$_SESSION["log"] 	= $login;
			$_SESSION["pass"] 	= cryptPasswordCrypt($password, null);

			
			$_SESSION["current_currency"] = $row["CID"];
			
			// update statistic
			stAddCustomerLog( $login );

			// move cart content into DB
			moveCartFromSession2DB();
			return true;
		}
		else
			return false;
	}
	else return false;
}



// *****************************************************************************
// Purpose  	sends password to customer email 
// Inputs   
// Remarks   
// Returns	true if success
function regSendPasswordToUser( $login, &$smarty_mail ){
	
	$sql = '
		SELECT Login, cust_password, Email FROM '.CUSTOMERS_TABLE.'
		WHERE (Login="'.xEscapeSQLstring($login).'" OR Email="'.xEscapeSQLstring($login).'")
		AND (ActivationCode="" OR ActivationCode IS NULL)
	';
	$q = db_query($sql) or die (db_error());
	if ($row = db_fetch_row($q)) //send password
	{
		$password = cryptPasswordDeCrypt( $row["cust_password"], null );
		$smarty_mail->assign( "user_pass", $password );
		$smarty_mail->assign( "user_login", $row['Login'] );
		$html = $smarty_mail->fetch("remind_password.txt");
		ss_mail($row["Email"], EMAIL_FORGOT_PASSWORD_SUBJECT, 
				$html, 
				"From: \"".CONF_SHOP_NAME."\"<".CONF_GENERAL_EMAIL.">\n".
					stripslashes(EMAIL_MESSAGE_PARAMETERS)."\nReturn-path: <".
						CONF_GENERAL_EMAIL.">");
		return true;		
	}
	else
		return false;
}


// *****************************************************************************
// Purpose  determine administrator user
// Inputs   $login - login
// Remarks  if user is authenticated successfully then this function sets sessions variables,
//		update statistic, move cart content into DB 
// Returns	false if authentication failure, true - otherwise
function regIsAdminiatrator( $login )
{
	return ( !strcmp($login, ADMIN_LOGIN) );
}



// *****************************************************************************
// Purpose	register new customer
// Inputs   
//				$login				- login
//				$cust_password		- password
//				$Email				- email
//				$first_name			- customer first name
//				$middle_name			- customer middle name
//				$last_name			- customer last name
//				$subscribed4news	- if 1 customer is subscribed to news
//				$additional_field_values - additional field values is array of item
//									"additional_field" is value of this field
//									key is reg_field_ID
// Remarks	
// Returns
function regRegisterCustomer( $login, $cust_password, $Email, $first_name, $middle_name, 
		$last_name, $subscribed4news, $additional_field_values
		, $affiliateLogin = ''
		)
{
	$login				= xEscapeSQLstring( $login );
	$cust_password		= xEscapeSQLstring( $cust_password );
	$Email				= xEscapeSQLstring( $Email );
	$first_name			= xEscapeSQLstring( $first_name );
	$middle_name			= xEscapeSQLstring( $middle_name );
	$last_name			= xEscapeSQLstring( $last_name );
	$affiliateLogin	= xEscapeSQLstring( $affiliateLogin);
	$affiliateID		= 0;
	
	if ($affiliateLogin){
		
		$sql = "
			SELECT customerID  FROM ".CUSTOMERS_TABLE."
			WHERE Login='{$affiliateLogin}'
		";
		list($affiliateID) = db_fetch_row(db_query($sql));
	}

	foreach( $additional_field_values as $key => $val)
		$additional_field_values[$key] = xEscapeSQLstring( $val );


	$q = db_query( "select CID from ".CURRENCY_TYPES_TABLE );
	$currencyID = db_fetch_row( $q );
	$currencyID = $currencyID[0];
	if ( $currencyID == null )
		$currencyID = "NULL";


	$cust_password = cryptPasswordCrypt( $cust_password, null );
	// add customer to CUSTOMERS_TABLE

	$custgroupID = CONF_DEFAULT_CUSTOMER_GROUP;
	if ( $custgroupID == 0 )
		$custgroupID = "NULL";

	/**
	 * Activation code
	 */
	$ActivationCode = '';
	if(CONF_ENABLE_REGCONFIRMATION){
		
		$CodeExists = true;
		while ($CodeExists) {
			
			$ActivationCode = generateRndCode(16);
			$sql = '
				SELECT 1 FROM '.CUSTOMERS_TABLE.'
				WHERE ActivationCode="'.xEscapeSQLstring($ActivationCode).'"
			';
			@list($CodeExists) = db_fetch_row(db_query($sql));
		}
	}
		
	$sql = "insert into ".CUSTOMERS_TABLE.
		"( Login, cust_password, Email, first_name,  middle_name, last_name, subscribed4news, reg_datetime, CID, custgroupID".
		", affiliateID".
		", ActivationCode)".
		"values( '".$login."', '".$cust_password."', '".$Email."', ".
		" '".$first_name."','".$middle_name."', '".$last_name."', '".$subscribed4news."', '".get_current_time()."', ".
			$currencyID.", ".$custgroupID.
			", {$affiliateID}".
			",'{$ActivationCode}' )";
	db_query($sql);

	// add additional values to CUSTOMER_REG_FIELDS_TABLE
	foreach( $additional_field_values as $key => $val )
		SetRegField($key, $login, $val["additional_field"]);

	$customerID = regGetIdByLogin($login);
	//db_query("update ".CUSTOMERS_TABLE." set addressID='".$addressID.
	//	"' where Login='".$login."'" );

	if ( $subscribed4news )
		subscrAddRegisteredCustomerEmail( $customerID );

	return true;
}


// *****************************************************************************
// Purpose	send notification message to email
// Inputs   
//				$login				- login
//				$cust_password		- password
//				$Email				- email
//				$first_name			- customer first name
//				$middle_name			- customer middle name
//				$last_name			- customer last name
//				$subscribed4news	- if 1 customer is subscribed to news
//				$additional_field_values - additional field values is array of item
//									"additional_field" is value of this field
//									key is reg_field_ID
//				$updateOperation	- 1 if customer info is updated, 0 
//								otherwise
// Remarks	
// Returns	
function regEmailNotification($smarty_mail, $login, $cust_password, $Email, $first_name, $middle_name, 
		$last_name, $subscribed4news, $additional_field_values, 
		$countryID, $zoneID, $state, $zip, $city, $address, $updateOperation )
{
	$user = array();
	$smarty_mail->assign( "login", $login );
	$smarty_mail->assign( "cust_password", $cust_password );
	$smarty_mail->assign( "first_name", $first_name );
	$smarty_mail->assign( "middle_name", $middle_name );
	$smarty_mail->assign( "last_name", $last_name );
	$smarty_mail->assign( "Email", $Email );
	$additional_field_values = GetRegFieldsValues( $login );
	$smarty_mail->assign( "additional_field_values", $additional_field_values );

	$addresses = regGetAllAddressesByLogin( $login );
	for( $i=0; $i<count($addresses); $i++ )
		$addresses[$i]["addressStr"] = regGetAddressStr( $addresses[$i]["addressID"] , true);
	$smarty_mail->assign( "addresses", $addresses );
	
	if(CONF_ENABLE_REGCONFIRMATION){
		
		$sql = '
			SELECT ActivationCode FROM '.CUSTOMERS_TABLE.'
			WHERE Login="'.xEscapeSQLstring($login).'" AND cust_password="'.xEscapeSQLstring(cryptPasswordCrypt($cust_password, null)).'"
		';
		@list($ActivationCode) = db_fetch_row(db_query($sql));
		
		$smarty_mail->assign('ActURL', CONF_FULL_SHOP_URL.(substr(CONF_FULL_SHOP_URL, strlen(CONF_FULL_SHOP_URL)-1,1)=='/'?'':'/').'index.php?act_customer=1&act_code='.$ActivationCode);
		$smarty_mail->assign('ActCode', $ActivationCode);
	}
	
	$html = $smarty_mail->fetch( "register_successful.txt" );
	ss_mail($Email,
		EMAIL_REGISTRATION,
		$html,
		"From: \"".CONF_SHOP_NAME."\"<".CONF_GENERAL_EMAIL.">\n".
			stripslashes(EMAIL_MESSAGE_PARAMETERS)."\nReturn-path: <".CONF_GENERAL_EMAIL.">");

}

// *****************************************************************************
// Purpose	get customer info
// Inputs   
//				$login				- login
//				$cust_password		- password
//				$Email				- email
//				$first_name			- customer first name
//				$middle_name			- customer middle name
//				$last_name			- customer last name
//				$subscribed4news	- if 1 customer is subscribed to news
//				$additional_field_values - additional field values is array of item
//									"additional_field" is value of this field
//									key is reg_field_ID
//				$updateOperation	- 1 if customer info is updated, 0 
//								otherwise
// Remarks	
// Returns	
function regGetCustomerInfo($login, & $cust_password, & $Email, & $first_name,& $middle_name, 
		& $last_name, & $subscribed4news, & $additional_field_values, 
		& $countryID, & $zoneID, & $state, & $zip, & $city, & $address )
{
	$q=db_query("select customerID, cust_password, Email, first_name,middle_name, last_name, ".
		" subscribed4news, custgroupID, addressID  from ".CUSTOMERS_TABLE.
		" where Login='".$login."'");
	$r = db_fetch_row($q);
	$cust_password		= cryptPasswordDeCrypt( $r["cust_password"], null );
	if (CONF_BACKEND_SAFEMODE)
		$row["Email"] = ADMIN_SAFEMODE_BLOCKED;
	else
		$Email = $r["Email"];
	$first_name			= $r["first_name"];
	$middle_name			= $r["middle_name"];
	$last_name			= $r["last_name"];
	$subscribed4news	= $r["subscribed4news"];
	$addressID			= $r["addressID"];
	$customerID			= $r["customerID"];
	$q=db_query("select countryID, zoneID, zip, state, city, address from ".
		CUSTOMER_ADDRESSES_TABLE." where customerID='".$customerID."'");
	$r=db_fetch_row($q);
	$countryID  = $r["countryID"];
	$zoneID		= $r["zoneID"];
	$zip		= $r["zip"];
	$state		= $r["state"];
	$city		= $r["city"];
	$address	= $r["address"];
	$additional_field_values = GetRegFieldsValues( $login );
}




// *****************************************************************************
// Purpose	get customer info
// Inputs   
// Remarks	
// Returns	
function regGetCustomerInfo2( $login )
{
	$q = db_query("select customerID, cust_password, Email, first_name,  middle_name, last_name, ".
		" subscribed4news, custgroupID, addressID, Login, ActivationCode from ".CUSTOMERS_TABLE.
		" where Login='".xEscapeSQLstring($login)."'");
	if ( $row=db_fetch_row($q) )
	{
		if ( $row["custgroupID"] != null )
		{
			$q = db_query("select custgroupID, custgroup_name, custgroup_discount, sort_order from ".
				CUSTGROUPS_TABLE." where custgroupID=".$row["custgroupID"] );
			$custGroup = db_fetch_row($q);
			$row["custgroup_name"] =$custGroup["custgroup_name"];
		}
		else
			$row["custgroup_name"] = "";
		$row["cust_password"] = cryptPasswordDeCrypt( $row["cust_password"], null );

		if (CONF_BACKEND_SAFEMODE)$row["Email"] = ADMIN_SAFEMODE_BLOCKED;

		$row["allowToDelete"]  = regVerifyToDelete( $row["customerID"] );
	}
	return $row;
}



// -----------------------------------------------

function regAddAddress( 
				$first_name, $middle_name, $last_name, $countryID, 
				$zoneID, $state, $zip, $city, 
				$address, $log, &$errorCode )
{
	$first_name = xEscapeSQLstring( $first_name );
	$middle_name = xEscapeSQLstring( $middle_name );
	$last_name	= xEscapeSQLstring( $last_name );
	$state		= xEscapeSQLstring( $state );
	$zip		= xEscapeSQLstring( $zip );
	$city		= xEscapeSQLstring( $city );
	$address	= xEscapeSQLstring( $address );

	$q = db_query("select count(zoneID) from ".ZONES_TABLE." where countryID=".$countryID);
	$r = db_fetch_row( $q );
	if ( $r[0] != 0 && $zoneID == 0 )
	{
		$errorCode = "zoneIncompatibleWithCountry";
		return false;
	}

	$customerID = regGetIdByLogin( $log );

	if ( $zoneID == 0 ) $zoneID = "NULL";
	$sql = "insert into ".CUSTOMER_ADDRESSES_TABLE.
		" ( first_name, middle_name, last_name, countryID, zoneID, zip, state, city, ".
				" address, customerID ) ".
		" values( '$first_name', '$middle_name', '$last_name', $countryID, $zoneID, '$zip', '$state', ".
			" '$city', '$address', $customerID )";
	db_query($sql);
	return db_insert_id();
}

function regUpdateAddress( $addressID,
				$first_name, $middle_name, $last_name, $countryID, 
				$zoneID, $state, $zip, $city, 
				$address, &$errorCode )
{
	$first_name = xEscapeSQLstring( $first_name );
	$middle_name = xEscapeSQLstring( $middle_name );
	$last_name	= xEscapeSQLstring( $last_name );
	$state		= xEscapeSQLstring( $state );
	$zip		= xEscapeSQLstring( $zip );
	$city		= xEscapeSQLstring( $city );
	$address	= xEscapeSQLstring( $address );

	if ( $zoneID == 0 ) $zoneID = "NULL";
	db_query("update ".CUSTOMER_ADDRESSES_TABLE.
		" set ".
		" first_name='$first_name', middle_name='$middle_name', last_name='$last_name', countryID=$countryID, ".
		" zoneID=$zoneID, zip='$zip', state='$state', ".
		" city='$city', address='$address' where addressID=$addressID" );	
	return true;
}

function redDeleteAddress( $addressID )
{
	db_query("update ".CUSTOMERS_TABLE." set addressID=NULL where addressID=$addressID ");
	db_query("delete from ".CUSTOMER_ADDRESSES_TABLE.
		" where addressID=$addressID" );
}


function regGetAddress( $addressID)
{
	if ( $addressID != null )
	{
		// $customerID
		$sql = "select first_name, middle_name, last_name, countryID, zoneID, zip, ".
						" state, city, address, customerID from ".
						CUSTOMER_ADDRESSES_TABLE.
						" where addressID=".xEscapeSQLstring($addressID);
		$q = db_query($sql);
		$row=db_fetch_row($q);
		return $row;
	}
	else
		return false;
}


function regGetAddressByLogin( $addressID, $login )
{
	$customerID = regGetIdByLogin( $login );
	$address = regGetAddress( $addressID );
	if ( (int)$address["customerID"] == (int)$customerID )
		return $address;
	else
		return false;
}


function regGetAllAddressesByLogin( $log )
{
	$customerID = regGetIdByLogin( $log );

	$customerID = (int) $customerID;
	if ($customerID == 0) return NULL;

	$q = db_query( "select addressID, first_name, middle_name, last_name, countryID, zoneID, zip, state, city, address ".
					" from ".
					CUSTOMER_ADDRESSES_TABLE." where customerID=$customerID " );
	$data = array();
	while( $row = db_fetch_row($q) )
	{

		if ( $row["countryID"] != null )
		{
			$q1=db_query("select country_name from ".COUNTRIES_TABLE.
				" where countryID=".$row["countryID"] );
			$country = db_fetch_row($q1);
			$row["country"] = $country[0];
		}
		else
			$row["country"] = "-";

		if ( $row["zoneID"] != null )
		{
			$q1 = db_query("select zone_name from ".ZONES_TABLE.
					" where zoneID=".$row["zoneID"] );
	 		$zone = db_fetch_row( $q1 );
			$row["state"] = $zone[0];
		}

		$data[] = $row;
	}
	return $data;
}

function regGetDefaultAddressIDByLogin( $log )
{
	$log = TransformStringToDataBase( $log );
	$q = db_query("select addressID from ".CUSTOMERS_TABLE." where Login='".$log."'");
	if ( $row = db_fetch_row( $q ) )
		return $row[0];
	else
		return null;
}

function regSetDefaultAddressIDByLogin( $log, $defaultAddressID )
{
	$log = TransformStringToDataBase( $log );
	db_query( "update ".CUSTOMERS_TABLE." set addressID=$defaultAddressID ".
		" where Login='".$log."'" );
}


function _testStrInvalidSymbol( $str )
{
	$res = strstr( $str, "'" );
	if ( is_string($res) )
		return false;

	$res = strstr( $str, "\\" );
	if ( is_string($res) )
		return false;

	$res = strstr( $str, '"' );
	if ( is_string($res) )
		return false;

	$res = strstr( $str, "<" );
	if ( is_string($res) )
		return false;

	$res = strstr( $str, ">" );
	if ( is_string($res) )
		return false;

	return true;
}

function _testStrArrayInvalidSymbol( $array )
{
	foreach( $array as $str )
	{
		$res = _testStrInvalidSymbol( $str );
		if ( !$res )
			return false;
	}
	return true;
}



// *****************************************************************************
// Purpose	verify address input data
// Inputs   
//				$first_name			- customer first name
//				$middle_name			- customer middle name
//				$last_name			- customer last name
//				$countryID			- country ID
//				$zoneID 
//				$state 
//				$zip 
//				$city 
//				$address
// Remarks	
// Returns	empty string if success, error message otherwise
function regVerifyAddress(	$first_name, 	$middle_name, $last_name,
							$countryID, $zoneID, $state, 
							$zip, $city, $address )
{
	$error = "";
	if ( trim($first_name) == "" ) $error = ERROR_INPUT_NAME;
	else
	if ( trim($middle_name) == "" ) $error = ERROR_INPUT_NAME;
	else
	if ( trim($last_name) == "" ) $error = ERROR_INPUT_NAME;
	
	
	else
	if ( CONF_ADDRESSFORM_ADDRESS == 0 && trim($address)=="")	$error = ERROR_INPUT_ADDRESS;

	/*$q = db_query("select count(*) from ".ZONES_TABLE." where countryID=$countryID");
	$r = db_fetch_row( $q );
	$countZone = $r[0];

	if ( $countZone != 0 )
	{
		$q = db_query("select count(*) from ".ZONES_TABLE." where zoneID=".$zoneID.
			"  AND countryID=$countryID");
		$r = db_fetch_row( $q );
		if ( $r[0] == 0  )
			$error = ERROR_ZONE_DOES_NOT_CONTAIN_TO_COUNTRY;
	}
	else if ($zoneID!=0) $error = ERROR_INPUT_STATE;*/

	return $error;
}

function regGetContactInfo( $login, &$cust_password, &$Email, &$first_name, &$middle_name, 
				&$last_name, &$subscribed4news, &$additional_field_values )
{
	$login = TransformStringToDataBase( $login );
	$q=db_query("select customerID, cust_password, Email, first_name,  middle_name, last_name, ".
		" subscribed4news, custgroupID, addressID  from ".CUSTOMERS_TABLE.
		" where Login='".$login."'");
	$row = db_fetch_row( $q );
	$cust_password				= cryptPasswordDeCrypt( $row["cust_password"], null );
	$Email						= ( $row["Email"] );
	$first_name					= ( $row["first_name"] );
	$middle_name					= ( $row["middle_name"] );
	$last_name					= ( $row["last_name"] );
	$subscribed4news			= $row["subscribed4news"];
	$additional_field_values	= GetRegFieldsValues($login);
}

function regVerifyContactInfo( $login, $cust_password1, $cust_password2, 
						$Email, $first_name, $middle_name, $last_name, $subscribed4news, 
						$additional_field_values )
{
	$error = "";
	if ( 
			!_testStrArrayInvalidSymbol( 
										array( $login, $cust_password1, $cust_password2 ) 
									) 
		)
		$error = ERROR_INVALID_SYMBOL_LOGIN_INFO;
	else
	if ( trim($login) == "" ) $error = ERROR_INPUT_LOGIN;
	else
	if (!(((ord($login)>=ord("a")) && (ord($login)<=ord("z"))) ||
			((ord($login)>=ord("A")) && (ord($login)<=ord("Z")))))
				$error = ERROR_LOGIN_SHOULD_START_WITH_LATIN_SYMBOL;
	else
	if ( $cust_password1 == "" ||  $cust_password2 == "" || $cust_password1 != $cust_password2 )
		$error = ERROR_WRONG_PASSWORD_CONFIRMATION;
	else
	if ( trim($first_name) == "" ) $error = ERROR_INPUT_NAME;
	else
	if ( trim($middle_name) == "" ) $error = ERROR_INPUT_NAME;
	else
	if ( trim($last_name) == "" ) $error = ERROR_INPUT_NAME;
	else
	if ( trim($Email) == "" ) $error = ERROR_INPUT_EMAIL;
	else if (!eregi("^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$", $Email) )
	{ //e-mail validation
		$error = ERROR_INPUT_EMAIL;
	}

	if (isset($_POST['affiliationLogin']))
	if ( !regIsRegister($_POST['affiliationLogin']) && $_POST['affiliationLogin'])
			$error = ERROR_WRONG_AFFILIATION;

	//aux fields
	foreach($_POST as $key => $val)
	{
		if (strstr($key,"additional_field_"))
		{
			$id = (int) str_replace("additional_field_","",$key);
			if (GetIsRequiredRegField($id) && strlen(trim($val))==0)
				$error = FEEDBACK_ERROR_FILL_IN_FORM;
		}
	}

	return $error;
}


function regUpdateContactInfo( $old_login, $login, $cust_password,
						$Email, $first_name,  $middle_name, $last_name, $subscribed4news, 
						$additional_field_values )
{
	$old_login		= TransformStringToDataBase( $old_login );
	$login			= TransformStringToDataBase( $login ); 
	$cust_password	= TransformStringToDataBase( $cust_password );
	$Email			= TransformStringToDataBase( $Email );
	$first_name		= TransformStringToDataBase( $first_name );
	$middle_name	= TransformStringToDataBase( $middle_name );
	$last_name		= TransformStringToDataBase( $last_name );
	db_query("update ".CUSTOMERS_TABLE."  set ".
			" Login = '".$login."', ".
			" cust_password = '".cryptPasswordCrypt( $cust_password, null )."', ".
			" Email = '".$Email."', ".
			" first_name = '".$first_name."', ".
			" middle_name = '".$middle_name."', ".
			" last_name = '".$last_name."', ".
			" subscribed4news = ".$subscribed4news." ".
			" where Login='".$old_login."'");
	foreach( $additional_field_values as $key => $val )
		SetRegField($key, $login, $val["additional_field"]);


	if (!strcmp($old_login, ADMIN_LOGIN)) //update administrator login (cfg/connect.inc.php)
	{
		$f = fopen("./cfg/connect.inc.php","w");
		$s = "<?php\n".
			 "		//database connection settings\n".
			 "		define('DBMS', '".DBMS."'); // database system\n".
			 "		define('DB_HOST', '".DB_HOST."'); // database host\n".
			 "		define('DB_USER', '".DB_USER."'); // username\n".
			 "		define('DB_PASS', '".DB_PASS."'); // password\n".
			 "		define('DB_NAME', '".DB_NAME."'); // database name\n".
			 "		define('ADMIN_LOGIN', '$login');  // administrator's login\n".
			 "		//database tables\n".
			 "		include(\"./cfg/tables.inc.php\");\n".
			 "?>";
		fputs($f,$s);
		fclose($f);
	}


	$customerID = regGetIdByLogin( $login );



	if ( $subscribed4news )
		subscrAddRegisteredCustomerEmail( $customerID );
	else
		subscrUnsubscribeSubscriberByEmail( base64_encode($Email) );
}


// *****************************************************************************
// Purpose	get address string by address ID
// Inputs   
// Remarks	
// Returns	
function regGetAddressStr( $addressID, $NoTransform = false ){
	
	$address = regGetAddress( $addressID,$NoTransform);
	if(!is_array($address))return '';
	
	// countryID, zoneID, zip, state
	$country = cnGetCountryById( $address["countryID"] );
	$country = $country["country_name"];
	if ( trim($address["state"]) == "" )
	{
		$zone = znGetSingleZoneById( $address["zoneID"] );
		$zone = $zone["zone_name"];
	}
	else{
		$zone = trim($address["state"]);
	}
	
	if(!$NoTransform){
		$address = xHtmlSpecialChars($address);
		$zone = xHtmlSpecialChars($zone);
		$country = xHtmlSpecialChars($country);
	}

	if ( $country != "" )
	{
		$strAddress = $address["first_name"]." ".$address["middle_name"]."  ".$address["last_name"];
		if (strlen($address["address"])>0) $strAddress .= "<br>".$address["address"];
		if (strlen($address["city"])>0) $strAddress .= "<br>".$address["city"];
		if (strlen($zone)>0) $strAddress .= "  ".$zone;
		if (strlen($address["zip"])>0) $strAddress .= "  ".$address["zip"];
		if (strlen($country)>0) $strAddress .= "<br>".$country;
	}
	else
	{
		$strAddress = $address["first_name"]." ".$address["middle_name"]."  ".$address["last_name"];
		if (strlen($address["address"])>0) $strAddress .= "<br>".$address["address"];
		if (strlen($address["city"])>0) $strAddress .= "<br>".$address["city"];
		if (strlen($zone)>0) $strAddress .= " ".$zone;
		if (strlen($address["zip"])>0) $strAddress .= " ".$address["zip"];
	}

	return $strAddress;
}


// *****************************************************************************
// Purpose	gets all customers 
// Inputs   
// Remarks	
// Returns	
function regGetCustomers( $callBackParam, &$count_row, $navigatorParams = null )
{
	if ( $navigatorParams != null )
	{
		$offset		= $navigatorParams["offset"];
		$CountRowOnPage	= $navigatorParams["CountRowOnPage"];
	}
	else
	{
		$offset = 0;
		$CountRowOnPage = 0;
	}

	$where_clause = "";
	if ( isset($callBackParam["Login"]) )
	{
		$callBackParam["Login"] = TransformStringToDataBase( $callBackParam["Login"] );
		$where_clause .= " Login LIKE '%".$callBackParam["Login"]."%' ";
	}
		
	if ( isset($callBackParam["first_name"]) )
	{
		$callBackParam["first_name"] = TransformStringToDataBase( $callBackParam["first_name"] );
		$callBackParam["first_name"] = str_replace('\\', '\\\\', $callBackParam["first_name"]);
		if ( $where_clause != "" ) $where_clause .= " AND ";
		$where_clause .= " first_name LIKE '%".$callBackParam["first_name"]."%' ";
	}

	if ( isset($callBackParam["middle_name"]) )
	{
		$callBackParam["middle_name"] = TransformStringToDataBase( $callBackParam["middle_name"] );
		$callBackParam["middle_name"] = str_replace('\\', '\\\\', $callBackParam["middle_name"]);
		if ( $where_clause != "" ) $where_clause .= " AND ";
		$where_clause .= " middle_name LIKE '%".$callBackParam["middle_name"]."%' ";
	}

	if ( isset($callBackParam["last_name"]) )
	{
		$callBackParam["last_name"] = TransformStringToDataBase( $callBackParam["last_name"] );
		$callBackParam["last_name"] = str_replace('\\', '\\\\', $callBackParam["last_name"]);
		if ( $where_clause != "" ) $where_clause .= " AND ";
		$where_clause .= " last_name LIKE '%".$callBackParam["last_name"]."%' ";
	}

	if ( isset($callBackParam["email"]) )
	{
		$callBackParam["email"] = TransformStringToDataBase( $callBackParam["email"] );
		if ( $where_clause != "" ) $where_clause .= " AND ";
		$where_clause .= " Email LIKE '%".$callBackParam["email"]."%' ";
	}
	
	if ( isset($callBackParam["groupID"]) )
	{
		if ( $callBackParam["groupID"] != 0 )
		{
			if ( $where_clause != "" ) $where_clause .= " AND ";
			$where_clause .= " custgroupID = ".$callBackParam["groupID"]." ";
		}
	}
	
	if ( isset($callBackParam["ActState"]) )
	{
		switch ($callBackParam["ActState"]){
			
			#activated
			case 1:
				if ( $where_clause != "" ) $where_clause .= " AND ";
				$where_clause .= " (ActivationCode='' OR ActivationCode IS NULL)";
				break;
			#not activated
			case 0:
				if ( $where_clause != "" ) $where_clause .= " AND ";
				$where_clause .= " ActivationCode<>''";
				break;
		}
	}
	
	if ( $where_clause != "" )
		$where_clause = " where ".$where_clause;


	$order_clause = "";
	if ( isset($callBackParam["sort"]) )
	{
		$order_clause .= " order by ".$callBackParam["sort"]." ";
		if ( isset($callBackParam["direction"]) )
		{
			if ( $callBackParam["direction"] == "ASC" )
				$order_clause .=  " ASC ";
			else
				$order_clause .=  " DESC ";
		}
	}
	

	
	$sql = "select customerID, Login, cust_password, Email, first_name,middle_name, last_name, subscribed4news, ".
		 " custgroupID, addressID, reg_datetime, ActivationCode ".
		 " from ".CUSTOMERS_TABLE." ".$where_clause." ".$order_clause;
	$q=db_query( $sql );
	$data = array();
	$i=0;//var_dump ($navigatorParams);
	while( $row=db_fetch_row($q) )
	{
		
		if ( ($i >= $offset && $i < $offset + $CountRowOnPage) || 
				$navigatorParams == null  )
		{
			$group = GetCustomerGroupByCustomerId( $row["customerID"] );
			$row["custgroup_name"] = $group["custgroup_name"];
			$row["allowToDelete"]  = regVerifyToDelete( $row["customerID"] );
			$row["reg_datetime"]  = format_datetime( $row["reg_datetime"] );
			$data[] = $row;
		}
		$i++;
	}
	$count_row = $i;
	return $data;
}


function regSetSubscribed4news( $customerID, $value )
{
	db_query( "update ".CUSTOMERS_TABLE." set subscribed4news = ".$value.
			" where customerID=".$customerID );
	if ($value > 0)
	{
		subscrAddRegisteredCustomerEmail($customerID);
	}
	else
	{
		subscrUnsubscribeSubscriberByCustomerId($customerID);
	}
}

function regSetCustgroupID( $customerID, $custgroupID )
{
	$customerID  = (int) $customerID;
	$custgroupID = (int) $custgroupID;
	db_query( "update ".CUSTOMERS_TABLE." set custgroupID=".$custgroupID.
			" where customerID=".$customerID );
}


// *****************************************************************************
// Purpose	 
// Inputs   
// Remarks	
// Returns	true if customer has address specified by $addressID
function regAddressBelongToCustomer( $customerID, $addressID )
{
	$customerID = (int) $customerID;
	if (!$customerID) return false;
	$addressID  = (int) $addressID;
	if (!$addressID) return false;

	$q_count = db_query( "select count(*) from ".CUSTOMER_ADDRESSES_TABLE.
		" where customerID=".$customerID." AND addressID=".$addressID );
	$count = db_fetch_row( $q_count );
	$count = $count[0];
	return ( $count != 0 );
}



// *****************************************************************************
// Purpose	 
// Inputs   
// Remarks	
// Returns	true if customer has address specified by $addressID
function regVerifyToDelete( $customerID )
{
	$customerID = (int) $customerID;
	if (!$customerID) return 0;

	$q = db_query( "select count(*) from ".CUSTOMERS_TABLE." where customerID=".$customerID );
	$row = db_fetch_row($q);

	if ( regIsAdminiatrator(regGetLoginById($customerID))  )
		return false;

	return ($row[0] == 1);
}


// *****************************************************************************
// Purpose	 
// Inputs   
// Remarks	
// Returns	true if customer has address specified by $addressID
function regDeleteCustomer( $customerID )
{
	if ( $customerID == null || trim($customerID) == ""  )
		return false;

	$customerID = (int) $customerID;
	if (!$customerID) return 0;

	if ( regVerifyToDelete( $customerID ) )
	{
		db_query( "delete from ".SHOPPING_CARTS_TABLE." where customerID=".$customerID ) or die (db_error());
		db_query( "delete from ".MAILING_LIST_TABLE." where customerID=".$customerID ) or die (db_error());
		db_query( "delete from ".CUSTOMER_ADDRESSES_TABLE." where customerID=".$customerID ) or die (db_error());
		db_query( "delete from ".CUSTOMER_REG_FIELDS_VALUES_TABLE." where customerID=".$customerID ) or die (db_error());
		db_query( "delete from ".CUSTOMERS_TABLE." where customerID=".$customerID ) or die (db_error());
		db_query( "update ".ORDERS_TABLE." set customerID=NULL where customerID=".$customerID ) or die (db_error());
		return true;
	}
	else
		return false;
}

function regActivateCustomer($_CustomerID){
	
	$sql = '
		UPDATE '.CUSTOMERS_TABLE.'
		SET ActivationCode = ""
		WHERE customerID="'.xEscapeSQLstring($_CustomerID).'"
	';
	db_query($sql);
}
?>