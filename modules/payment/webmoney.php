<?php
/**
 * @connect_module_class_name CWebMoney
 *
 */
// WebMoney method implementation
// see also 
//		http://www.webmoney.ru
//		https://merchant.webmoney.ru/conf/guide.asp#properties
	
class CWebMoney extends PaymentModule {
	
	function _initVars(){
		
		$this->title 		= "WebMoney";
		$this->description 	= "WebMoney Merchant Interface (www.webmoney.ru)<br>��������: ����� ����, ��� ������ ����� ����������, ��� ���������� �������� ����� ������ �������� ����� Merchant Interface � ����� ������� ������ WebMoney";
		$this->sort_order 	= 0;
		$this->Settings = array( 
				"CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_PURSE", 
				"CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_EXCHANGERATE",
				"CONF_PAYMENTMODULE_WEBMONEY_TESTMODE",
				"CONF_PAYMENTMODULE_WEBMONEY_PAYMENTS_DESC"  
			);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_PURSE'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> '����� ��������, �� ������� ����� ����������� ������ � ����� ��������', 
			'settings_description' 	=> '������ - ����� � 12 ����', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_EXCHANGERATE'] = array(
			'settings_value' 		=> '1', 
			'settings_title' 			=> '���� �.�. �������� �� ��������� � ������ Web-Money', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(1,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_WEBMONEY_TESTMODE'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> '�������� �����', 
			'settings_description' 	=> '����������� �������� ����� ��� �������� ������', 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_WEBMONEY_PAYMENTS_DESC'] = array(
			'settings_value' 		=> '������ ������ #[orderID]', 
			'settings_title' 			=> '���������� ��������', 
			'settings_description' 	=> '������� �������� ��������. �� ������ ������������ ������ [orderID] - ��� ������������� ����� �������� �� ����� ������', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
	}

	function after_processing_html( $orderID ) 
	{
		$order = ordGetOrder( $orderID );
		$order_amount = $order["order_amount"];

		$exhange_rate = (float)$this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_EXCHANGERATE');
		if ( (float)$exhange_rate == 0 )
			$exhange_rate = 1;

		$order_amount = $order_amount/((float)$this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_EXCHANGERATE'));

		$res = "";
		$res .= 
			"<table width='100%'>\n".
			"	<tr>\n".
			"		<td align='center'>\n".
			"<form method='POST' action='https://merchant.webmoney.ru/lmi/payment.asp'>\n".
			"	<input type='hidden' name='LMI_PAYMENT_AMOUNT' value='".$order_amount."'>\n".
			"	<input type='hidden' name='LMI_PAYMENT_DESC' value='".str_replace("[orderID]",$orderID,$this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_PAYMENTS_DESC'))."'>\n".
			"	<input type='hidden' name='LMI_PAYMENT_NO' value='".$orderID."'>\n".
			"	<input type='hidden' name='LMI_PAYEE_PURSE' value=".$this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_MERCHANT_PURSE').">\n".
			"	<input type='hidden' name='LMI_MODE' value=".$this->_getSettingValue('CONF_PAYMENTMODULE_WEBMONEY_TESTMODE').">\n".
			"	<input type='submit' value='�������� �� WebMoney ������!' align='center'>\n".
			"</form>\n".
			"		</td>\n".
			"	</tr>\n".
			"</table>";
		return $res;
	}
}
?>