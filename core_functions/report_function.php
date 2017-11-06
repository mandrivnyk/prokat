<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	// *****************************************************************************
	// Purpose	get report for all product for particular category
	// Inputs     	
	// Remarks		
	// Returns	
	function repGetProductReportByCategoryID( $callBackParam, &$count_row, $navigatorParams = null )
	{

		if ( $navigatorParams != null )
		{
			$offset			= $navigatorParams["offset"];
			$CountRowOnPage	= $navigatorParams["CountRowOnPage"];
		}
		else
		{
			$offset = 0;
			$CountRowOnPage = 0;
		}

		$where_clause = "";
		$order_clause = "";

		if ( isset($callBackParam["categoryID"]) )
			if ( $callBackParam["categoryID"] != 0 )
				$where_clause = " where categoryID=".$callBackParam["categoryID"]; 

		if ( isset($callBackParam["sort"]) )
		{
			$order_clause = " order by ".$callBackParam["sort"];
			if (  isset($callBackParam["direction"])  )
				$order_clause .= " ".$callBackParam["direction"];
		}
	
		$res = array();
		$q = db_query( "select name, customers_rating, customer_votes, items_sold, ".
			" viewed_times, in_stock, sort_order  from ".PRODUCTS_TABLE." ".$where_clause.
					" ".$order_clause );
		$i = 0;
		while( $row=db_fetch_row($q) )
		{
			if ( ($i >= $offset && $i < $offset + $CountRowOnPage) || $navigatorParams == null  )
				$res[] = $row;
			$i ++;
		}
		$count_row = $i;

		return $res;		
	}

?>