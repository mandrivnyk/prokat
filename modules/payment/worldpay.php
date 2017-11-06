<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
// WorldPay payment module
// http://www.worldpay.com

/**
 * @connect_module_class_name CWorldPay
 *
 */
class CWorldPay extends PaymentModule {
	
	function _initVars(){
		
		$this->title 		= CWORLDPAY_TTL;
		$this->description 	= CWORLDPAY_DSCR;
		$this->sort_order 	= 2;
		
		$this->Settings = array( 
				"CONF_PAYMENTMODULE_WORLDPAY_INSTID",
				"CONF_PAYMENTMODULE_WORLDPAY_TESTMODE"
			);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_PAYMENTMODULE_WORLDPAY_INSTID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CWORLDPAY_CFG_INSTID_TTL, 
			'settings_description' 	=> CWORLDPAY_CFG_INSTID_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);

		$this->SettingsFields['CONF_PAYMENTMODULE_WORLDPAY_TESTMODE'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CWORLDPAY_CFG_TESTMODULE_TTL, 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 2,
		);
	}

	function after_processing_html( $orderID ) 
	{
		$order = ordGetOrder( $orderID );
		$order_amount = round(100*$order["order_amount"] * $order["currency_value"])/100;

		$res = "";

		$q = db_query("select country_iso_2 from ".COUNTRIES_TABLE." where country_name = '".$order["billing_country"]."';") or die (db_error());
		$row = db_fetch_row($q);
		if (!$row) //country is not defined
		{
			$country = "US";
		}
		else //fetch country ISO 2
		{
			$country = $row[0];
		}

		$testmode = $this->_getSettingValue('CONF_PAYMENTMODULE_WORLDPAY_TESTMODE') ? "100" : "0";

		$res .= 
			"<table width='100%'>\n".
			"	<tr>\n".
			"		<td align='center'>\n".
			"<form method='POST' action='https://select.worldpay.com/wcc/purchase'>\n".
			"<input type=\"hidden\" name=\"instId\" value=\"".$this->_getSettingValue('CONF_PAYMENTMODULE_WORLDPAY_INSTID')."\">\n".
			"<input type=\"hidden\" name=\"desc\" value=\"".CONF_SHOP_NAME." - Order #".$orderID."\">\n".
			"<input type=\"hidden\" name=\"cartId\" value=\"".$orderID."\">\n".
			"<input type=\"hidden\" name=\"amount\" value=\"".$order_amount."\">\n".
			"<input type=\"hidden\" name=\"currency\" value=\"".$order["currency_code"]."\">\n".
			"<input type=\"hidden\" name=\"testMode\" value=\"".$testmode."\">\n".
			"<input type=\"hidden\" name=\"country\" value=\"".$country."\">\n".
			"<input type=\"hidden\" name=\"postcode\" value=\"".$order["billing_zip"]."\">\n".
			"<input type=\"hidden\" name=\"address\" value=\"".str_replace("\n","&#10;",$order["billing_address"])."\">\n".
			"<input type=\"hidden\" name=\"email\" value=\"".$order["customer_email"]."\">\n".
			"<input type=\"submit\" value=\"".CWORLDPAY_TXT_AFTER_PROCESSING_HTML_1."\"></form>\n".		
			"		</td>\n".
			"	</tr>\n".
			"</table>";

		return $res;
	}
}
?>