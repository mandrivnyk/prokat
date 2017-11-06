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
'à'=>'a',
'á'=>'b',
'â'=>'v',
'ã'=>'g',
'ä'=>'d',
'å'=>'e',
'¸'=>'jo',
'æ'=>'zh',
'ç'=>'z',
'è'=>'i',
'é'=>'jj',
'ê'=>'k',
'ë'=>'l',
'ì'=>'m',
'í'=>'n',
'î'=>'o',
'ï'=>'p',
'ð'=>'r',
'ñ'=>'s',
'ò'=>'t',
'ó'=>'u',
'ô'=>'f',
'õ'=>'kh',
'ö'=>'c',
'÷'=>'ch',
'ø'=>'sh',
'ù'=>'shh',
'ú'=>'"',
'û'=>'y',
'ü'=>"'",
'ý'=>'eh',
'þ'=>'yu',
'ÿ'=>'ya',
'À'=>'A',
'Á'=>'B',
'Â'=>'V',
'Ã'=>'G',
'Ä'=>'D',
'Å'=>'E',
'¨'=>'JO',
'Æ'=>'ZH',
'Ç'=>'Z',
'È'=>'I',
'É'=>'JJ',
'Ê'=>'K',
'Ë'=>'L',
'Ì'=>'M',
'Í'=>'N',
'Î'=>'O',
'Ï'=>'P',
'Ð'=>'R',
'Ñ'=>'S',
'Ò'=>'T',
'Ó'=>'U',
'Ô'=>'F',
'Õ'=>'KH',
'Ö'=>'C',
'×'=>'CH',
'Ø'=>'SH',
'Ù'=>'SHH',
'Ú'=>'"',
'Û'=>'Y',
'Ü'=>"'",
'Ý'=>'EH',
'Þ'=>'YU',
'ß'=>'YA',
		));
		return $s;
	}
}
?>