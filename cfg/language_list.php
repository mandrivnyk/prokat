<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
//this file indicates listing of all available languages

class Language
{
	var $description; //language name
	var $filename; //language PHP constants file
	var $template; //template filename
	var $iso2;
}

	//a list of languages
	$lang_list = array();

	//to add new languages add similiar structures
	$lang_list[] = new Language();
	$tlang = &$lang_list[count($lang_list)-1];
	$tlang->description = "Русский";
	$tlang->filename = "russian.php";
	$tlang->template_path = "tmpl0";
	$tlang->iso2 = "ru";

	$lang_list[] = new Language();
	$tlang = &$lang_list[count($lang_list)-1];
	$tlang->description = "English";
	$tlang->filename = "english.php";
	$tlang->template_path = "tmpl0";
	$tlang->iso2 = "en";
?>