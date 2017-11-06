<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	// *****************************************************************************
	// Purpose	sets current currency
	// Inputs     	nothing
	// Remarks		
	// Returns	nothing
	function currSetCurrentCurrency( $currencyID )
	{
		//echo $currencyID; 
		//exit();
		//register current currency type in session vars
		$_SESSION["current_currency"] = $currencyID;

		if (isset($_SESSION["log"]))
		{
			db_query("UPDATE ".CUSTOMERS_TABLE." SET CID='".$currencyID.
				"' WHERE Login='".$_SESSION["log"]."'");
		}
	}



	// *****************************************************************************
	// Purpose	gets current selected by user currency unit
	// Inputs     	nothing
	// Remarks		
	// Returns	currency unit ID ( see CURRENCY_TYPES_TABLE table in DataBase )
	function currGetCurrentCurrencyUnitID()
	{
		if ( isset($_SESSION["log"]) )
		{

			$q = db_query("SELECT cust_password, CID FROM ".CUSTOMERS_TABLE.
				" WHERE Login='".$_SESSION["log"]."'");
			$customerInfo = db_fetch_row($q);
			$_SESSION["current_currency"] = $customerInfo["CID"];
			if ( $_SESSION["current_currency"] != null )
				return isset($_SESSION["current_currency"]) ? $_SESSION["current_currency"] : CONF_DEFAULT_CURRENCY;
			else
				return null;
		}
		else
		{
			$cID = isset($_SESSION["current_currency"]) ? (int)$_SESSION["current_currency"] : CONF_DEFAULT_CURRENCY;
			$q = db_query( "select count(CID) from ".CURRENCY_TYPES_TABLE." where CID=".$cID );
			$count = db_fetch_row($q);
			$count = $count[0];
			if ( $count == 0 )
				return null;
			else
				return $cID;
		}
	}


	// *****************************************************************************
	// Purpose	gets current selected by user currency unit
	// Inputs     	nothing
	// Remarks		
	// Returns	currency unit ID ( see CURRENCY_TYPES_TABLE table in DataBase )
	function currGetCurrencyByID( $currencyID )
	{
		$q = db_query( "select CID, Name, code, currency_value, where2show, sort_order, currency_iso_3 from ".
			CURRENCY_TYPES_TABLE." where CID=$currencyID " );
		$row = db_fetch_row($q);
		if ($row)
		{
			$row["Name"]			= TransformDataBaseStringToText( $row["Name"] );
			$row["code"]			= TransformDataBaseStringToText( $row["code"] );
			$row["currency_iso_3"]	= TransformDataBaseStringToText( $row["currency_iso_3"] );
		}
		else
		{
			$row = NULL;
		}
		return $row;
	}

	// *****************************************************************************
	// Purpose	gets current selected by user currency unit
	// Inputs     	nothing
	// Remarks		
	// Returns	currency unit iso3 ( see CURRENCY_TYPES_TABLE table in DataBase )
	function currGetCurrencyByISO3( $_ISO3 )
	{
		$q = db_query( "select CID, Name, code, currency_value, where2show, sort_order, currency_iso_3 from ".
			CURRENCY_TYPES_TABLE." where currency_iso_3='".xEscapeSQLstring($_ISO3)."' " );
		$row = db_fetch_row($q);
		if ($row)
		{
			$row["Name"]			= TransformDataBaseStringToText( $row["Name"] );
			$row["code"]			= TransformDataBaseStringToText( $row["code"] );
			$row["currency_iso_3"]	= TransformDataBaseStringToText( $row["currency_iso_3"] );
		}
		else
		{
			$row = NULL;
		}
		return $row;
	}



	// *****************************************************************************
	// Purpose	get all currencies
	// Inputs     	nothing
	// Remarks		
	// Returns	currency array
	function currGetAllCurrencies() 
	{
		$q = db_query("select Name, code, currency_iso_3, currency_value, where2show, CID, sort_order from ".
				CURRENCY_TYPES_TABLE." order by sort_order");
		$data = array();
		while( $row = db_fetch_row($q) )
		{
			$row["Name"]			= TransformDataBaseStringToText( $row["Name"] );
			$row["code"]			= TransformDataBaseStringToText( $row["code"] );
			$row["currency_iso_3"]	= TransformDataBaseStringToText( $row["currency_iso_3"] );
			$data[] = $row;
		}
		return $data;
	}


	// *****************************************************************************
	// Purpose	delete currency by ID
	// Inputs     	CID
	// Remarks		
	// Returns	nothing
	function currDeleteCurrency( $CID )
	{
		$q = db_query( "select CID from ".CURRENCY_TYPES_TABLE." where CID!=".$CID );
		if ( $currency=db_fetch_row($q) )
			db_query("update ".CUSTOMERS_TABLE." set CID=".$currency["CID"]." where CID=".$CID );
		else
			db_query("update ".CUSTOMERS_TABLE." set CID=NULL where CID=".$CID );
		db_query( "delete from ".CURRENCY_TYPES_TABLE." where CID=$CID" );
	}


	// *****************************************************************************
	// Purpose	update currency by ID
	// Inputs     	CID
	// Remarks		
	// Returns	nothing
	function currUpdateCurrency( $CID, $name, $code, $currency_iso_3, $value, $where, $sort_order )
	{
		$name				= TransformStringToDataBase( $name );
		$code				= TransformStringToDataBase( $code );
		$currency_iso_3		= TransformStringToDataBase( $currency_iso_3 );
		$sort_order			= (int)$sort_order;
		db_query( "update ".
				CURRENCY_TYPES_TABLE.
				" set ".
				" 	Name='".$name."', ".
				"  	code='".$code."', ". 
				"	currency_value='".$value."', ".
				" 	where2show='".$where."', ".
				"	sort_order='".$sort_order."', ".
				"	currency_iso_3='".$currency_iso_3."' ".
				" where CID=$CID" );
	}


	// *****************************************************************************
	// Purpose	add currency by ID
	// Inputs     	CID
	// Remarks		
	// Returns	nothing
	function currAddCurrency( $name, $code, $currency_iso_3, $value, $where, $sort_order )
	{
		$name				= TransformStringToDataBase( $name );
		$code				= TransformStringToDataBase( $code );
		$currency_iso_3		= TransformStringToDataBase( $currency_iso_3 );
		$sort_order			= (int)$sort_order;
		db_query( "insert into ".CURRENCY_TYPES_TABLE.
			" (Name, code, currency_iso_3, currency_value, where2show, sort_order) ".
			" values ('".$name."', '".$code."', '".$currency_iso_3."', '".$value."', '".$where."', '".$sort_order."')" );
	}

?>