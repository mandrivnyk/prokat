<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
    if ( isset($contact_info) && isset($_SESSION["log"]) )
	{


		// *****************************************************************************
		// Purpose	copies data from $_POST variable to HTML page
		// Inputs     		$smarty - smarty object
		// Remarks		
		// Returns	nothing
		function _copyDataFromPostToPage( & $smarty )
		{
			$smarty->xassign("login", $_POST["login"] );
			$smarty->xassign("cust_password1", $_POST["cust_password1"] );
			$smarty->xassign("cust_password2", $_POST["cust_password2"] );
			$smarty->xassign("first_name", $_POST["first_name"] );
			$smarty->xassign("last_name", $_POST["last_name"] );
			$smarty->xassign("email", $_POST["email"] );
			$smarty->assign("subscribed4news", (isset($_POST["subscribed4news"])?1:0) );

			$additional_field_values = array();
			$data = ScanPostVariableWithId( array( "additional_field" ) );
			foreach( $data as $key => $val )
			{
				$item = array( "reg_field_ID" => $key, "reg_field_name" => "", 
					"reg_field_value" => $val["additional_field"] );
				$additional_field_values[] = $item;
			}
			$smarty->xassign("additional_field_values", $additional_field_values );
		}


		// *****************************************************************************
		// Purpose	copies data from DataBase variable to HTML page
		// Inputs     		$smarty - smarty object
		//					$log - customer login
		// Remarks		
		// Returns	nothing
		function _copyDataFromDataBaseToPage( & $smarty, $log )
		{
			$cust_password = 0; $Email = 0;			$first_name = 0;
			$last_name = 0;		$subscribed4news=0; $additional_field_values = 0;
			$countryID = 0;		$zoneID = 0;		$state = 0; 
			$zip	   = 0;		$city	= 0;		$address = 0;
			regGetContactInfo( $log, $cust_password, $Email, $first_name, 
				$last_name, $subscribed4news, $additional_field_values );
			
			$smarty->hassign("login", $log );
			$smarty->hassign("cust_password1", $cust_password );
			$smarty->hassign("cust_password2", $cust_password );
			$smarty->hassign("first_name", $first_name );
			$smarty->hassign("last_name", $last_name );
			$smarty->hassign("email", $Email );
			$smarty->hassign("subscribed4news", $subscribed4news );
			$smarty->hassign("additional_field_values", $additional_field_values );
		}



		if ( isset($_POST["login"]) )
			_copyDataFromPostToPage( $smarty );
		else
			_copyDataFromDataBaseToPage( $smarty, $_SESSION["log"] );

		if ( isset($_POST["save"]) )
		{
			$login				= $_POST["login"];
			$cust_password1		= $_POST["cust_password1"];
			$cust_password2		= $_POST["cust_password2"];
			$first_name			= $_POST["first_name"];
			$last_name			= $_POST["last_name"];
			$Email				= $_POST["email"];
			$subscribed4news	= ( isset($_POST["subscribed4news"]) ? 1 : 0 );
			$additional_field_values = ScanPostVariableWithId( array( "additional_field" ) );
			if ( ( trim($login) != trim($_SESSION["log"]) ) && regIsRegister($login) )
				$error = ERROR_USER_ALREADY_EXISTS;
			if ( !isset($error) )
				$error = regVerifyContactInfo( $login, $cust_password1, $cust_password2, 
						$Email, $first_name, $last_name, $subscribed4news, 
						$additional_field_values );

			if ( $error == "" ) unset($error);

			if ( !isset($error) )
			{
				regUpdateContactInfo( $_SESSION["log"], $login, $cust_password1,
						$Email, $first_name, $last_name, $subscribed4news, 
						$additional_field_values );
				$_SESSION["log"]	= $login;
				$_SESSION["pass"]	= cryptPasswordCrypt($cust_password1, null);
				Redirect( "index.php?user_details=yes" );
			}
			else
				$smarty->assign( "error", $error );
		}

		// additional fields
		$additional_fields=GetRegFields();
		$smarty->assign("additional_fields", $additional_fields );		
		$smarty->assign("main_content_template", "contact_info.tpl.html");
	}
?>