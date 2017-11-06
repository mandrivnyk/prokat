<?php
    if ( (isset($address_editor) || isset($add_new_address)) && isset($_SESSION["log"]) )
	{
		$_POST = xStripSlashesGPC($_POST);
		$errorCode = "";
		$countryID = CONF_DEFAULT_COUNTRY;

		// *****************************************************************************
		// Purpose	copies data from $_POST variable to HTML page
		// Inputs     		$smarty - smarty object
		// Remarks		
		// Returns	nothing
		function _copyDataFromPostToPage( &$smarty )
		{
			$smarty->hassign("first_name", $_POST["first_name"] );
			$smarty->hassign("last_name", $_POST["last_name"] );
			$smarty->hassign("countryID", $_POST["countryID"] );
			$smarty->hassign("zoneID", $_POST["zoneID"] );
			$smarty->hassign("state", $_POST["state"] );
			$smarty->hassign("zip", $_POST["zip"] );
			$smarty->hassign("city", $_POST["city"] );
			$smarty->hassign("address", $_POST["address"] );
			$zones = znGetZonesById( $_POST["countryID"] );
			$smarty->hassign( "zones", $zones );
		}

		// *****************************************************************************
		// Purpose	copies data from DataBase variable to HTML page
		// Inputs     		$smarty - smarty object
		//					$log - customer login
		// Remarks		
		// Returns	nothing
		function _copyDataFromDataBaseToPage( &$smarty, $addressID )
		{
			if ( !isset($_SESSION["log"]) )
				Redirect("index.php?page_not_found=yes");
			$address = regGetAddressByLogin( $addressID, $_SESSION["log"] );
			if ( $address === false )
				Redirect("index.php?page_not_found=yes");
			else
			{
				$smarty->hassign("first_name", $address["first_name"] );
				$smarty->hassign("last_name", $address["last_name"] );
				$smarty->hassign("countryID", $address["countryID"] );
				$smarty->hassign("zoneID", $address["zoneID"] );
				$smarty->hassign("state", $address["state"] );
				$smarty->hassign("zip", $address["zip"] );
				$smarty->hassign("city", $address["city"] );
				$smarty->hassign("address", $address["address"] );
				$zones = znGetZonesById( $address["countryID"] );
				$smarty->hassign("zones", $zones );
			}
		}


		if ( !isset($_POST["zoneID"])  )	$_POST["zoneID"]	=	0;
		if ( !isset($_POST["state"])  )		$_POST["state"]		=	"";

		if ( isset($add_new_address) )
			$smarty->assign("add_new_address", 1 );
		if ( isset($address_editor) )
			$smarty->assign("address_editor", $address_editor );

		if ( isset($_POST["first_name"]) )
			_copyDataFromPostToPage( $smarty );
		else if ( isset($address_editor) )
			{
				$address_editor = (int) $address_editor;
				_copyDataFromDataBaseToPage( $smarty, $address_editor );
			}
			else
			{
				$zones = znGetZonesById( $countryID );
				$smarty->hassign("zones", $zones );
			}

		if ( isset($_POST["save"]) )
		{
			$first_name = $_POST["first_name"];
			$last_name  = $_POST["last_name"];
			$countryID  = $_POST["countryID"];
			$zoneID		= $_POST["zoneID"];
			$state		= $_POST["state"];
			$zip		= $_POST["zip"];
			$city		= $_POST["city"];
			$address	= $_POST["address"];

			$error		= regVerifyAddress(	$first_name, $last_name,
										$countryID, $zoneID, $state, 
										$zip, $city, $address );
			if ( $error == "" ) unset( $error );
			else $smarty->assign("error", $error );

			if ( !isset($error) )
			{
				//regTransformAddressToSafeForm(
				//			&$first_name, &$last_name,
				//			&$countryID, &$zoneID, &$state, 
				//			&$zip, &$city, &$address );

				if ( isset($add_new_address) )
				{
					regAddAddress( 
						$first_name, $last_name, $countryID, 
						$zoneID, $state, $zip, $city, 
						$address, $_SESSION["log"], $errorCode );
					header("Location: index.php?address_book=yes");	
				}
				else if ( isset($address_editor) )
				{
					regUpdateAddress( $address_editor,
						$first_name, $last_name, $countryID, 
						$zoneID, $state, $zip, $city, 
						$address, $errorCode );
					header("Location: index.php?address_book=yes");
				}
			}
		}
		else //show 'select zone' statement
		{
			if (isset($_POST["first_name"]))
				$smarty->assign("select_zone_statement", ERROR_ZONE_DOES_NOT_CONTAIN_TO_COUNTRY);
		}

		$callBackParam = null;
		$count_row = 0;
		$smarty->hassign("countries", cnGetCountries( $callBackParam, $count_row ) );
		$smarty->assign("main_content_template", "address_editor.tpl.html");
	}
?>