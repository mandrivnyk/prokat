<?php
// RUPAY payment module
// http://www.rupay.com

/**
 * @connect_module_class_name CRupayPaymentRequest
 *
 */
class CRupayPaymentRequest extends PaymentModule {
	
	function _initVars(){
		
		$this->title 		= 'RUpay (������� ������ �� ����� ��������)';
		$this->description 	= '������ � ������� RUpay - ����� ���������� "������� ������ �� ����� ��������". ���������: http://www.rupay.com';
		$this->sort_order 	= 1;
		
		$this->Settings = array( 
				"CONF_CRUPAYPAYMENTREQUEST_PAY_ID",
				"CONF_CRUPAYPAYMENTREQUEST_NAME_SERVICE",
				"CONF_CRUPAYPAYMENTREQUEST_USD_CURRENCY",
			);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_CRUPAYPAYMENTREQUEST_PAY_ID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> '����� ������ ����� (��������) � ������� RUpay', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_CRUPAYPAYMENTREQUEST_NAME_SERVICE'] = array(
			'settings_value' 		=> '������ ������ �[orderID]', 
			'settings_title' 			=> '���������� �������', 
			'settings_description' 	=> '������� �������� ��������. �� ������ ������������ ������ [orderID] - ��� ������������� ����� �������� �� ����� ������', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_CRUPAYPAYMENTREQUEST_USD_CURRENCY'] = array(
			'settings_value' 		=> CONF_DEFAULT_CURRENCY, 
			'settings_title' 			=> '������ \"������� ���\" � ����� ��������', 
			'settings_description' 	=> '����� � ������, ������������ �� ������ RUpay, ����������� � �������� ��� (USD). �������� �� ������ ������� ��� � ����� �������� - ��� ���������� ��� ������� ��������� ����� (�� ����� �������). ���� ��� ������ �� ���������, ���� ��������� ������ 1', 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 3,
		);
	}

	function after_processing_html( $orderID ){
		
		$order = ordGetOrder( $orderID );
		
		if ( $this->_getSettingValue('CONF_CRUPAYPAYMENTREQUEST_USD_CURRENCY') > 0 )
		{
			$RUpay_curr = currGetCurrencyByID ( $this->_getSettingValue('CONF_CRUPAYPAYMENTREQUEST_USD_CURRENCY') );
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
			'<form action="http://rupay.ru/rupay/pay/index.php" name="pay" method="POST">
			
				<input type="hidden" name="pay_id" value="'.$this->_getSettingValue('CONF_CRUPAYPAYMENTREQUEST_PAY_ID').'">
				<input type="hidden" name="sum_pol" value="'.$order_amount.'">
				<input type="hidden" name="name_service" value="'.str_replace('[orderID]', $orderID, $this->_getSettingValue('CONF_CRUPAYPAYMENTREQUEST_NAME_SERVICE')).'">
				<input type="hidden" name="order_id" value="'.$orderID.'">
				<input type="submit" name="button" value="�������� � ������� RUpay ������">
			</form>'.
			"		</td>\n".
			"	</tr>\n".
			"</table>";

		return $res;
	}
}
?>