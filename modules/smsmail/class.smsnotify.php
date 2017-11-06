<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
require_once('./classes/class.virtual.module.php');

function SMSNotify_getSMSSenderConfigIDOptions(){
	
	$SMSMailObjects = modGetAllInstalledModuleObjs(SMSMAIL_MODULE);
	$Options = array(array('title'=>'-', 'value'=>'0'));
	$_TC = count($SMSMailObjects);
	for($_i=0;$_i<$_TC;$_i++){
		
		$Options[] = array(
			'title' => $SMSMailObjects[$_i]->getTitle(),
			'value' => $SMSMailObjects[$_i]->getModuleConfigID(),
			);
	}
	return $Options;
}

function SMSNotify_setting_PERIOD($_SettingID){
	
	$SettingConstName = settingGetConstNameByID($_SettingID);
	if (isset($_POST['save']) && isset($_POST[$SettingConstName.'_from']) && isset($_POST[$SettingConstName.'_till'])) {
		
		$dfrom = explode(':', $_POST[$SettingConstName.'_from']);
		$dtill = explode(':', $_POST[$SettingConstName.'_till']);
		if(!isset($dfrom[1]))
			$dfrom[1] = '';
		if(!isset($dtill[1]))
			$dtill[1] = '';
		if(	$dfrom[0]>23 || $dfrom[0]<0 || $dfrom[1]>59 || $dfrom[1]<0 ||
			$dtill[0]>23 || $dtill[0]<0 || $dtill[1]>59 || $dtill[1]<0 ||
			!preg_match('/^\d\d\:\d\d$/',$_POST[$SettingConstName.'_from']) 
			|| !preg_match('/^\d\d\:\d\d$/',$_POST[$SettingConstName.'_till'])){
			
			return array(
				'error'=>STRING_SMS_NOTIFY_ERROR_PERIOD,
				'html'=>'
	<table>
		<tr>
			<td><input name="'.$SettingConstName.'_from" value="'.html_spchars($_POST[$SettingConstName.'_from']).'" type="text" size="5" /></td>
			<td> - </td>
			<td><input name="'.$SettingConstName.'_till" value="'.html_spchars($_POST[$SettingConstName.'_till']).'" type="text" size="5" /></td>
		</tr>
		<tr>
			<td>HH:MM</td>
			<td></td>
			<td>HH:MM</td>
		</tr>
	</table>
		'
			);
		}
		_setSettingOptionValueByID($_SettingID, $_POST[$SettingConstName.'_from'].'-'.$_POST[$SettingConstName.'_till']);
	}
	
	$SettingConstVal = _getSettingOptionValueByID($_SettingID);
	@list($FromTime,$TillTime) = explode('-',$SettingConstVal);
	return '
	<table>
		<tr>
			<td><input name="'.$SettingConstName.'_from" value="'.html_spchars($FromTime).'" type="text" size="5" /></td>
			<td> - </td>
			<td><input name="'.$SettingConstName.'_till" value="'.html_spchars($TillTime).'" type="text" size="5" /></td>
		</tr>
		<tr>
			<td>HH:MM</td>
			<td></td>
			<td>HH:MM</td>
		</tr>
	</table>
		';
}

