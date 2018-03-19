<?php
// *****************************************************************************
// Purpose  insert predefined modules setting group into SETTINGS_GROUPS_TABLE table
// Inputs   
// Remarks  this function is called in CreateTablesStructureXML, ID of this group equals to 
//		result of function settingGetFreeGroupId()
// Returns  nothing
function settingInstall()
{
	db_query("insert into ".SETTINGS_GROUPS_TABLE.
		" ( settings_groupID, settings_group_name, sort_order ) ".
		" values( ".settingGetFreeGroupId().", 'MODULES', 0 ) " );
}


// *****************************************************************************
// Purpose  see settingInstall() function
// Inputs   
// Remarks  
// Returns  group ID
function settingGetFreeGroupId()
{
	return 1;
}

function settingGetConstNameByID($_SettingID){
	
	$ReturnVal = '';
	$sql = '
		SELECT settings_constant_name FROM '.SETTINGS_TABLE.' WHERE settingsID='.intval($_SettingID).'
	';
	@list($ReturnVal) = db_fetch_row(db_query($sql));
	return $ReturnVal;
}

function settingGetAllSettingGroup()
{
	$q = db_query( "select settings_groupID, settings_group_name, sort_order from ".
			SETTINGS_GROUPS_TABLE.
			" where settings_groupID != ".settingGetFreeGroupId().
			" order by sort_order, settings_group_name " );
	$res = array();
	while( $row = db_fetch_row($q) )
		$res[] = $row;
	return $res;
}


function settingGetSetting( $constantName )
{
	$q = db_query("select settingsID, settings_groupID, settings_constant_name, ".
		" settings_value, settings_title, settings_description, ".
		" settings_html_function, sort_order ".
		" from ".SETTINGS_TABLE.
		" where settings_constant_name='$constantName' " );
 	return ( $row = db_fetch_row($q) );
}


function settingGetSettings( $settings_groupID )
{
	$q = db_query("select settingsID, settings_groupID, settings_constant_name, ".
		" settings_value, settings_title, settings_description, ".
		" settings_html_function, sort_order ".
		" from ".SETTINGS_TABLE.
		" where settings_groupID=$settings_groupID ".
		" order by sort_order, settings_title ");
	$res = array();
 	while( $row = db_fetch_row($q) )
		$res[] = $row;
	return $res;
}

function _setSettingOptionValue( $settings_constant_name, $value )
{
	$value = xEscapeSQLstring( $value );
	db_query("update ".SETTINGS_TABLE." set settings_value='$value' ".
		" where settings_constant_name='$settings_constant_name'" );
}

function _getSettingOptionValue( $settings_constant_name )
{
	$q = db_query("select settings_value from ".SETTINGS_TABLE.
		" where settings_constant_name='".$settings_constant_name."'" );
	if ( $row = db_fetch_row( $q ) )
		return TransformDataBaseStringToText($row["settings_value"]);
	return null;
}

function _setSettingOptionValueByID( $settings_constant_id, $value )
{
	$value = xEscapeSQLstring( $value );
	$sql = '
		UPDATE '.SETTINGS_TABLE.' SET settings_value="'.$value.'"
		WHERE settingsID="'.(int)$settings_constant_id.'"
	';
	db_query($sql);
}

function _getSettingOptionValueByID( $settings_constant_id )
{
	$q = db_query("select settings_value from ".SETTINGS_TABLE.
		" where settingsID='".$settings_constant_id."'" );
	if ( $row = db_fetch_row( $q ) )
		return $row["settings_value"];
	return null;
}


function settingCallHtmlFunction( $constantName )
{
	$q = db_query("select settings_html_function, settingsID, settings_constant_name from ".
		SETTINGS_TABLE.
		" where settings_constant_name='$constantName' " );
  	if( $row = db_fetch_row($q) )
	{
		$function 	=  $row["settings_html_function"];
		$settingsID	=  $row["settingsID"];
		$str = "";
		if ( preg_match('/,[ ]*$|\([ ]*$/',$function))
			eval( "\$str=".$function."$settingsID);" );
		else
			eval( "\$str=".$function.";" );
		return $str;
	}
	return false;	
}


