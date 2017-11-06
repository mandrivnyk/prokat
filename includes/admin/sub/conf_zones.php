<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	if (!strcmp($sub, "zones"))
	{

		if ( isset($_GET["delete"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=conf&sub=zones&countryID=".$_GET["countryID"]."&safemode=yes" );
			}

			znDeleteZone( $_GET["delete"] );
			Redirect( "admin.php?dpt=conf&sub=zones&countryID=".$_GET["countryID"] );
		}

		if ( isset($_POST["save_zones"]) )
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=conf&sub=zones&countryID=".$_GET["countryID"]."&safemode=yes" );
			}

			// add new zone
			$name=$_POST["new_zone_name"];
			$code=$_POST["new_zone_code"];
			$countryID = $_GET["countryID"];
			if ( $name != "" )
				znAddZone( $name, $code, $countryID );

			// update zones list
			$data = ScanPostVariableWithId( array("zone_name", "zone_code" ) );

			foreach( $data as $key => $val )
			{
				znUpdateZone($key, 
					$data[$key]["zone_name"], 
					$data[$key]["zone_code"],
					$countryID );
			}
			Redirect( "admin.php?dpt=conf&sub=zones&countryID=".$_GET["countryID"] );
		}

		//if country is not selected, select the first country from the database
		if ( !isset($_GET["countryID"]) )
		{
			$q = db_query("select countryID from ".COUNTRIES_TABLE);
			$row = db_fetch_row( $q );
			Redirect( "admin.php?dpt=conf&sub=zones&countryID=".$row[0] );
		}

		$callBackParam		= null;
		$count_row			= 0;
		$navigatorParams	= null;
		$countries = cnGetCountries( $callBackParam, 
				$count_row, $navigatorParams );
		$smarty->hassign("countries", $countries);

		$zones = znGetZones( $_GET["countryID"] );
		$smarty->hassign("zones",$zones);
		$smarty->assign("zones_count",count($zones));

		$smarty->assign("countryID", $_GET["countryID"] );

		//set sub-department template
		$smarty->assign("admin_sub_dpt", "conf_zones.tpl.html");
	}
?>