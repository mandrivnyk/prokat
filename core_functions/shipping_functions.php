<?php
// *****************************************************************************
// Purpose  delete shipping method
// Inputs   
// Remarks  
// Returns  nothing	
function shDeleteShippingMethod( $SID )
{
	db_query("delete from ".SHIPPING_METHODS_TABLE." where SID=$SID");
}



// *****************************************************************************
// Purpose  get payment methods by module
// Inputs   
// Remarks  
// Returns  
function shGetShippingMethodsByModule( $shippingModule )
{
	$moduleID = $shippingModule->get_id();

	if ( strlen($moduleID) == 0 )
		return array();

	$moduleID = (int)$moduleID;

	$q = db_query("select SID, Name, description, Enabled, sort_order, ".
			" email_comments_text, module_id ".
			" from ".
			SHIPPING_METHODS_TABLE." where module_id=".$moduleID );	
	$data = array();
	while( $row = db_fetch_row($q) )
	{
		$row["Name"]				= TransformDataBaseStringToText( $row["Name"] );
		$row["description"]			= TransformDataBaseStringToText( $row["description"] );
		$row["email_comments_text"]	= TransformDataBaseStringToText( $row["email_comments_text"] );
		$data[] = $row;
	}
	return $data;		
}




// *****************************************************************************
// Purpose  get shipping method by ID
// Inputs   
// Remarks  
// Returns  
function shGetShippingMethodById( $shippingMethodID )
{
	$shippingMethodID = (int) $shippingMethodID;

	$q = db_query( "select SID, Name, description, shipping_price, Enabled, sort_order, email_comments_text, module_id from ".
		SHIPPING_METHODS_TABLE.
		" where SID=$shippingMethodID " );
	if ( $row=db_fetch_row($q) )
	{
		$row["Name"]				= TransformDataBaseStringToText( $row["Name"] );
		$row["description"]			= TransformDataBaseStringToText( $row["description"] );
		$row["email_comments_text"]	= TransformDataBaseStringToText( $row["email_comments_text"] );
	}
	return $row;
}


// *****************************************************************************
// Purpose  get all shipping methods
// Inputs   
// Remarks  
// Returns  nothing
function shGetAllShippingMethods( $enabledOnly = false )
{
	$whereClause = "";
	if ( $enabledOnly )
		$whereClause = " where Enabled=1 ";
	$q = db_query("select SID, Name, description, Enabled, sort_order, email_comments_text, module_id from ".
				SHIPPING_METHODS_TABLE." ".$whereClause.
				" order by sort_order") or die (db_error());
	$data = array();
	while( $row = db_fetch_row($q) )
	{
		$row["Name"]				= TransformDataBaseStringToText( $row["Name"] );
		$row["description"]			= TransformDataBaseStringToText( $row["description"] );
		$row["email_comments_text"]	= TransformDataBaseStringToText( $row["email_comments_text"] );
		$data[] = $row;
	}
	return $data;
}


// *****************************************************************************
// Purpose  get all installed shipping modules
// Inputs   
// Remarks  
// Returns  nothing
function shGetInstalledShippingModules()
{
	$moduleFiles = GetFilesInDirectory( "./modules/shipping", "php" );
	$shipping_modules = array();
	foreach( $moduleFiles as $fileName )
	{
		$className = GetClassName( $fileName );
		if(!$className)continue;
		eval( "\$shipping_module = new ".$className."();" );
		if ( $shipping_module->is_installed() )
			$shipping_modules[] = $shipping_module;
	}
	return $shipping_modules;
}


// *****************************************************************************
// Purpose  add shipping method
// Inputs   
// Remarks  
// Returns  nothing	
function shAddShippingMethod( $Name, $description, $Enabled, $sort_order,
				$module_id, $email_comments_text )
{
	$Name					= TransformStringToDataBase( $Name );
	$description			= TransformStringToDataBase( $description );
	$email_comments_text	= TransformStringToDataBase( $email_comments_text );
	$sort_order	= (int)$sort_order;
	db_query("insert into ".SHIPPING_METHODS_TABLE.
			" ( Name, description, email_comments_text, Enabled, module_id, sort_order  ) ".
			"values".
			" ( '$Name', '$description', '$email_comments_text', $Enabled, $module_id, $sort_order )" );
	return db_insert_id();
}


// *****************************************************************************
// Purpose  update shipping method
// Inputs   
// Remarks  
// Returns  nothing	
function shUpdateShippingMethod(
				$SID, $Name, $description, $Enabled, $sort_order,
				$module_id, $email_comments_text )
{
	$Name					= TransformStringToDataBase( $Name );
	$description			= TransformStringToDataBase( $description );
	$email_comments_text	= TransformStringToDataBase( $email_comments_text );
	$module_id = (int) $module_id;
	$sort_order	= (int)$sort_order;
	db_query("update ".SHIPPING_METHODS_TABLE.
		" set  ".
		" Name='$Name', description='$description', email_comments_text='$email_comments_text', ".
		" Enabled='$Enabled', module_id=$module_id, sort_order=$sort_order ".
		" where SID=$SID");	
}


// *****************************************************************************
// Purpose  
// Inputs   $shippingMethodID - shipping exists
// Remarks  
// Returns  true if shipping method is exists
function shShippingMethodIsExist( $shippingMethodID )
{
	$q_count = db_query( "select count(*) from ".SHIPPING_METHODS_TABLE.
			" where SID=".$shippingMethodID." AND Enabled=1" );
	$count = db_fetch_row( $q_count );
	$count = $count[0];
	return ( $count != 0 );
}

?>