function settingCallHtmlFunctions( $settings_groupID )
{
	$q = db_query("select settings_html_function, settingsID from ".SETTINGS_TABLE.
		" where settings_groupID=$settings_groupID ".
		" order by sort_order, settings_title " );
	$controls = array();
  	while( $row = db_fetch_row($q) )
	{
		$function 	=  $row["settings_html_function"];
		$settingsID	=  $row["settingsID"];
		$str = "";
		if ( is_bool(strpos($function,")")) )
			eval( "\$str=".$function."$settingsID);" );
		else
			eval( "\$str=".$function.";" );
		$controls[] = $str;
	}
	return $controls;
}



// *****************************************************************************
// Purpose	generate define directive withhelp eval function
// Inputs   nothing  	
// Remarks	
// Returns	nothing
function settingDefineConstants()
{
	$q = db_query("select settings_constant_name, settings_value from ".SETTINGS_TABLE);
	while( $row = db_fetch_row($q) ){
		
		$EvalStr = 'define(\''.$row["settings_constant_name"].'\', \''.
			str_replace(array('\\',"'"),array('\\\\',"\'"), $row["settings_value"] ).'\');';
		@eval( $EvalStr );
	}
}


function setting_CHECK_BOX($settingsID)
{
	$q = db_query("select settings_constant_name from ".
			SETTINGS_TABLE." where settingsID=$settingsID");
	$row = db_fetch_row( $q );
	$settings_constant_name = $row["settings_constant_name"];

	if ( isset($_POST["save"]) )
		_setSettingOptionValue( $settings_constant_name, 
				isset($_POST["setting".$settings_constant_name])?1:0 );
	$res = "<input type=checkbox name='setting".$settings_constant_name."' value=1 ";
	if ( _getSettingOptionValue($settings_constant_name) )
		$res .= " checked ";
	$res .= ">";
	return $res;	
}



// *****************************************************************************
// Purpose	
// Inputs   
//			$dataType = 0	- string
//			$dataType = 1	- float
//			$dataType = 2	- int
// Remarks		
// Returns	
function setting_TEXT_BOX($dataType, $settingsID, $BlockInSafeMode = null){
	
	if(isset($BlockInSafeMode)){
		
		if($settingsID && CONF_BACKEND_SAFEMODE)return ADMIN_SAFEMODE_BLOCKED;
		else{
			$settingsID = $BlockInSafeMode;
		}
	}
	$q = db_query("select settings_constant_name from ".
			SETTINGS_TABLE." where settingsID=$settingsID");
	$row = db_fetch_row( $q );
	$settings_constant_name = $row["settings_constant_name"];

	if ( isset($_POST["save"]) && isset($_POST["setting".$settings_constant_name]) )
	{
	 	if ( $dataType == 0 )
			$value = $_POST["setting".$settings_constant_name];
		else if ( $dataType == 1 )
			$value = (float)$_POST["setting".$settings_constant_name];
		else if ( $dataType == 2 )
			$value = (int)$_POST["setting".$settings_constant_name];
		_setSettingOptionValue( $settings_constant_name, $value );
	}
	return "<input type=text size='55' value='"._getSettingOptionValue( $settings_constant_name ).
			"' name='setting".$settings_constant_name."' >";
}

// *****************************************************************************
// Purpose	same as setting_TEXT_BOX() except for it stores data in encrypted way
// Inputs   
//			$dataType = 0	- string
//			$dataType = 1	- float
//			$dataType = 2	- int
// Remarks		
// Returns	
function setting_TEXT_BOX_SECURE($dataType, $settingsID)
{
	$q = db_query("select settings_constant_name from ".
			SETTINGS_TABLE." where settingsID=$settingsID");
	$row = db_fetch_row( $q );
	$settings_constant_name = $row["settings_constant_name"];

	if ( isset($_POST["save"]) && isset($_POST["setting".$settings_constant_name]) )
	{
	 	if ( $dataType == 0 )
			$value = $_POST["setting".$settings_constant_name];
		else if ( $dataType == 1 )
			$value = (float)$_POST["setting".$settings_constant_name];
		else if ( $dataType == 2 )
			$value = (int)$_POST["setting".$settings_constant_name];
		_setSettingOptionValue( $settings_constant_name, cryptCCNumberCrypt ( $value , NULL ) );
	}
	return "<input type=text value='".cryptCCNumberDeCrypt( _getSettingOptionValue( $settings_constant_name ) , NULL ).
			"' name='setting".$settings_constant_name."' >";
}


