<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
class IValidator{

	var $Themes;
	var $Fonts;
	var $Width = 100;
	var $Height = 40;
	var $BorderWidth = 1;
	var $ImageType = 'jpeg';
	var $FontsDir = '.';
	var $RndCodes = 'qwertyuiopasdfghjklzxcvbnm0123456789';
	var $RndLength = 6;
	var $SPrefix = 'SS_';
	
	function IValidator(){
		
		$this->Themes = array (
			array(
				'background' => array(225, 60, 50),
				'border' => array(0, 0, 0),
				'font' => array(0, 0, 0),
			),
			array(
				'background' => array(205, 255, 204),
				'border' => array(0, 0, 0),
				'font' => array(0, 0, 0),
			),
			array(
				'background' => array(252,252,150),
				'border' => array(0, 0, 0),
				'font' => array(60,60,150),
			),
			array(
				'background' => array(160, 160, 227),
				'border' => array(0, 0, 0),
				'font' => array(32,69,38),
			),
		);
		$this->Fonts = array(
			'arial.ttf',
			'verdana.ttf',
		);
	}
	
	function generateImage($_Code = ''){
		
		if(!$_Code)$_Code = $this->rndCode();
		
		$this->storeCode($_Code);
		
		$Theme = mt_rand(0, count($this->Themes)-1);
		$Theme = $this->Themes[$Theme];
		$FontFile = mt_rand(0, count($this->Fonts)-1);
		$FontFile = $this->FontsDir.'/'.$this->Fonts[$FontFile];

		if(function_exists('imagecreatetruecolor'))
			$Image = imagecreatetruecolor($this->Width, $this->Height);
		else 
			$Image = imagecreate($this->Width, $this->Height);
		
		$Fill   = ImageColorAllocate($Image, $Theme['background'][0], $Theme['background'][1], $Theme['background'][2]);
		$Border = ImageColorAllocate($Image, $Theme['border'][0], $Theme['border'][1], $Theme['border'][2]);
		
		ImageFilledRectangle($Image, $this->BorderWidth, $this->BorderWidth, $this->Width-$this->BorderWidth-1, $this->Height-$this->BorderWidth-1, $Fill);
		ImageRectangle($Image, 0, 0, $this->Width-1, $this->Height-1, $Border);
		
	        $Font	= imagecolorallocate($Image, $Theme['font'][0], $Theme['font'][1], $Theme['font'][2]);
	
	        $TrFontSize = 14;
	        $_TC = strlen($_Code)-1;
	        $LettersStart = 5;
	        $LetterOffset = ceil(($this->Width-$LettersStart*2)/($_TC+1));
	        for(;$_TC>=0;$_TC--){
	        	
	        	$RSize = mt_rand(3, 5);
	        	imagestring($Image,$RSize,$LettersStart+($_TC)*$LetterOffset, $RSize*2+$RSize, $_Code{$_TC}, $Font);
//	        	imagettftext($Image, $TrFontSize+$RSize, 0, $LettersStart+($_TC)*$LetterOffset, 25+$RSize*2, $Font, $FontFile, $_Code{$_TC});
	        }
	        
		if(0 && function_exists('imagecreatetruecolor')){
			
		        $TrFont 	= imagecolorallocatealpha($Image, $Theme['font'][0], $Theme['font'][1], $Theme['font'][2], 100);  
		        $TrFontSize = 20;
		        $_TC = strlen($_Code)-1;
		        $LetterOffset = ceil($this->Width/($_TC+1));
		        for(;$_TC>=0;$_TC--){
		        	
		        	$RSize = mt_rand(1, 5);
		        	imagettftext($Image, $TrFontSize+$RSize, 0, ($_TC)*$LetterOffset, 25+$RSize, $TrFont, $FontFile, $_Code{$_TC});
		        }
		}
		
	        if($this->ImageType == "jpeg"){
	        	
	            header("Content-type: image/jpeg");
	            imagejpeg($Image, false, 95);
	        }else{
	        	
	            header("Content-type: image/png");
	            imagepng($Image);
	        }
	
	        imagedestroy($Image);
	}

	function rndCode(){
		
		$l_name='';
		$top = strlen($this->RndCodes)-1;
		srand((double) microtime()*1000000);
		for($j=0; $j<$this->RndLength; $j++)$l_name .= $this->RndCodes{rand(0,$top)};
		return $l_name;
	}
	
	function storeCode($_Code){
		
		if(!session_is_registered($this->SPrefix.'IVAL')){
			
			session_register($this->SPrefix.'IVAL');
		}
		$_SESSION[$this->SPrefix.'IVAL'] = $_Code;
	}
	
	function checkCode($_Code){
		
		if(!session_is_registered($this->SPrefix.'IVAL'))return false;
		if(!$_Code)return false;
		if($_SESSION[$this->SPrefix.'IVAL'] == $_Code){
			
			return true;
		}else{
			
			$_SESSION[$this->SPrefix.'IVAL'] = '';
			return false;
		}
	}
}
?>