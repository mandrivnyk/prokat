<?
/**
 * @connect_module_class_name CInvoicePhys
 *
 */
// ������ ������������ ��������� �� ������ ��� ����������

define('CINVOICEPHYS_DB_TABLE','SS__module_payment_invoice_phys');

class CInvoicePhys extends PaymentModule {
	
	var $DB_TABLE = '';
	
	function _initVars(){
		
		$this->title 		= "���������";
		$this->description 	= "������ ������������ ��������� �� ������";
		$this->sort_order 	= 2;
		
		$this->Settings = array(
				"CONF_PAYMENTMODULE_INVOICE_PHYS_CURRENCY",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_DESCRIPTION",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_EMAIL_HTML_INVOICE",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_COMPANYNAME",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_BANK_ACCOUNT_NUMBER",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_INN",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_KPP",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_BANKNAME",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_BANK_KOR_NUMBER",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_BIK"
			);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_CURRENCY'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> '������ ���������', 
			'settings_description' 	=> '�������� ������, � ������� ����� ����������� ����� � ���������. ���� ��� ������ �� ���������, �� ��������� ����� ������������ � ��� ������, ������� ������� ������������� ��� ���������� ������', 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_DESCRIPTION'] = array(
			'settings_value' 		=> '������ ������ �[orderID]', 
			'settings_title' 			=> '�������� �������', 
			'settings_description' 	=> '������� �������� ��������. �� ������ ������������ ������ <i>[orderID]</i> - ��� ������������� ����� �������� �� ����� ������', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_EMAIL_HTML_INVOICE'] = array(
			'settings_value' 		=> '1', 
			'settings_title' 			=> '���������� ���������� HTML-���������', 
			'settings_description' 	=> '�������� ��� �����, ���� ������, ����� ���������� ������������� ������������ ��������� � HTML-�������. ���� ����� ���������, �� ���������� ����� ���������� ������ �� ��������� �� ����� ��������', 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_COMPANYNAME'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> '�������� ��������', 
			'settings_description' 	=> '������� �������� �����������, �� ����� ������� ������������ ���������', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_BANK_ACCOUNT_NUMBER'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> '��������� ����', 
			'settings_description' 	=> '����� ���������� ����� �����������', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_INN'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> '���', 
			'settings_description' 	=> '��� �����������', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_KPP'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> '���', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_BANKNAME'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> '������������ �����', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_BANK_KOR_NUMBER'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> '����������������� ����', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_BIK'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> '���', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);