function SMSNotify_setting_Phones($_SettingID){
	
	$SettingConstName = settingGetConstNameByID($_SettingID);
	
	if (isset($_POST['save']) && isset($_POST[$SettingConstName])){
		
		$_ind = 0;
		$savePhones = array();
		foreach ($_POST[$SettingConstName] as $_ind=>$_val){
			
			if($_POST[$SettingConstName][$_ind] && $_ind<15 && !in_array($_POST[$SettingConstName][$_ind], $savePhones) && $_POST[$SettingConstName][$_ind]!=STRING_SMS_NOTIFY_NEW_PHONE){
				$savePhones[] = $_POST[$SettingConstName][$_ind];
			}
		}
		
		_setSettingOptionValueByID($_SettingID, implode(',', $savePhones));
	}
	
	$SettingConstVal = _getSettingOptionValueByID($_SettingID);
	$Phones = explode(',', $SettingConstVal);
	$HTML= '';
	foreach ($Phones as $_Phone){
		
		if($_Phone)
		$HTML .= '<input name="'.$SettingConstName.'[]" value="'.xHtmlSpecialChars($_Phone).'" type="text" /><br />';
	}
	$HTML .= '<hr /><input name="'.$SettingConstName.'[]" style="color:#aaaaaa" value="'.STRING_SMS_NOTIFY_NEW_PHONE.'" type="text" onfocus="if(this.value==\''.STRING_SMS_NOTIFY_NEW_PHONE.'\'){this.style.color=\'#000000\';this.value=\'\';}" onblur="if(this.value==\'\'){this.style.color=\'#aaaaaa\';this.value=\''.STRING_SMS_NOTIFY_NEW_PHONE.'\'}" /><br />';
	return $HTML;
}

class SMSNotify extends virtualModule {
	
	var $SingleInstall = true;
	
	function _initVars(){

		$this->title = 'SMS-уведомления';
		
		$this->Settings = array(
			'CONF_SMSNOTIFY_ENABLED',
			'CONF_SMSNOTIFY_SMSSENDER_CONFIG_ID',
			'CONF_SMSNOTIFY_SEND_PERIOD',
			'CONF_SMSNOTIFY_PHONES',
			);
	}
	
	function _initSettingFields(){
		
		$this->SettingsFields['CONF_SMSNOTIFY_ENABLED'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> '', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> '', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSNOTIFY_SMSSENDER_CONFIG_ID'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> '', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_SELECT_BOX(SMSNotify_getSMSSenderConfigIDOptions(),', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSNOTIFY_SEND_PERIOD'] = array(
			'settings_value' 		=> '00:00-23:59', 
			'settings_title' 			=> '', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'SMSNotify_setting_PERIOD(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_SMSNOTIFY_PHONES'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> '', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'SMSNotify_setting_Phones(', 
			'sort_order' 			=> 1,
		);
	}

	function onEvent($_Event, $_Params){
		
		if(!$this->is_installed())return 0;
		if(!$this->_getSettingValue('CONF_SMSNOTIFY_ENABLED')) return 0;
		
		switch ($_Event){
			case 'new_order':
				$_tSmarty = new Smarty();
				$_tSmarty->assign('OrderNumber', $_Params['OrderNumber']);
				$_tSmarty->assign('OrderAmount', $_Params['OrderAmount']);
				$this->notify($_tSmarty->fetch('sms/smsmail_neworder.tpl.html'));
		}
	}
	
	function notify($_Message){
		
		$SMSsender = modGetModuleObj($this->_getSettingValue('CONF_SMSNOTIFY_SMSSENDER_CONFIG_ID'), SMSMAIL_MODULE);
		$Phones = explode(',', $this->_getSettingValue('CONF_SMSNOTIFY_PHONES'));
		
		if(preg_match('/^\d\d\:\d\d\-\d\d\:\d\d$/', $this->_getSettingValue('CONF_SMSNOTIFY_SEND_PERIOD'))){

			@list($FromTime,$TillTime) = explode('-', $this->_getSettingValue('CONF_SMSNOTIFY_SEND_PERIOD'));
			
			$FromTime = explode(':', $FromTime);
			$FromTime = $FromTime[0]*60+$FromTime[1];
			
			$TillTime = explode(':',$TillTime);
			$TillTime = $TillTime[0]*60+$TillTime[1];
			
			$CurrentTime = date("H")*60+date("i");
			
			if($TillTime>=$FromTime){
				if(!($TillTime>=$CurrentTime&&$CurrentTime>=$FromTime))return 0;
			}
			else {
				if($CurrentTime<$FromTime&&$CurrentTime>$TillTime)return 0;
			}
		}
		
		if(!$SMSsender)return 0;
		if(!count($Phones))return 0;
		
		$SMSsender->sendSMS($_Message, $Phones);
		return 1;
	}
}
?>