<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
// *****************************************************************************
// Purpose	determine weither zone belongs to particlar country
// Inputs     	
// Remarks		
// Returns	true if zone belongs to particlar country
function ZoneBelongsToCountry($zoneID, $countryID)
{
	$q = db_query("select count(*) from ".ZONES_TABLE." where countryID=$countryID");
	$row = db_fetch_row( $q );
	if ( $row[0]!=0 )
	{
		if ( trim($zoneID) == (string)((int)$zoneID)  )
		{
			$q = db_query("select count(*) from ".ZONES_TABLE.
				" where countryID=$countryID AND zoneID=$zoneID");
			$row = db_fetch_row( $q );
			return ($row[0] != 0);
		}
		else
			return false;
	}
	return true;
}




// *****************************************************************************
// Purpose	gets all zones
// Inputs     		nothing
// Remarks		
// Returns		array of maunfactirer, each item of this array 
//				have next struture
//					"zoneID"	- id
//					"zone_name"	- zone name
//					"zone_code"	- zone code 
//					"countryID"	- countryID
function znGetZones( $countryID = null ){
	
	if ( $countryID == null ){
		
		$q = db_query('SELECT zoneID, zone_name, zone_code, countryID FROM '.ZONES_TABLE.' ORDER BY zone_name');
	}else{
		
		$q = db_query('SELECT zoneID, zone_name, zone_code, countryID FROM '.ZONES_TABLE.' WHERE countryID="'.xEscapeSQLstring($countryID).'" ORDER BY zone_name');
	}
	$data=array();
	while( $r=db_fetch_row($q) )
	{
		$data[]=$r;
	}
	return $data;
}


// *****************************************************************************
// Purpose	gets all zones of particular country
// Inputs     		country ID
// Remarks		
// Returns		array of zone, each item of this array 
//				have next struture
//					"zoneID"	- id
//					"zone_name"	- zone name
//					"zone_code"	- zone code 
//					"countryID"	- countryID
function znGetZonesById($countryID)
{
	if ( is_null($countryID) || $countryID == "" )
		$countryID = "NULL";
	$q = db_query('SELECT zoneID, zone_name, zone_code, countryID FROM '.ZONES_TABLE.' WHERE countryID="'.xEscapeSQLstring($countryID).'" ORDER BY zone_name');
	$data=array();
	while( $r=db_fetch_row($q) )
	{
		$data[]=$r;
	}
	return $data;
}

// *****************************************************************************
// Purpose	gets zone by zone ID
// Inputs     		zone ID
// Remarks		
// Returns	array of
//			"zoneID"	- id
//			"zone_name"	- zone name
//			"zone_code"	- zone code 
//			"countryID"	- countryID
function znGetSingleZoneById( $zoneID )
{
	if ( is_null($zoneID) || $zoneID == "" )
		$zoneID = "NULL";
	$q=db_query( "select zoneID, zone_name, ".
			" zone_code, countryID from ".ZONES_TABLE." ".
			" where zoneID=$zoneID " );
	$r = db_fetch_row($q);
	return $r;
}



// *****************************************************************************
// Purpose	deletes Zone
// Inputs     		id
// Remarks		
// Returns		nothing
function znDeleteZone($zoneID)
{
	$tax_classes = taxGetTaxClasses();
	foreach( $tax_classes as $class )
		taxDeleteZoneRate( $class["classID"], $zoneID );

	db_query("update ".CUSTOMER_ADDRESSES_TABLE.
		" set zoneID=NULL where zoneID='".$zoneID."'");
	db_query("delete from ".ZONES_TABLE.
		" where zoneID='".$zoneID."'" );	
}


// *****************************************************************************
// Purpose	updates Zone
// Inputs     		$zoneID		- id
//					$zone_name	- zone name
//					$zone_code	- code zone
// Remarks		
// Returns		nothing
function znUpdateZone( $zoneID, $zone_name, $zone_code, $countryID )
{
	$zone_name	= TransformStringToDataBase( $zone_name );
	$zone_code	= TransformStringToDataBase( $zone_code );
	db_query("update ".ZONES_TABLE." set ".
		"  zone_name='".$zone_name."', ".
		"  zone_code='".$zone_code."', ".
		"  countryID='".$countryID."' ".
		"  where zoneID='".$zoneID."'" );
}


// *****************************************************************************
// Purpose	adds zone
// Inputs     		
//			$zone_name	- zone name
//			$zone_code	- code zone
// Remarks		
// Returns		nothing
function znAddZone( $zone_name, $zone_code, $countryID  )
{
	$zone_name	= TransformStringToDataBase( $zone_name );
	$zone_code	= TransformStringToDataBase( $zone_code );
	db_query("insert into ".ZONES_TABLE.
		"( zone_name, zone_code, countryID )".
		"values( '".$zone_name."', '".$zone_code."', '".$countryID."' )" );
	return db_insert_id();
}

?>