function setting_DATEFORMAT()
{
	if ( isset($_POST["save"]) )
	{
		if ( isset($_POST["setting_DATEFORMAT"]) )
		{
			_setSettingOptionValue( "CONF_DATE_FORMAT", 
				$_POST["setting_DATEFORMAT"] );
		}
	}

	$res = "";
	$currencies = currGetAllCurrencies();
	$res = "<select name='setting_DATEFORMAT'>";
	$current_format = _getSettingOptionValue("CONF_DATE_FORMAT");
	if (!$current_format) $current_format = "MM/DD/YYYY";

	//first option  - MM/DD/YYYY - US style
	$res .= "<option value='MM/DD/YYYY'";
	if (!strcmp($current_format,"MM/DD/YYYY")) $res .= " selected";
	$res .= ">MM/DD/YYYY</option>";

	//second option - DD.MM.YYYY - European style
	$res .= "<option value='DD.MM.YYYY'";
	if (!strcmp($current_format,"DD.MM.YYYY")) $res .= " selected";
	$res .= ">DD.MM.YYYY</option>";

	$res .= "</select>";
	return $res;
}


function setting_WEIGHT_UNIT($settingsID)
{
	if ( isset($_POST["save"]) )
		_setSettingOptionValue( "CONF_WEIGHT_UNIT", 
				$_POST["setting_WEIGHT_UNIT"] );
	$res = "<select name='setting_WEIGHT_UNIT'>";

	$units = array(
				"lbs" => STRING_LBS,
				"kg" => STRING_KG,
				"g" => STRING_GRAM
			);

	foreach( $units as $key => $val )
	{
		$res .= "<option value='".$key."'";
		if ( !strcmp(_getSettingOptionValue("CONF_WEIGHT_UNIT"),$key) )$res .= " selected ";
		$res .= ">";
		$res .= "	".$val;
		$res .= "</option>";
	}
	$res .= "</select>";
	return $res;
}


function settingCONF_DEFAULT_CURRENCY()
{
	if ( isset($_POST["save"]) )
	{
		if ( isset($_POST["settingCONF_DEFAULT_CURRENCY"]) )
		{
			_setSettingOptionValue( "CONF_DEFAULT_CURRENCY", 
				$_POST["settingCONF_DEFAULT_CURRENCY"] );
		}
	}

	$res = "";
	$currencies = currGetAllCurrencies();
	$res = "<select name='settingCONF_DEFAULT_CURRENCY'>";
	$res .= "<option value='0'>".ADMIN_NOT_DEFINED."</option>";
	$selectedID = _getSettingOptionValue("CONF_DEFAULT_CURRENCY");
	foreach( $currencies as $currency )
	{
		$res .= "<option value='".$currency["CID"]."' ";
		if ( $selectedID == $currency["CID"] )
			$res .= " selected ";
		$res .= ">";
		$res .= $currency["Name"];
		$res .= "</option>";
	}
	$res .= "</select>";
	return $res;
}


function settingCONF_DEFAULT_COUNTRY()
{
	if ( isset($_POST["save"]) )
		_setSettingOptionValue( "CONF_DEFAULT_COUNTRY", 
				$_POST["settingCONF_DEFAULT_COUNTRY"] );
	$res = "<select name='settingCONF_DEFAULT_COUNTRY'>";
	$res .= "<option value='0'>".ADMIN_NOT_DEFINED."</option>";
	$selectedID = _getSettingOptionValue("CONF_DEFAULT_COUNTRY");
	$count_row = 0;
	$countries = cnGetCountries( array(), $count_row );

	foreach( $countries as $country )
	{
		$res .= "<option value='".$country["countryID"]."'";
		if ( $selectedID == $country["countryID"] )
			$res .= " selected ";
		$res .= ">";
		$res .= "	".xHtmlSpecialChars($country["country_name"]);
		$res .= "</option>";
	}
	$res .= "</select>";
	return $res;
}


