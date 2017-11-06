<?php
/**
 * @connect_module_class_name SMSTrafficRu
 */

class SMSTrafficRu extends SMSMail {
	
	function _initVars(){
		
		$this->title = 'SMS traffic';
		$this->description = '<a href="http://www.smstraffic.ru">www.smstraffic.ru</a>';
		$this->sort_order = 0;
		
		$this->Settings[] = 'CONF_SMSTRAFFICRU_LOGIN';
		$this->Settings[] = 'CONF_SMSTRAFFICRU_PASSWORD';
		$this->Settings[] = 'CONF_SMSTRAFFICRU_RUS';
		$this->Settings[] = 'CONF_SMSTRAFFICRU_ORIGINATOR';
	}
	
	function _initSettingFields(){
		
		$this->SettingsFields['CONF_SMSTRAFFICRU_LOGIN'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> '�����', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSTRAFFICRU_PASSWORD'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> '������', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSTRAFFICRU_RUS'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> '���������� �� ��������� �� ������(������������ ����� 70 ��������)', 
			'settings_description' 	=> '���� ��� ��������� ����� �����������������.', 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSTRAFFICRU_ORIGINATOR'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> '����������� ���������, ��� �� ����� ��������� �� �������� ����������', 
			'settings_description' 	=> '����������� ����� ���� ��������, � ���� ������ ��� ����� ���������� 15-� ���������, ��� ��������-�������� (��������, �������� ����� ��������), � ���� ������ ����� ���������� 11-� ���������. ������� ����� � ����� ����������� �� ���������. �� ��������� �������� originator=999.', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
	}

	function _prepareRequest($_SMSMessage, $_PhonesList, $_Params){

		if(!$_SMSMessage)return null;
		if(!count($_PhonesList))return null;
		
		return array(
			'login' => $this->_getSettingValue('CONF_SMSTRAFFICRU_LOGIN'),
			'password' => $this->_getSettingValue('CONF_SMSTRAFFICRU_PASSWORD'),
			'phones' => implode(',',$_PhonesList),
			'message' => $_SMSMessage,
			'rus' => $this->_getSettingValue('CONF_SMSTRAFFICRU_RUS'),
			'originator' => $this->_getSettingValue('CONF_SMSTRAFFICRU_ORIGINATOR'),
		);
	}
	
	function _sendRequest($_Request){
		
		$url = 'https://www.smstraffic.ru/multi.php';
		
		if ( !($ch = curl_init()) ){
			
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Local error: '.ERR_CURLINIT);
			return ERR_CURLINIT;
		}

		if ( curl_errno($ch) != 0 ){
			
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Curl error: '.curl_errno($ch));
			return ERR_CURLINIT;
		}

		@curl_setopt($ch, CURLOPT_URL, $url );
		@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		@curl_setopt($ch, CURLOPT_HEADER, 0);
		@curl_setopt($ch, CURLOPT_POST, 1);
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $_Request);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		@curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );

		$result = @curl_exec($ch);
		if ( curl_errno($ch) != 0){
			
			$this->_writeLogMessage(MODULE_LOG_CURL, 'Curl error: '.curl_errno($ch));
			return ERR_CURLEXEC;
		}

		curl_close($ch);
		return $result;
	}
	
	function _parseResponce($_Responce){
		;
	}
}
?>