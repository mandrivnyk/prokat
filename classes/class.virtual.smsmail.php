<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
require_once('./classes/class.virtual.module.php');

class SMSMail extends virtualModule {
	
	var $Request;
	var $Responce;
	
	/**
	 * constructor
	 *
	 * @param integer $_ModuleConfigID - module config id
	 */
	function SMSMail($_ModuleConfigID = 0){
		
		$this->LanguageDir = './modules/smsmail/languages/';
		$this->ModuleType = SMSMAIL_MODULE;
		
		virtualModule::virtualModule($_ModuleConfigID);
	}

	/**
	 * Send SMS-message by phone lists
	 *
	 * @param string $_SMSMessage - sms message
	 * @param array or string $_PhonesList - phone list
	 * @param array $_Params - some params
	 * @return parsed responce
	 */
	function sendSMS($_SMSMessage, $_PhonesList, $_Params = array()){
		
		if(!is_array($_PhonesList)){
			
			$_PhonesList = array($_PhonesList);
		}
		
		$this->Request 	= $this->_prepareRequest($_SMSMessage, $_PhonesList, $_Params);
		$this->Responce 	= $this->_sendRequest($this->Request);
		return $this->_parseResponce($this->Responce);
	}

	/**
	 * Prepare request for sending SMS
	 *
	 * @param string $_SMSMessage
	 * @param array or string $_PhonesList
	 * @param array $_Params
	 */
	function _prepareRequest($_SMSMessage, $_PhonesList, $_Params){
		
		;
	}
	
	/**
	 * Send request for sms sending
	 *
	 * @param unknown_type $_Request
	 */
	function _sendRequest($_Request){
		
		;
	}
	
	/**
	 * Parse responce on sms-sending request
	 *
	 * @param unknown_type $_Responce
	 */
	function _parseResponce($_Responce){
		
		;
	}
	
	function _translit($_Message){
		
		$s=strtr($_Message,array(
'�'=>'a',
'�'=>'b',
'�'=>'v',
'�'=>'g',
'�'=>'d',
'�'=>'e',
'�'=>'jo',
'�'=>'zh',
'�'=>'z',
'�'=>'i',
'�'=>'jj',
'�'=>'k',
'�'=>'l',
'�'=>'m',
'�'=>'n',
'�'=>'o',
'�'=>'p',
'�'=>'r',
'�'=>'s',
'�'=>'t',
'�'=>'u',
'�'=>'f',
'�'=>'kh',
'�'=>'c',
'�'=>'ch',
'�'=>'sh',
'�'=>'shh',
'�'=>'"',
'�'=>'y',
'�'=>"'",
'�'=>'eh',
'�'=>'yu',
'�'=>'ya',
'�'=>'A',
'�'=>'B',
'�'=>'V',
'�'=>'G',
'�'=>'D',
'�'=>'E',
'�'=>'JO',
'�'=>'ZH',
'�'=>'Z',
'�'=>'I',
'�'=>'JJ',
'�'=>'K',
'�'=>'L',
'�'=>'M',
'�'=>'N',
'�'=>'O',
'�'=>'P',
'�'=>'R',
'�'=>'S',
'�'=>'T',
'�'=>'U',
'�'=>'F',
'�'=>'KH',
'�'=>'C',
'�'=>'CH',
'�'=>'SH',
'�'=>'SHH',
'�'=>'"',
'�'=>'Y',
'�'=>"'",
'�'=>'EH',
'�'=>'YU',
'�'=>'YA',
		));
		return $s;
	}
}
?>