function settingCONF_DEFAULT_TAX_CLASS()
{
	if ( isset($_POST["save"]) )
		_setSettingOptionValue( "CONF_DEFAULT_TAX_CLASS", 
				$_POST["settingCONF_DEFAULT_TAX_CLASS"] );
	$res  = "<select name='settingCONF_DEFAULT_TAX_CLASS'>";
	$res .= "	<option value='0'>".ADMIN_NOT_DEFINED."</option>";
	$selectedID = _getSettingOptionValue("CONF_DEFAULT_TAX_CLASS");
	$count_row = 0;
	$taxClasses = taxGetTaxClasses();
	foreach( $taxClasses as $taxClass )
	{
		$res .= "	<option value='".$taxClass["classID"]."'";
		if ( $selectedID == $taxClass["classID"] )
			$res .= " selected ";
		$res .= ">";
		$res .= "	".$taxClass["name"];
		$res .= "</option>";
	}
	$res .= "</select>";
	return $res;
}

function settingCONF_DEFAULT_CUSTOMER_GROUP()
{
	if ( isset($_POST["save"]) )
		_setSettingOptionValue( "CONF_DEFAULT_CUSTOMER_GROUP", 
				$_POST["settingCONF_DEFAULT_CUSTOMER_GROUP"] );

	$res = "<select name='settingCONF_DEFAULT_CUSTOMER_GROUP'>";
	$selectedID = _getSettingOptionValue("CONF_DEFAULT_CUSTOMER_GROUP");

	$res .= "<option value='0'>".ADMIN_NOT_DEFINED."</option>";

	$custGroups = GetAllCustGroups();
	foreach( $custGroups as $custGroup )
	{
		$res .= "<option value='".$custGroup["custgroupID"]."'";
		if ( $selectedID == $custGroup["custgroupID"] )
			$res .= " selected ";
		$res .= ">";
		$res .= "	".$custGroup["custgroup_name"];
		$res .= "</option>";
	}
	$res .= "</select>";
	return $res;
}


function _CONF_DISCOUNT_TYPE_radio_button( $value, $caption, $checked, $href )
{
	if ( $checked == 1 )
		$checked = "checked";
	else 
		$checked = "";
	if ( $href )
	{
		$href1 = "<a href='admin.php?dpt=custord&sub=custgroup'>";
		$href2 = "</a>";
	}
	else
	{
		$href1 = "";
		$href2 = "";
	}
	$res  = "";
	$res .= "	<tr>";
	$res .= "		<td>";
	$res .= "			<input name='settingCONF_DISCOUNT_TYPE' type=radio $checked value='$value' >";
	$res .= "		</td>";
	$res .= "		<td>";
	$res .= "			$href1".$caption.$href2;
	$res .= "		</td>";
	$res .= "	</tr>";
	return $res;
}


function settingCONF_DISCOUNT_TYPE()
{
	if ( isset($_POST["save"]) )
		_setSettingOptionValue( "CONF_DISCOUNT_TYPE", $_POST["settingCONF_DISCOUNT_TYPE"] );
	$value = _getSettingOptionValue("CONF_DISCOUNT_TYPE");
	$res = "";
	$res .= "<table border=0 cellspacing=1 cellpadding=5>";
	$res .= _CONF_DISCOUNT_TYPE_radio_button( "1", ADMIN_DISCOUNT_IS_SWITCHED_OFF,  $value=="1"?1:0, 0 );
	$res .= _CONF_DISCOUNT_TYPE_radio_button( "2", ADMIN_DISCOUNT_CUSTOMER_GROUP, 	$value=="2"?1:0, 1 );
	$res .= _CONF_DISCOUNT_TYPE_radio_button( "3", ADMIN_DISCOUNT_GENERAL_ORDER_PRICE, $value=="3"?1:0, 0 );
	$res .= _CONF_DISCOUNT_TYPE_radio_button( "4", ADMIN_DISCOUNT_CUSTOMER_GROUP_PLUS_GENERAL_ORDER_PRICE, 	$value=="4"?1:0, 0 );
	$res .= _CONF_DISCOUNT_TYPE_radio_button( "5", ADMIN_DISCOUNT_MAX_CUSTOMER_GROUP_GENERAL_ORDER_PRICE, 	$value=="5"?1:0, 0 );
	$res .= "</table>";
	return $res;
}


