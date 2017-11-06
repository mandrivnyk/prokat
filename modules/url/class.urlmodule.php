<?php

?><?php
class Url extends virtualModule {

	var $DB_TABLE = '';
	var $UrlPerPage = 10;
	var $UrlInShortList = CONF_NEWS_COUNT_IN_CUSTOMER_PART;
	var $CurrentPage = 1;
	var $TotalPages = 1;

	function Url($_ModuleConfigID = 0){

		$this->ModuleType = 0;
		$this->SingleInstall = true;
		$this->ModuleVersion = 1.0;

		if(defined('URL_TABLE')){

			$this->DB_TABLE = CATEGORIES_TABLE;
		}else{

			$this->DB_TABLE = 'SS_categories';
		}



		virtualModule::virtualModule($_ModuleConfigID);
	}

	function generatePage($_PageName){

		global $smarty, $_t;

		//echo $_PageName;
		//exit();
		switch($_PageName){


			case 'admin url list':
					$url_new = '';
					$Url_arr = $this->getUrl();
					$Url_base_str = $this->getHtaccess_base();
					$fp = fopen('./.htaccess', 'w');
					fwrite($fp, $Url_base_str);
					for($i=0;$i<count($Url_arr);$i++)
					{
						$url_new .= $Url_arr[$i]['url_rewrite'].chr(10).chr(13);
					}
					$res = fwrite($fp, $url_new);
					fclose($fp);
					$smarty->assign( "status_ok", 'Перекомпилировано успешно' );
				$smarty->assign( "admin_sub_dpt", 'url.admin_list.tpl.html' );
				break;
		}
	}

	/**
	 * Handler for actions. Work with external data without checking admin mode.
	 * Should be called only from backoffice
	 */


	function getUrlNumber(){

		$sql = '
			SELECT COUNT(*) FROM '.$this->DB_TABLE.'
		';

		@list($Number) = db_fetch_row(db_query($sql));
		return $Number;
	}

	function getUrl(){

			//----------Собираем URL с таблицы категорий-----------------------------------
			$sql = 'SELECT categoryID, url_name FROM '.$this->DB_TABLE.'';
			$Result = db_query($sql);
			$i=0;
			while($_Row = db_fetch_row($Result))
			{
				$Url[$i][0] = $_Row['categoryID'];
				$Url[$i][1] = $_Row['url_name'];
				$Url[$i][2] = 'RewriteRule ^'.$_Row['url_name'].'$ categoryID-'.$_Row['categoryID'].'.html';
				$Url[$i]['categoryID'] = $_Row['categoryID'];
				$Url[$i]['url_name'] = $_Row['url_name'];
				$Url[$i++]['url_rewrite'] = 'RewriteRule ^'.$_Row['url_name'].'$ categoryID-'.$_Row['categoryID'].'.html';

			}


			//----------Собираем URL с таблицы продуктов-----------------------------------
			$sql = 'SELECT productID, url_name FROM '.PRODUCTS_TABLE.'';
			$Result = db_query($sql);

			while($_Row = db_fetch_row($Result))
			{
				$Url[$i][0] = $_Row['productID'];
				$Url[$i][1] = $_Row['url_name'];
				$Url[$i][1] = 'RewriteRule ^'.$_Row['url_name'].'$ productID-'.$_Row['productID'].'.html';
				$Url[$i]['productID'] = $_Row['productID'];
				$Url[$i]['url_name'] = $_Row['url_name'];
				$Url[$i++]['url_rewrite'] = 'RewriteRule ^'.$_Row['url_name'].'$ productID-'.$_Row['productID'].'.html';
			}

			//----------Собираем URL с таблицы дополнительных страниц-----------------------------------
			$sql = 'SELECT aux_page_ID, url_name FROM '.AUX_PAGES_TABLE.'';
			$Result = db_query($sql);

			while($_Row = db_fetch_row($Result))
			{
				$Url[$i][0] = $_Row['aux_page_ID'];
				$Url[$i][1] = $_Row['url_name'];
				$Url[$i][1] = 'RewriteRule ^'.$_Row['url_name'].'$ show_aux_page-'.$_Row['aux_page_ID'].'.html';
				$Url[$i]['aux_page_ID'] = $_Row['aux_page_ID'];
				$Url[$i]['url_name'] = $_Row['url_name'];
				$Url[$i++]['url_rewrite'] = 'RewriteRule ^'.$_Row['url_name'].'$ show_aux_page-'.$_Row['aux_page_ID'].'.html';
			}
		/*echo '<pre>';
		print_r($Url);
		echo '</pre>';*/
		//$Url
		return $Url;
	}

	function getHtaccess_base()
	{
		
		$file = file_get_contents('./htaccess_base.txt');
		
		return $file;
	}

	function saveUrl($_Info){

		$FieldsSQL = array();
		foreach ($_Info as $_Key=>$_Val){

			$FieldsSQL[] = '`'.xEscapeSQLstring($_Key).'`="'.xEscapeSQLstring($_Val).'"';
		}

		if(!count($FieldsSQL))return false;

		$FieldsSQL = implode(', ', $FieldsSQL);

		$sql = '
			UPDATE '.$this->DB_TABLE.'
			SET '.$FieldsSQL.'
			WHERE NID="'.xEscapeSQLstring($_Info['NID']).'"
		';
		db_query($sql);
		return true;
	}

	function addUrl($_Info){

		if(!count($_Info))return false;

		$sql = '
			INSERT INTO '.$this->DB_TABLE.'
			(`'.implode('`, `', xEscapeSQLstring(array_keys($_Info))).'`)
			VALUES("'.implode('", "', xEscapeSQLstring($_Info)).'")
		';
		db_query($sql);

		return db_insert_id();
	}

	function sendUrl($_ID){

		$sql = '
			SELECT textToMail FROM '.$this->DB_TABLE.'
			WHERE NID="'.xEscapeSQLstring($_ID).'"
		';
		$Url = db_fetch_row(db_query($sql));

		$callBackParam = '';
		$count_row = '';
		$Subscribers = subscrGetAllSubscriber( $callBackParam, $count_row);

		foreach ($Subscribers as $subscriber){

			if (eregi("^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$", $subscriber["Email"])){

				ss_mail($subscriber["Email"], EMAIL_NEWS_OF." ".
					CONF_SHOP_NAME, $Url["textToMail"]."\n\n".
					EMAIL_SINCERELY.", ".
					CONF_SHOP_NAME."\n".
					CONF_SHOP_URL, "From: \"".
					CONF_SHOP_NAME."\"<".
					CONF_GENERAL_EMAIL.">\n".stripslashes(EMAIL_MESSAGE_PARAMETERS).
					"\nReturn-path: <".CONF_GENERAL_EMAIL.">" );
			}
		}
	}

	function deleteUrl($_ID){

		$sql = '
			DELETE FROM '.$this->DB_TABLE.'
			WHERE NID="'.xEscapeSQLstring($_ID).'"
		';
		db_query($sql);
		return true;
	}
}
?>