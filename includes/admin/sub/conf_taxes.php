<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
// *****************************************************************************
// Purpose			define tax rates by tax class, zones, zip codes
// Call condition   
//					admin.php?dpt=conf&sub=taxes 
//										- show all tax class
//					admin.php?dpt=conf&sub=taxes&define_rate=<tax class ID>
//										- define tax rate by countries for specified tax class
//					admin.php?dpt=conf&sub=taxes&define_rate=<tax class ID>&kill_rate=<country ID>
//										- delete tax rate for specified country for specified tax class 
//					admin.php?dpt=conf&sub=taxes&define_zone_rates=<tax class ID>&countryID=<country ID>
//										- shows tax rates rate for specified country for specified tax class
//												by zones and zip codes
//					admin.php?dpt=conf&sub=taxes&define_zone_rates=<tax class ID>&countryID=<country ID>&kill_zone_rate=<zone ID>
//										- delete tax rate for specified country for specified zone for specified tax class 
//					admin.php?dpt=conf&sub=taxes&define_zone_rates=<tax class ID>&countryID=<country ID>&kill_zone_rate=<zone ID>
//										- delete tax rate for specified country for specified zip code for specified tax class 
//					
// Include PHP		admin.php -> [conf_taxes.php]
// Uses TPL			conf_taxes.tpl.html
// Remarks


	if (!strcmp($sub, "taxes"))
	{

		function _getUrlToSubmit()
		{
			$url = "admin.php?dpt=conf&sub=taxes";
			if ( isset($_GET["define_rate"]) )
				$url .= "&define_rate=".$_GET["define_rate"];
			if ( isset($_GET["define_zone_rates"]) )
				$url .= "&define_zone_rates=".$_GET["define_zone_rates"];
			if ( isset($_GET["countryID"]) )
				$url .= "&countryID=".$_GET["countryID"];
			return $url;
		}

		function _verifyPerCent( $value, &$verifyResult )
		{
			$value = (float)$value;
			$verifyResult = 0;
			if ( $value < 0 )
			{
				$value = 0;
				$verifyResult = 1;
			}
			if ( $value > 100 )
			{
				$value = 100;
				$verifyResult = 1;
			}
			return $value;
		}


		// *****************************************************************************
		// Purpose	saves zip rates
		// Inputs   
		// Remarks	adds new zip rate and updates existing rates
		// Returns	
		function _saveZipRates()
		{
			$error_percent = "";
			$data = ScanPostVariableWithId( array( "zip_template", "zip_rate" ) );
			foreach( $data as $key => $val )
			{
				taxUpdateZipRate( $key, $val["zip_template"], 
					_verifyPerCent($val["zip_rate"], $verifyResult) );
				if ( $verifyResult == 1 ) 
						$error_percent = "&error_percent=yes";
			}
			if ( trim($_POST["new_zip_template"]) != "" )
			{
				taxAddZipRate( $_GET["define_zone_rates"], 
					$_GET["countryID"], $_POST["new_zip_template"], 
					_verifyPerCent($_POST["new_zip_rate"], $verifyResult) );
				if ( $verifyResult == 1 ) 
						$error_percent = "&error_percent=yes";
			}

			taxSetIsByZoneAttribute( $_GET["define_zone_rates"], $_GET["countryID"], 1 );

			Redirect( "admin.php?dpt=conf&sub=taxes&define_zone_rates=".
					$_GET["define_zone_rates"]."&countryID=".$_GET["countryID"].$error_percent );
		}

		// *****************************************************************************
		// Purpose	saves zone rates
		// Inputs   
		// Remarks	adds new zone rate and updates existing rates
		// Returns	
		function _saveZoneRates()
		{
			$error_percent = "";
			$data = ScanPostVariableWithId( array("zone_rate") );
			foreach( $data as $key => $val )
			{
				taxUpdateZoneRate( $_GET["define_zone_rates"], $key, 
						_verifyPerCent($val["zone_rate"], $verifyResult ) );
				if ( $verifyResult == 1 ) 
						$error_percent = "&error_percent=yes";
			}
			if ( isset($_POST["new_zone"]) )
				if ( (int)$_POST["new_zone"] != -1 )
				{
					taxAddZoneRate( $_GET["define_zone_rates"], 
							$_GET["countryID"],
							$_POST["new_zone"], 
							_verifyPerCent($_POST["new_rate"], $verifyResult) );
					if ( $verifyResult == 1 ) 
						$error_percent = "&error_percent=yes";
				}

			taxSetIsByZoneAttribute( $_GET["define_zone_rates"], $_GET["countryID"], 1 );

			Redirect( "admin.php?dpt=conf&sub=taxes&define_zone_rates=".
					$_GET["define_zone_rates"]."&countryID=".$_GET["countryID"].$error_percent );
		}


		// *****************************************************************************
		// Purpose	saves tax classes
		// Inputs   
		// Remarks	adds new tax class rate and updates existing classes
		// Returns	
		function _saveTaxClasses()
		{
			$error_percent = "";
			$data = ScanPostVariableWithId( array( "class_name", 
										"tax_based_on_address" ) );
			foreach( $data as $key => $val )
				taxUpdateTaxClass( $key, $val["class_name"], 
						$val["tax_based_on_address"] );
			if ( trim($_POST["new_class_name"]) != "" )
				taxAddTaxClass( trim($_POST["new_class_name"]), 
						$_POST["new_tax_based_on_address"] );
			header( "Location: admin.php?dpt=conf&sub=taxes" );
		}


		// *****************************************************************************
		// Purpose	saves rates
		// Inputs   
		// Remarks	
		// Returns	
		function _saveRates()
		{
			$error_percent = "";
			$data = ScanPostVariableWithId( array("isByZone", "rate") );
			foreach( $data as $key => $val )
			{
				if ( !isset($val["isByZone"]) ) $val["isByZone"]=0;
				taxUpdateRate(	$_GET["define_rate"],	$key, 
								$val["isByZone"], 
								_verifyPerCent($val["rate"], $verifyResult) );
				if ( $verifyResult == 1 ) 
					$error_percent = "&error_percent=yes";
			}

			if ( isset($_POST["new_country"]) )
				if ( (int)$_POST["new_country"] != -1 )
				{
					taxAddRate( $_GET["define_rate"], 
							$_POST["new_country"], 
							0, _verifyPerCent($_POST["new_rate"], $verifyResult)  );
					if ( $verifyResult == 1 ) 
						$error_percent = "&error_percent=yes";
				}
			Redirect( "admin.php?dpt=conf&sub=taxes&define_rate=".
					$_GET["define_rate"].$error_percent );
		}


		// *****************************************************************************
		// Purpose	sets in smarty template corresponded variables to show rates by classes
		// Inputs   
		// Remarks	
		// Returns	
		function _showRates( &$smarty )
		{
			$class = taxGetTaxClassById($_GET["define_rate"]);
			$smarty->assign("class_name", $class["name"]);
			$rates = taxGetRates($_GET["define_rate"]);
			foreach( $rates as $val )
				if ( $val["countryID"] == 0 )
					$smarty->assign( "group_exists", 1 );

			$admin_is_depended_on_zone = array();
			$count_zones = array();
			foreach( $rates as $val )
				if ( $val["countryID"] != 0 )
				{
					$str = ADMIN_RATE_IS_DEPENDED_ON_ZONE;
					$str = str_replace( "{N}", taxGetCountSetZone($_GET["define_rate"], 
							$val["countryID"]),  $str );
					$str = str_replace( "{M}", taxGetCountZones($val["countryID"]), 
							$str );
					$count_zones[] = taxGetCountZones($val["countryID"]);
					$admin_is_depended_on_zone[] = $str;
				}
			$smarty->assign("admin_is_depended_on_zone", $admin_is_depended_on_zone);
			$smarty->assign("count_zones", $count_zones);

			$smarty->assign("rates", $rates );
			$smarty->assign("rate_count", count($rates) );
			$countries = taxGetCountriesByClassID_ToSetRate($_GET["define_rate"]);
			$smarty->assign("countries", $countries );
			$smarty->assign("country_count", count($countries) );
			$smarty->assign("define_rate", $_GET["define_rate"] );
		}


		// *****************************************************************************
		// Purpose	sets in smarty template corresponded variables to show zone rates and 
		//				zip rates
		// Inputs   
		// Remarks	
		// Returns	
		function _showZoneRates( &$smarty )
		{
			$zone_rates = taxGetZoneRates( $_GET["define_zone_rates"], 
				$_GET["countryID"] );
			foreach( $zone_rates as $val )
				if ( $val["zoneID"] == 0 )
					$smarty->assign( "group_exists", 1 );
			$smarty->assign("zone_rates", $zone_rates );
			$smarty->assign("zone_rate_count", count($zone_rates) );

			$zip_rates = taxGetZipRates( $_GET["define_zone_rates"], $_GET["countryID"] );
			$smarty->assign("zip_rates", $zip_rates );
			$smarty->assign("rowspan", 
					count($zone_rates) +	// zone count
					2 +						// ADD row + new row
					1 +						// SAVE button
					1 +						// define taxes by zones title
					1 +						// head new table
					count($zip_rates)	+	// zip rates count
					2 +						// ADD row + new row
					2 +						// 2 prompts
					1						// SAVE button
						);
			$zones = taxGetZoneByClassIDCountryID_ToSetRate( $_GET["define_zone_rates"], 
					$_GET["countryID"] );
			$smarty->assign("zones", $zones );
			$smarty->assign("zone_count", count($zones) );
			$smarty->assign("define_zone_rates", $_GET["define_zone_rates"] );
			$tax_class = taxGetTaxClassById( $_GET["define_zone_rates"] );
			$smarty->assign("className", $tax_class["name"] );
			$country = cnGetCountryById( $_GET["countryID"] );
			$smarty->hassign("country_name", $country["country_name"] );
		}


		// *****************************************************************************
		// Purpose	sets in smarty template corresponded variables to show classes
		// Inputs   
		// Remarks	
		// Returns	
		function _showTaxClasses( &$smarty )
		{
			$smarty->assign("classes", taxGetTaxClasses() );
		}



		// invalid percent error ( less than 0 or more than 100 )
		if ( isset($_GET["error_percent"]) )
			$smarty->assign("error_percent", 1);

		// delete tax rate rate for specified country for specified tax class
		//		by zones and zip codes
		if ( isset($_GET["kill_rate"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=conf&sub=taxes&define_rate=".$_GET["define_rate"]."&safemode=yes" );
			}

			taxDeleteRate( $_GET["define_rate"], 
					$_GET["kill_rate"] );

			Redirect( "admin.php?dpt=conf&sub=taxes&define_rate=".$_GET["define_rate"] );
		}

		// delete tax rate for specified country for specified zone for 
		//		specified tax class 
		if ( isset($_GET["kill_zone_rate"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=conf&sub=taxes&define_zone_rates=".
						$_GET["define_zone_rates"]."&countryID=".$_GET["countryID"]."&safemode=yes" );
			}
			taxDeleteZoneRate( $_GET["define_zone_rates"], 
				$_GET["kill_zone_rate"] );
			Redirect("admin.php?dpt=conf&sub=taxes&define_zone_rates=".
						$_GET["define_zone_rates"]."&countryID=".$_GET["countryID"] );
		}

		// delete tax rate for specified country for specified zip code 
		//		for specified tax class 
		if ( isset($_GET["kill_zip_rate"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=conf&sub=taxes&define_zone_rates=".
						$_GET["define_zone_rates"]."&countryID=".$_GET["countryID"]."&safemode=yes" );
			}
			taxDeleteZipRate( $_GET["kill_zip_rate"] );
			Redirect("admin.php?dpt=conf&sub=taxes&define_zone_rates=".
						$_GET["define_zone_rates"]."&countryID=".$_GET["countryID"] );
		}


		// update and add new zone rate 
		if ( isset($_POST["save_zone_rates"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=conf&sub=taxes&define_zone_rates=".
						$_GET["define_zone_rates"]."&countryID=".$_GET["countryID"]."&safemode=yes" );
			}
			_saveZoneRates();
		}

		// update and add new zip rate 
		if ( isset($_POST["save_zip_rates"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=conf&sub=taxes&define_zone_rates=".
						$_GET["define_zone_rates"]."&countryID=".$_GET["countryID"]."&safemode=yes" );
			}
			_saveZipRates();
		}

		if ( isset($_POST["save"]) )
		{
			if ( isset($_GET["define_rate"]) )
			{
				if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
				{
					Redirect( "admin.php?dpt=conf&sub=taxes&define_rate=".$_GET["define_rate"]."&safemode=yes" );
				}
				// update and add new country rate
				_saveRates();
			}
			else
			{
				if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
				{
					Redirect( "admin.php?dpt=conf&sub=taxes&safemode=yes" );
				}
				// update and add new tax class
				_saveTaxClasses();
			}
		}

		// delete class tax 
		if ( isset($_GET["kill_class"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=conf&sub=taxes&safemode=yes" );
			}
			taxDeleteTaxClass( $_GET["kill_class"] );
			Redirect( "admin.php?dpt=conf&sub=taxes" );
		}

		if ( isset($_GET["define_rate"]) )
			_showRates( $smarty );
		else if ( isset($_GET["define_zone_rates"]) )
			_showZoneRates( $smarty );
		else
			_showTaxClasses( $smarty );


		$smarty->assign("urlToSubmit", _getUrlToSubmit() );
		//set sub-department template
		$smarty->assign("admin_sub_dpt", "conf_taxes.tpl.html");
	}
?>