function settingCONF_NEW_ORDER_STATUS()
{
	if ( isset($_POST["save"]) && isset($_POST["settingCONF_NEW_ORDER_STATUS"]) )
		_setSettingOptionValue( "CONF_NEW_ORDER_STATUS", 
				$_POST["settingCONF_NEW_ORDER_STATUS"] );
	$orders = ostGetOrderStatues( false );

	$res = "";
	if ( count($orders)<2 )
		$res .= "<b>".ADMIN_STATUSES_COUNT_PROMPT_ERROR."<b>";
	else
	{
		$selectedID = _getSettingOptionValue("CONF_NEW_ORDER_STATUS");
		if ( $selectedID == "" )
			$res .= "<b>".ADMIN_STATUS_IS_NOT_DEFINED."</b>&nbsp;";
		$res .= "<select name='settingCONF_NEW_ORDER_STATUS'>\n";
		foreach( $orders as $order )
		{
			$res .= "<option value='".$order["statusID"]."' ";
			if ( $selectedID == $order["statusID"] )
				$res .= "selected";
			$res .= ">\n";
			$res .= "		".$order["status_name"]."\n";
			$res .= "</option>\n";
		}	
		$res .= "</select>";
	}
	return $res;
}

function settingCONF_COMPLETED_ORDER_STATUS()
{
	$equal_prompt_error = "";
	if ( isset($_POST["save"]) && isset($_POST["settingCONF_COMPLETED_ORDER_STATUS"]) )
	{
		if ( $_POST["settingCONF_NEW_ORDER_STATUS"] == 
				$_POST["settingCONF_COMPLETED_ORDER_STATUS"] )
		{
			$equal_prompt_error = ADMIN_STATUSES_EQUAL_PROMPT_ERROR;
			$_POST["settingCONF_COMPLETED_ORDER_STATUS"] = ostGetOtherStatus( 
									$_POST["settingCONF_COMPLETED_ORDER_STATUS"] );
			$_POST["settingCONF_COMPLETED_ORDER_STATUS"] = 
				$_POST["settingCONF_COMPLETED_ORDER_STATUS"]["statusID"];
		}
		_setSettingOptionValue( "CONF_COMPLETED_ORDER_STATUS", 
				$_POST["settingCONF_COMPLETED_ORDER_STATUS"] );
	}
	$orders = ostGetOrderStatues( false );
	$res = "";
	if ( count($orders)<2 )
		$res = "<b>".ADMIN_STATUSES_COUNT_PROMPT_ERROR."<b>";
	else
	{
		$selectedID = _getSettingOptionValue("CONF_COMPLETED_ORDER_STATUS");
		if ( $selectedID == "" )
			$res .= "<b>".ADMIN_STATUS_IS_NOT_DEFINED."</b>&nbsp;";
		$res .= "<b>".$equal_prompt_error."</b><br>";
		$res .= "<select name='settingCONF_COMPLETED_ORDER_STATUS'>\n";
		foreach( $orders as $order )
		{
			$res .= "<option value='".$order["statusID"]."' ";
			if ( $selectedID == $order["statusID"] )
				$res .= "selected";
			$res .= ">";
			$res .= "		".$order["status_name"]."\n";
			$res .= "</option>\n";
		}
		$res .= "</select>";
	}
	return $res;
}

