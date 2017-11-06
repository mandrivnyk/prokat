<?php
// *****************************************************************************
// Purpose  delete payment method
// Inputs   
// Remarks  
// Returns  nothing
function payDeletePaymentMethod( $PID )
{
	db_query("delete from ".PAYMENT_TYPES_TABLE." where PID=$PID");
}


// *****************************************************************************
// Purpose  get payment methods by module
// Inputs   
// Remarks  
// Returns  
function payGetPaymentMethodsByModule( $paymentModule )
{
	$moduleID = $paymentModule->get_id();
	if ( $moduleID == "" )
		return array();
	$q = db_query("select PID, Name, description, Enabled, sort_order, ".
			" email_comments_text, module_id, calculate_tax ".
			" from ".
			PAYMENT_TYPES_TABLE." where module_id=".$moduleID );	
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
// Purpose  get payment module by ID
// Inputs   
// Remarks  
// Returns  
function payGetPaymentModuleById( $PID, $paymentModulesFiles )
{
	$paymentModules = modGetModules( $paymentModulesFiles );
	$currentPaymentModule = null;
	foreach( $paymentModules as $paymentModule )
	{
		if ( (int)$paymentModule->get_id()==(int)$PID )
		{
			$currentPaymentModule = $paymentModule;
			break;
		}
	}
	return $currentPaymentModule;
}


// *****************************************************************************
// Purpose  get payment method by ID
// Inputs   
// Remarks  
// Returns  
function payGetPaymentMethodById( $PID )
{
	$PID = (int)$PID;

	$q = db_query("select PID, Name, description, Enabled, sort_order, ".
			" email_comments_text, module_id, calculate_tax, module_id ".
			" from ".
			PAYMENT_TYPES_TABLE." where PID=$PID " );
	if ( $row=db_fetch_row($q) )
	{
		$row["Name"]				= TransformDataBaseStringToText( $row["Name"] );
		$row["description"]			= TransformDataBaseStringToText( $row["description"] );
		$row["email_comments_text"]	= TransformDataBaseStringToText( $row["email_comments_text"] );
	}
	return $row;
}


// *****************************************************************************
// Purpose  get all payment methods
// Inputs   
// Remarks  
// Returns  nothing
function payGetAllPaymentMethods( $enabledOnly = false )
{
	$whereClause = "";
	if ( $enabledOnly )
		$whereClause = " where Enabled=1 ";
	$q = db_query("select PID, Name, description, Enabled, sort_order,  ".
			" email_comments_text, module_id, calculate_tax from ".
			PAYMENT_TYPES_TABLE." ".$whereClause.
			" order by sort_order") or die (db_error());
	$data = array();
	while( $row = db_fetch_row($q) )
	{
		$row["ShippingMethodsToAllow"] =
			_getShippingMethodsToAllow( $row["PID"] );

		$row["Name"]				= TransformDataBaseStringToText( $row["Name"] );
		$row["description"]			= TransformDataBaseStringToText( $row["description"] );
		$row["email_comments_text"]	= TransformDataBaseStringToText( $row["email_comments_text"] );

		$data[] = $row;
	}
	return $data;
}


// *****************************************************************************
// Purpose  get all installed payment modules
// Inputs   
// Remarks  
// Returns  nothing
function payGetInstalledPaymentModules()
{
	$moduleFiles = GetFilesInDirectory( "./modules/payment", "php" );
	$payment_modules = array();
	foreach( $moduleFiles as $fileName )
	{
		$className = GetClassName( $fileName );
		if(!$className)continue;
		eval( "\$payment_module = new ".$className."();" );
		if ( $payment_module->is_installed() )
			$payment_modules[] = $payment_module;
	}
	return $payment_modules;
}


// *****************************************************************************
// Purpose  add payment method
// Inputs   
// Remarks  
// Returns  nothing	
function payAddPaymentMethod( $Name, $description, $Enabled, $sort_order,
				$email_comments_text, $module_id, $calculate_tax )
{
	$Name					= TransformStringToDataBase( $Name );
	$description			= TransformStringToDataBase( $description );
	$email_comments_text	= TransformStringToDataBase( $email_comments_text );
	$sort_order		= (int)$sort_order;
	$calculate_tax	= (float)$calculate_tax;
	db_query("insert into ".PAYMENT_TYPES_TABLE.
			" ( Name, description, email_comments_text, Enabled, module_id, sort_order, calculate_tax  ) ".
			"values".
			" ( '$Name', '$description', '$email_comments_text', $Enabled, $module_id, $sort_order, ".
					" $calculate_tax )" );
	return db_insert_id();
}


// *****************************************************************************
// Purpose  update payment method
// Inputs   
// Remarks  
// Returns  nothing	
function payUpdatePaymentMethod(
				$PID, $Name, $description, $Enabled, $sort_order,
				$module_id, $email_comments_text, $calculate_tax )
{
	$Name					= TransformStringToDataBase( $Name );
	$description			= TransformStringToDataBase( $description );
	$email_comments_text	= TransformStringToDataBase( $email_comments_text );
	$module_id = (int) $module_id;
	$sort_order		= (int)$sort_order;
	$calculate_tax	= (float)$calculate_tax;
	db_query("update ".PAYMENT_TYPES_TABLE.
		" set  ".
		" Name='$Name', description='$description', email_comments_text='$email_comments_text', ".
		" Enabled='$Enabled', module_id=$module_id, sort_order=$sort_order, calculate_tax = $calculate_tax ".
		" where PID=$PID");	
}

// *****************************************************************************
// Purpose  
// Inputs   
// Remarks  
// Returns  nothing	
function payResetPaymentShippingMethods( $PID )
{
	db_query("delete from ".SHIPPING_METHODS_PAYMENT_TYPES_TABLE." where PID=$PID");
}


// *****************************************************************************
// Purpose  
// Inputs   
// Remarks  
// Returns  nothing	
function _getShippingMethodsToAllow( $PID )
{
	$res = array();
	$shipping_methods = shGetAllShippingMethods();
	for($i=0; $i<count($shipping_methods); $i++)
	{
		$q = db_query("select count(*) from ".SHIPPING_METHODS_PAYMENT_TYPES_TABLE.
				" where SID=".$shipping_methods[$i]["SID"]." AND ".
				" PID=$PID ");
		$row = db_fetch_row($q);
		$item["SID"] = $shipping_methods[$i]["SID"];
		$item["allow"] = $row[0];
		$item["name"]  = $shipping_methods[$i]["Name"];
		$res[] = $item;
	}
	return $res;
}

// *****************************************************************************
// Purpose  
// Inputs   
// Remarks  
// Returns  nothing	
function paySetPaymentShippingMethod( $PID, $SID )
{
	db_query( "insert into ".SHIPPING_METHODS_PAYMENT_TYPES_TABLE." ( PID, SID ) ".
			" values( $PID, $SID )" );
}



// *****************************************************************************
// Purpose  
// Inputs   
// Remarks  
// Returns  nothing	
function payPaymentMethodIsExist( $paymentMethodID )
{
	$q_count = db_query( "select count(*) from ".PAYMENT_TYPES_TABLE.
			" where PID=".$paymentMethodID." AND Enabled=1" );
	$count = db_fetch_row( $q_count );
	$count = $count[0];
	return ( $count != 0 );	
}

/**
 * Return url for transaction result
 *
 * @param string $_Type - success or failure
 * @return string
 */
function getTransactionResultURL($_Type){
	
	$scURL = trim( CONF_FULL_SHOP_URL );
	$scURL = str_replace("http://",  "", $scURL);
	$scURL = str_replace("https://", "", $scURL);
	$scURL = "http://".$scURL;
	return set_query('&transaction_result='.$_Type, $scURL);
}
?>