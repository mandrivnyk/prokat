<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	function GetCustomerGroupByCustomerId( $customerID )
	{
		$customerID = (int) $customerID;
		$q = db_query( "select custgroupID from ".CUSTOMERS_TABLE.
				" where customerID=$customerID" );
		$customer = db_fetch_row($q);
	
		if ( is_null($customer["custgroupID"]) || trim($customer["custgroupID"])=="" )
			return false;
	
		$q = db_query("select custgroupID, custgroup_name, custgroup_discount, sort_order from ".
				CUSTGROUPS_TABLE." where custgroupID=".$customer["custgroupID"] );
		$row = db_fetch_row($q);
		$row["custgroup_name"] = TransformDataBaseStringToText( $row["custgroup_name"] );
		return $row;
   	}


	function GetAllCustGroups()
	{
		$q=db_query("select custgroupID, custgroup_name, custgroup_discount, sort_order from ".
				CUSTGROUPS_TABLE." order by sort_order, custgroup_name ");
		$data=array();
	 	while( $r=db_fetch_row($q) )
		{
			$row=array();
			$row["custgroupID"]		= $r["custgroupID"];
			$row["custgroup_name"]		= TransformDataBaseStringToText( $r["custgroup_name"] );
			$row["custgroup_discount"]	= $r["custgroup_discount"];
			$row["sort_order"]		= $r["sort_order"];
			$data[]=$row;
		}
		return $data;
	}

	function DeleteCustGroup($custgroupID)
	{
		db_query("update ".CUSTOMERS_TABLE." set custgroupID=NULL ".
				" where custgroupID='".$custgroupID."'");
		db_query("delete from ".CUSTGROUPS_TABLE.
			" where custgroupID='".$custgroupID."'");
	}

	function UpdateCustGroup(
			$custgroupID,  
			$custgroup_name, $custgroup_discount, 
			$sort_order )
	{
		$custgroup_name = TransformStringToDataBase( $custgroup_name );
		db_query(
				"update ".CUSTGROUPS_TABLE." set  ".
				"custgroup_name='".$custgroup_name."', ".
				"custgroup_discount='".(float)$custgroup_discount."', ".
				"sort_order='".(int)$sort_order."' ".
				"where custgroupID='".$custgroupID."'"
			);				
	}


	function AddCustGroup(
			$custgroup_name, 
			$custgroup_discount, 
			$sort_order)
	{
		$custgroup_name = TransformStringToDataBase( $custgroup_name );
		db_query("insert into ".CUSTGROUPS_TABLE.
			"( custgroup_name, custgroup_discount, sort_order ) ".
			"values( '".$custgroup_name."', '".(float)$custgroup_discount."', '".(int)$sort_order."' )");
	}

?>