function settingCONF_COLOR( $settingsID )
{
	$q = db_query("select settingsID, settings_constant_name from ".
				SETTINGS_TABLE." where settingsID=$settingsID");
	$row = db_fetch_row($q);
	$constant_name = $row["settings_constant_name"];


	if ( isset($_POST["save"]) && isset($_POST["settingCONF_COLOR_".$settingsID])  )
		_setSettingOptionValue( $constant_name, 
				$_POST["settingCONF_COLOR_".$settingsID]  );

	$value = _getSettingOptionValue( $constant_name );
	$value = strtoupper($value);
	$res = "<table><tr><td><table bgcolor=black cellspacing=1><tr><td bgcolor=#".$value.">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table></td>";
	$res .= "<td><input type=text value='".$value."' name='settingCONF_COLOR_$settingsID' ></td></tr></table>";
	return $res;
}

function setting_CURRENCY_SELECT( $_SettingID ){
	
	$Options = array(array('title'=>ADMIN_NOT_DEFINED, 'value'=>0,));
	$Currencies = currGetAllCurrencies();
	foreach ($Currencies as $_Currency){
		
		$Options[] = array(
			'title' 		=> $_Currency['Name'],
			'value' 	=> $_Currency['CID'],
			);
	}
	
	return setting_SELECT_BOX($Options, $_SettingID);
}

function settingCONF_COUNTRY()
{
	if ( isset($_POST["save"]) )
		_setSettingOptionValue( "CONF_COUNTRY", 
				$_POST["settingCONF_COUNTRY"] );

	$count_row = 0;
	$countries = cnGetCountries( array(), $count_row );

	$res = "";
	
	$selectedID = _getSettingOptionValue("CONF_COUNTRY");
	if ( isset( $_GET["countryID"] ) )
		$selectedID = $_GET["countryID"];
	//if ( $selectedID == "0" )
	//	$res .= "<b>".ADMIN_CONF_COUNTRY_IS_NOT_DEFINED."</b>&nbsp;";
	$onChange = "JavaScript:window.location=\"admin.php?dpt=conf&sub=setting&settings_groupID=".$_GET["settings_groupID"]."&countryID=\" + document.MainForm.settingCONF_COUNTRY.value";
	// onchange='$onChange'
	$res .= "<select name='settingCONF_COUNTRY' >\n";
	$res .= "	<option value='0'>".ADMIN_NOT_DEFINED."</option>";
	foreach( $countries as $country )
	{
		$res .= "<option value='".$country["countryID"]."' ";
		if ( $selectedID == $country["countryID"] )
			$res .= "selected";
		$res .= ">\n";
		$res .= "		".xHtmlSpecialChars($country["country_name"])."\n";
		$res .= "</option>\n";
	}	
	$res .= "</select>";
	return $res;
}

function settingCONF_ZONE()
{
	if ( isset($_POST["save"]) )
		if ( isset($_POST["settingCONF_ZONE"]) )
			_setSettingOptionValue( "CONF_ZONE", $_POST["settingCONF_ZONE"] );

	$countries = cnGetCountries( array(), $count_row );
	if ( count($countries) != 0 )
	{

		$countryID = _getSettingOptionValue("CONF_COUNTRY");
		$zones = znGetZones( _getSettingOptionValue("CONF_COUNTRY") );

		$selectedID = _getSettingOptionValue("CONF_ZONE");
		$res = "";
		if ( !ZoneBelongsToCountry($selectedID, $countryID) )
			$res .= ERROR_ZONE_DOES_NOT_CONTAIN_TO_COUNTRY."<br>";
		if ( count($zones) > 0 )
		{
			$res .= "<select name='settingCONF_ZONE'>\n";
			foreach( $zones as $zone )
			{
				$res .= "<option value='".$zone["zoneID"]."' ";
				if ( $selectedID == $zone["zoneID"] )
					$res .= "selected";
				$res .= ">\n";
				$res .= "		".xHtmlSpecialChars($zone["zone_name"])."\n";
				$res .= "</option>\n";
			}	
			$res .= "</select>";
		}
		else
		{
			if ( trim($selectedID) != (string)((int)$selectedID) )
				$res .= "<input type=text name='settingCONF_ZONE' value='$selectedID'>";
			else
				$res .= "<input type=text name='settingCONF_ZONE' value=''>";
		}
		return $res;
	}
	else
		return "-";
}