		//������� �������, � ������� ����� ���������� ���������� ��� ���������
		// - ����� � ������ � ��������� ������
		if(!in_array(strtolower(CINVOICEPHYS_DB_TABLE), db_get_all_tables())){
			
			$sql = '
				CREATE TABLE '.CINVOICEPHYS_DB_TABLE.' 
				(module_id INT UNSIGNED, orderID INT, order_amount_string varchar(64))
			';
			db_query($sql);
		}
	}

	function after_processing_php( $orderID )
	{
		//��������� ����� ���������
		$orderID = (int) $orderID;
		$order = ordGetOrder( $orderID );
		if ($order)
		{
			$q = db_query("select count(*) from ".CINVOICEPHYS_DB_TABLE."  where orderID=$orderID AND module_id='{$this->ModuleConfigID}'");
			$row = db_fetch_row($q);
			if ($row[0] > 0) //������� ��� ������ ������
			{
				db_query("delete from ".CINVOICEPHYS_DB_TABLE." where orderID=$orderID AND module_id='{$this->ModuleConfigID}'");
			}

			//�������� ����� ������
			db_query("insert into ".CINVOICEPHYS_DB_TABLE." (module_id, orderID, order_amount_string) values (".$this->ModuleConfigID.", $orderID, '".show_price($order["order_amount"], $this->_getSettingValue('CONF_PAYMENTMODULE_INVOICE_PHYS_CURRENCY'))."' )");

			//��������� ��������� ���������� �� ����������� �����
			if ($this->_getSettingValue('CONF_PAYMENTMODULE_INVOICE_PHYS_EMAIL_HTML_INVOICE') == 1) //html
			{

				$mySmarty = new Smarty; //core smarty object
				//define smarty vars
				$mySmarty->template_dir = "./modules/templates/";
				$mySmarty->hassign( "billing_lastname", $order["billing_lastname"] );
				$mySmarty->hassign( "billing_firstname", $order["billing_firstname"] );
				$mySmarty->hassign( "billing_city", $order["billing_city"] );
				$mySmarty->hassign( "billing_address", $order["billing_address"] );
				$mySmarty->hassign( "invoice_description", str_replace("[orderID]", (string)$orderID, $this->_getSettingValue('CONF_PAYMENTMODULE_INVOICE_PHYS_DESCRIPTION')) );

				//����� ���������
				$q = db_query("select order_amount_string from ".CINVOICEPHYS_DB_TABLE." where orderID=$orderID AND module_id='{$this->ModuleConfigID}'");
				$row = db_fetch_row($q);
				if ($row) //����� ������� � ����� � ��������� ���������
				{
					$mySmarty->assign( "invoice_amount", $row[0] );
				}
				else //����� �� ������� - ���������� � ������� ������
				{
					$mySmarty->assign( "invoice_amount", show_price($order["order_amount"]) );
				}
				$mySmarty->assign('InvoiceModule', $this);
				
				$invoice = $mySmarty->fetch("invoice_phys.tpl.html");



				ss_mail($order["customer_email"],"��������� �� ������", $invoice, "From: \"".CONF_SHOP_NAME."\"<".CONF_GENERAL_EMAIL.">\n".
					stripslashes( str_replace( "text/plain", "text/html", EMAIL_MESSAGE_PARAMETERS ) )."\nReturn-path: <".
					CONF_GENERAL_EMAIL.">");
			}
			else //������ �� ���������
			{
				$URLprefix = trim( CONF_FULL_SHOP_URL );
				$URLprefix = str_replace("http://",  "", $URLprefix);
				$URLprefix = str_replace("https://", "", $URLprefix);
				if ($URLprefix[ strlen($URLprefix)-1 ] == '/')
				{
					$URLprefix = substr($URLprefix, 0, strlen($URLprefix)-1 );
				}

				$invoice_url = "http://" . $URLprefix . "/invoice_phys.php?moduleID=".$this->ModuleConfigID."&orderID=$orderID&order_time=" . base64_encode( $order["order_time_mysql"] ) . "&customer_email=" . base64_encode( $order["customer_email"] );

				ss_mail($order["customer_email"],"��������� �� ������","������������!\n\n������� �� ��� �����.\n��������� �� ������ �� ������ ���������� � ����������� �� ������:\n" . $invoice_url . "\n\n� ���������,\n".CONF_SHOP_NAME, "From: \"".CONF_SHOP_NAME."\"<".CONF_GENERAL_EMAIL.">\n".
					stripslashes(EMAIL_MESSAGE_PARAMETERS)."\nReturn-path: <".
					CONF_GENERAL_EMAIL.">");
			}

		}

		return "";
	}

	function after_processing_html( $orderID ) 
	{
		//������� ���� � ����������
		$order = ordGetOrder( $orderID );

		if(!$this->ModuleConfigID){
			
			$sql = '
				SELECT module_id FROM '.MODULES_TABLE.' WHERE module_name="'.$this->title.'"
			';
			@list($this->ModuleConfigID) = db_fetch_row(db_query($sql));
		}
		$res = "";

		$res .= 
			"<script>\n".
			"	open_window('invoice_phys.php?moduleID=".$this->ModuleConfigID."&orderID=$orderID&order_time=".base64_encode( $order["order_time_mysql"] )."&customer_email=".base64_encode( $order["customer_email"] )."',700,600);\n".
			"</script>\n";

		return $res;
	}

	function uninstall($_ModuleConfigID = 0){

		PaymentModule::uninstall($_ModuleConfigID);
		
		if(!count(modGetModuleConfigs(get_class($this)))){
			
			//������� ������� � ����������� � ������
			db_query("DROP TABLE IF EXISTS ".CINVOICEPHYS_DB_TABLE);
		}else {
			
			$sql = '
				DELETE FROM '.CINVOICEPHYS_DB_TABLE.' WHERE module_id="'.$this->ModuleConfigID.'"
			';
		}
	}
}
?>