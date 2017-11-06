<?php
// RUPAY payment module
// http://www.rupay.com

/**
 * @connect_module_class_name CRUpay
 *
 */
class CRUpay extends PaymentModule{

	function _initVars(){
		
		$this->title 		= "RUpay (On-line �����)";
		$this->description 	= "������ � ������� RUpay - ����� ���������� \"On-line �����\". ���������: http://www.rupay.com";
		$this->sort_order 	= 1;
		
		$this->Settings = array( 
				"CONF_PAYMENTMODULE_RUPAY_MERCHANT_EMAIL",
				"CONF_PAYMENTMODULE_RUPAY_PAYMENTS_DESC",
				"CONF_PAYMENTMODULE_RUPAY_USD_CURRENCY"
			);
	}

	function _initSettingFields(){
		
		$this->SettingsFields['CONF_PAYMENTMODULE_RUPAY_MERCHANT_EMAIL'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Email (������������� � ������� RUpay)', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 2,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_RUPAY_PAYMENTS_DESC'] = array(
			'settings_value' 		=> '������ ������ �[orderID]', 
			'settings_title' 			=> '���������� �������', 
			'settings_description' 	=> '������� �������� ��������. �� ������ ������������ ������ [orderID] - ��� ������������� ����� �������� �� ����� ������', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 2,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_RUPAY_USD_CURRENCY'] = array(
			'settings_value' 		=> CONF_DEFAULT_CURRENCY, 
			'settings_title' 			=> '������ \"������� ���\" � ����� ��������', 
			'settings_description' 	=> '����� � ������, ������������ �� ������ RUpay, ����������� � �������� ��� (USD). �������� �� ������ ������� ��� � ����� �������� - ��� ���������� ��� ������� ��������� ����� (�� ����� �������). ���� ��� ������ �� ���������, ���� ��������� ������ 1', 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 3,
		);
	}
	
	function after_processing_html( $orderID ) 
	{
		$order = ordGetOrder( $orderID );

		//��������� order amount � USD
		if ( $this->_getSettingValue('CONF_PAYMENTMODULE_RUPAY_USD_CURRENCY') > 0 )
		{
			$RUpay_curr = currGetCurrencyByID ( $this->_getSettingValue('CONF_PAYMENTMODULE_RUPAY_USD_CURRENCY') );
			$RUpay_curr_rate = $RUpay_curr["currency_value"];
		}
		if (!isset($RUpay_curr) || !$RUpay_curr)
		{
			$RUpay_curr_rate = 1;
		}
		$order_amount = round(100*$order["order_amount"] * $RUpay_curr_rate)/100;

		$res = "";
		$res .= 
			"<table width='100%'>\n".
			"	<tr>\n".
			"		<td align='center'>\n".
			"		<form action=\"https://rupay.com/pay.php\" name=\"pay\" method=\"POST\">\n".
			"		<input type=\"hidden\" name=\"in_email\" value=\"".$this->_getSettingValue('CONF_PAYMENTMODULE_RUPAY_MERCHANT_EMAIL')."\">\n".
			"		<input type=\"hidden\" name=\"send_sum\" value=\"".$order_amount."\">\n".
			"		<input type=\"hidden\" name=\"name_service\" value=\"".str_replace("[orderID]",$orderID,$this->_getSettingValue('CONF_PAYMENTMODULE_RUPAY_PAYMENTS_DESC'))."\">\n".
			"		<input type=\"hidden\" name=\"order_id\" value=\"".$orderID."\">\n".
			"		<input type=\"hidden\" name=\"success_url\" value=\"".getTransactionResultURL('success')."\">\n".
			"		<input type=\"hidden\" name=\"fail_url\" value=\"".getTransactionResultURL('failure')."\">\n".
			"		<input type=\"submit\" name=\"button\" value=\"�������� ����� � ������� RUpay ������!\">\n".
			"		</form>\n".
			"		</td>\n".
			"	</tr>\n".
			"</table>";

		return $res;
	}
}
?>