function settingCONF_CALCULATE_TAX_ON_SHIPPING()
{
	if ( isset($_POST["save"]) )
		_setSettingOptionValue( "CONF_CALCULATE_TAX_ON_SHIPPING", $_POST["settingCONF_CALCULATE_TAX_ON_SHIPPING"] );

	$res = "<select name='settingCONF_CALCULATE_TAX_ON_SHIPPING'>";
	$res .= "	<option value='0'>".ADMIN_NOT_DEFINED."</option>";
	$selectedID = _getSettingOptionValue("CONF_CALCULATE_TAX_ON_SHIPPING");
	$count_row = 0;
	$taxClasses = taxGetTaxClasses();
	foreach( $taxClasses as $taxClass )
	{
		$res .= "<option value='".$taxClass["classID"]."'";
		if ( $selectedID == $taxClass["classID"] )
			$res .= " selected ";
		$res .= ">";
		$res .= "	".$taxClass["name"];
		$res .= "</option>";
	}
	$res .= "</select>";
	return $res;
}

function setting_SELECT_BOX($_Options, $_SettingID){
	
	if(!is_array($_Options)){
	
		$_Options = explode(',',$_Options);
		$TC = count($_Options)-1;
		for(;$TC>=0;$TC--){
		
			$_Options[$TC] = explode(':', $_Options[$TC]);
			$_Options[$TC]['title'] = $_Options[$TC][0];
			if(!isset($_Options[$TC][1])){
				$_Options[$TC]['value'] = '';
			}else{
				$_Options[$TC]['value'] = $_Options[$TC][1];
			}
		}
	}
	$sql = "
		SELECT settings_constant_name 
		FROM ".SETTINGS_TABLE." 
		WHERE settingsID={$_SettingID}
	";
	$row = db_fetch_row( db_query($sql) );
	$settings_constant_name = $row["settings_constant_name"];
	
	if ( isset($_POST["save"]) )
		_setSettingOptionValue( $settings_constant_name, 	$_POST["setting_".$settings_constant_name] );

	$html = '<select name="setting_'.$settings_constant_name.'">';
	$SettingConstantValue = _getSettingOptionValue($settings_constant_name);
	foreach ($_Options as $_Option){
		
		$html .= '<option value="'.xHtmlSpecialChars($_Option['value']).'"'.($SettingConstantValue==$_Option['value']?' selected="selected"':'').'>'.htmlspecialchars($_Option['title']).'</option>';
	}
	$html .= '</select>';
	return $html;
}

function setting_CHECKBOX_LIST($_boxDescriptions, $_SettingID){
	
	$sql = "
		SELECT settings_constant_name 
		FROM ".SETTINGS_TABLE." 
		WHERE settingsID={$_SettingID}
	";
	$row = db_fetch_row( db_query($sql) );
	$settings_constant_name = $row["settings_constant_name"];

	if ( isset($_POST["save"]) ){
		
		$newValues = '';
		$_POST['setting_'.$settings_constant_name] = isset($_POST['setting_'.$settings_constant_name])?$_POST['setting_'.$settings_constant_name]:array();
		
		$maxOffset = max(array_keys($_boxDescriptions));
		
		for(; $maxOffset>=0; $maxOffset-- ){
			
			$newValues .= (int)in_array($maxOffset, $_POST['setting_'.$settings_constant_name]);
		}
		_setSettingOptionValue( $settings_constant_name, 	bindec($newValues) );
	}
	
	$Value = _getSettingOptionValue($settings_constant_name);
	$html = '';

	
	foreach ($_boxDescriptions as $_offset=>$_boxDescr){
		
		$html .= '<div style="padding:2px;"><input'.($Value&pow(2, $_offset)?' checked="checked"':'').' name="setting_'.$settings_constant_name.'[]" value="'.$_offset.'" type="checkbox" style="margin:0px;padding:0px;" />&nbsp;'.$_boxDescr.'</div>';
	}
	return $html;
}

function setting_COUNTRY_SELECT($_ShowButton, $_SettingID = null){
	
	if(!isset($_SettingID)){
		
		$_SettingID = $_ShowButton;
		$_ShowButton = false;
	}
	
	$Options = array(
		array("title"=>'-', "value"=>0)
		);
	$CountriesNum = 0;
	$Countries = cnGetCountries(array('raw data'=>true), $CountriesNum );
	foreach ($Countries as $_Country){
		
		$Options[] = array("title"=>$_Country['country_name'], "value"=>$_Country['countryID']);
	}
	return '<nobr>'.setting_SELECT_BOX($Options, $_SettingID).($_ShowButton?'&nbsp;&nbsp;<input type="submit" name="save" value="'.SELECT_BUTTON.'" />':'').'</nobr>';
}

function setting_ZONE_SELECT($_CountryID, $_Params ,$_SettingID = null){
	
	$Mode = '';
	if(!isset($_SettingID)){
		
		$_SettingID = $_Params;
		$Mode = 'simple';
	}elseif(isset($_Params['mode'])) {
		
		$Mode = $_Params['mode'];
	}
	$Zones = znGetZones($_CountryID);
	$Options = array(
		array("title"=>'-', "value"=>0)
		);
	switch ($Mode){
		default:
		case 'simple':
			break;
		case 'notdef':
			if(!count($Zones))return STR_ZONES_NOTDEFINED;
			break;
	}
	foreach ($Zones as $_Zone){
		
		$Options[] = array("title"=>$_Zone['zone_name'], "value"=>$_Zone['zoneID']);
	}
	return setting_SELECT_BOX($Options, $_SettingID);
}

function setting_RADIOGROUP($_Options, $_SettingID){
	
	if(!is_array($_Options)){
	
		$_Options = explode(',',$_Options);
		$TC = count($_Options)-1;
		for(;$TC>=0;$TC--){
		
			$_Options[$TC] = explode(':', $_Options[$TC]);
			$_Options[$TC]['title'] = $_Options[$TC][0];
			if(!isset($_Options[$TC][1])){
				$_Options[$TC]['value'] = '';
			}else{
				$_Options[$TC]['value'] = $_Options[$TC][1];
			}
		}
	}
	$sql = "
		SELECT settings_constant_name 
		FROM ".SETTINGS_TABLE." 
		WHERE settingsID={$_SettingID}
	";
	$row = db_fetch_row( db_query($sql) );
	$settings_constant_name = $row["settings_constant_name"];
	
	if ( isset($_POST["save"]) )
		_setSettingOptionValue( $settings_constant_name, 	$_POST["setting_".$settings_constant_name] );

	$html = '';
	$TC = 0;
	$SettingConstantValue = _getSettingOptionValue($settings_constant_name);
	foreach ($_Options as $_Option){
		
		$html .= '<input class="inlradio" type="radio" name="setting_'.$settings_constant_name.'" value="'.htmlspecialchars($_Option['value']).'"'.($SettingConstantValue==$_Option['value']?' checked="checked"':'').' id="id_'.$settings_constant_name.$TC.'" />&nbsp;<label for="id_'.$settings_constant_name.$TC.'">'.htmlspecialchars($_Option['title']).'</label><br />';
		$TC++;
	}
	return $html;
}

function setting_SINGLE_FILE($_Path, $_SettingID){

	$Error = 0;
	$ConstantName = settingGetConstNameByID($_SettingID);
	if(isset($_POST['save']) && isset($_FILES['setting_'.$ConstantName])){
	
		if($_FILES['setting_'.$ConstantName]['name']){
			if(@copy($_FILES['setting_'.$ConstantName]['tmp_name'], $_Path.'/'.$_FILES['setting_'.$ConstantName]['name'])){
				_setSettingOptionValue($ConstantName, $_FILES['setting_'.$ConstantName]['name']);
			}else{
				$Error = 1;
			}
		}
	}
	
	$ConstantValue = _getSettingOptionValue($ConstantName);
	return ($Error?'<div>'.ERROR_FAILED_TO_UPLOAD_FILE.'</div>':'').'<input type="file" name="setting_'.$ConstantName.'" /><br />'.($ConstantValue?$ConstantValue:'&nbsp;');
}
?>