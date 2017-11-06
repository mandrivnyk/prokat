<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
class News extends virtualModule {
	
	var $DB_TABLE = '';
	var $NewsPerPage = 10;
	var $NewsInShortList = CONF_NEWS_COUNT_IN_CUSTOMER_PART;
	var $CurrentPage = 1;
	var $TotalPages = 1;
	
	function News($_ModuleConfigID = 0){
		
		$this->ModuleType = 0;
		$this->SingleInstall = true;
		$this->ModuleVersion = 1.0;

		if(defined('NEWS_TABLE')){
			
			$this->DB_TABLE = NEWS_TABLE;
		}else{
			
			$this->DB_TABLE = 'SS_news_table';
		}
		
		$this->TotalPages = ceil($this->getNewsNumber()/$this->NewsPerPage);
		if(isset($_GET['news_page']) && intval($_GET['news_page'])>0 && $_GET['news_page']<=$this->TotalPages)
			$this->CurrentPage = (int)$_GET['news_page'];
		
		virtualModule::virtualModule($_ModuleConfigID);
	}
	
	function generatePage($_PageName){
		
		global $smarty, $_t;
		if(isset($_POST['DATA']))$_POST['DATA'] = xStripSlashesGPC($_POST['DATA']);
		
		$smarty->assign( "current_date", dtConvertToStandartForm( get_current_time() ) );
		//echo $_PageName;
		//exit();
		switch($_PageName){
			
			case 'frontend news short list':
				$smarty->assign('news_array', xHtmlSpecialChars($this->getNews(null, 1, $this->NewsInShortList), array(), 'title') );
				$smarty->assign( 'NewsShortListTpl', 'news.frontend.shortlist.tpl.html' );
				break;
				
			case 'frontend news list':
				
				$xRequestURI = set_query('&msg=');
				$smarty->assign('xRequestURI', $xRequestURI);
				
				$lister = getListerRange($this->CurrentPage, $this->TotalPages);
				$smarty->assign('ListerRange', range($lister['start'], $lister['end']));
				$smarty->assign('CurrentPage', $this->CurrentPage);
				$smarty->assign('TotalPages', $this->TotalPages);
				$smarty->assign('LastPage', $this->TotalPages);
				$smarty->assign('news_posts', xHtmlSpecialChars($this->getNews(), array(), 'title') );
				
				$smarty->assign( 'main_content_template', 'news.frontend.list.tpl.html' );
				break;
				
			case 'frontend new':
				if(isset($_GET['idn']))$idn = xStripSlashesGPC($_GET['idn']);
				//echo '<br>idn='.$idn.'<br>';
				$xRequestURI = set_query('&msg=');
				$smarty->assign('xRequestURI', $xRequestURI);
				
				
				$smarty->assign('news_posts', $this->getNews($idn) );
				/*echo '<pre>';
				print_r($this->getNews($idn));
				echo '</pre>';*/
				//function getNews($_ID = null, $CurrentPage = null, $NewsPerPage = null)
				$smarty->assign( 'main_content_template', 'news.frontend.one.tpl.html' );
				break;
			/**
			 * Should be called only from backoffice
			 */
			case 'admin news list':
				
				$rMsg = array();
			
				$usePOST = $this->ActionsHandler($rMsg);
				
				$msg = isset($_GET['msg'])?$_GET['msg']:'';
				switch ($msg){
					case 'delete_ok':
						$rMsg = array(
							'type' => 'ok',
							'text' => MOD_NEWS_TXT_DELETE_OK,
						);
						break;
					case 'edit_ok':
						$rMsg = array(
							'type' => 'ok',
							'text' => MOD_NEWS_TXT_EDIT_OK,
						);
						break;
					case 'add_ok':
						$rMsg = array(
							'type' => 'ok',
							'text' => MOD_NEWS_TXT_ADD_OK,
						);
						break;
					case 'pctdelete_ok':
						$rMsg = array(
							'type' => 'ok',
							'text' => MOD_NEWS_TXT_PCTDELETE_OK,
						);
						break;
					default:
						$msg = '';
				}
				
				$xRequestURI = set_query('&msg=&safemode=');
				$smarty->assign('xRequestURI', $xRequestURI);
				$smarty->assign('Message', $rMsg);
				
				if(isset($_GET['news_number'])){
					
					$News = $this->getNews($_GET['news_number']);
				}
				if(!isset($News)){
					//exit();
					$lister = getListerRange($this->CurrentPage, $this->TotalPages);
					$smarty->assign('ListerRange', range($lister['start'], $lister['end']));
					$smarty->assign('CurrentPage', $this->CurrentPage);
					$smarty->assign('TotalPages', $this->TotalPages);
					$smarty->assign('LastPage', $this->TotalPages);
					$smarty->assign( "news_posts", $this->getNews());
					//$smarty->assign( "news_posts", xHtmlSpecialChars($this->getNews(), array(), 'title') );
					if($usePOST)$smarty->hassign('NewsInfo', $_POST['DATA']);
				}else {
					
					$smarty->assign('NewsEdit',1);
					$_t = $usePOST?$_POST['DATA']:$News;
					//TransformDataBaseStringToText
					//$_t = xHtmlSpecialChars($usePOST?$_POST['DATA']:$News);
					$_t['textToMail'] = nl2br($_t['textToMail']);
					$smarty->assign('NewsInfo', $_t);
					/*echo '<pre>';
					print_r($_t);
					echo '</pre>';*/
				}
				$smarty->assign( "admin_sub_dpt", 'news.admin_list.tpl.html' );
				break;
		}
	}

	/**
	 * Handler for actions. Work with external data without checking admin mode. 
	 * Should be called only from backoffice
	 */
	function ActionsHandler(&$_rMsg){
		
		$ACTION = isset($_POST['fACTION'])?$_POST['fACTION']:'';
		
		if(isset($_GET['dlt_picture'])){
			
			$ACTION = 'DELETE_PICTURE_NEWS';
		}
		if (isset($_GET["delete"])){
			
			$ACTION = 'DELETE_NEWS';
		}
		
		if ($ACTION && CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
		{
			Redirect(set_query("&safemode=yes&delete=&dlt_picture="));
		}

		switch ($ACTION){
			
			case 'DELETE_NEWS':
			
				$this->deleteNews($_GET["delete"]);
				Redirect(set_query('&delete=&msg=delete_ok'));
				break;
			case 'DELETE_PICTURE_NEWS':
			
				$News = $this->getNews($_GET['news_number']);
				if(!isset($News))break;
				@unlink("./products_pictures/".$News['picture']);
				$this->saveNews(array('NID'=>$News['NID'], 'picture'=>''));
				Redirect(set_query('dlt_picture=&msg=pctdelete_ok'));
				break;
			case 'SAVE_NEWS':
				
				if(isset($_POST['DATA'])){
					
					$picture = "";
					if ( $_FILES["picture"]["size"]!=0 && preg_match('/\.(jpg|jpeg|gif|jpe|pcx|bmp)$/i', $_FILES["picture"]["name"]))
					{
						$r = move_uploaded_file( $_FILES["picture"]["tmp_name"], 	"./products_pictures/".$_FILES["picture"]["name"] );
						if ( $r ) 
						{
							$picture = $_FILES["picture"]["name"];
							SetRightsToUploadedFile( "./products_pictures/".$picture );
						}
					}
		
					if ( !file_exists("./products_pictures/".$picture) )
						$picture = "";
						
					$_POST['DATA']['picture'] = $picture?$picture:'';
					if(!$_POST['DATA']['picture'])unset($_POST['DATA']['picture']);
					
					if(!$_POST['DATA']['title'] && !$_POST['DATA']['textToPublication']){
						
						$_POST['DATA']['picture'] = '';
						$_rMsg = array(
							'type' => 'error',
							'text' => MOD_NEWS_EMPTY_TITLETEXT,
						);
						return true;
					}
					$this->saveNews($_POST['DATA']);
				}
				Redirect(set_query('&msg=edit_ok'));
				break;
			case 'ADD_NEWS':
		
					
				if(!$_POST['DATA']['title'] && !$_POST['DATA']['textToPublication']){
					
					$_rMsg = array(
						'type' => 'error',
						'text' => MOD_NEWS_EMPTY_TITLETEXT,
					);
					return true;
				}
				if(isset($_POST['DATA']['emailed'])&&!$_POST['DATA']['textToMail']){
						
					$_rMsg = array(
						'type' => 'error',
						'text' => MOD_NEWS_EMPTY_TEXTTOEMAIL,
					);
					return true;
				}
				$picture = "";
				if ( $_FILES["picture"]["size"]!=0  && preg_match('/\.(jpg|jpeg|gif|bmp)$/', $_FILES["picture"]["name"]))
				{
					$r = move_uploaded_file( $_FILES["picture"]["tmp_name"], 
						"./products_pictures/".$_FILES["picture"]["name"] );
					if ( $r ) 
					{
						$picture = $_FILES["picture"]["name"];
						SetRightsToUploadedFile( "./products_pictures/".$picture );
					}
				}
	
				if ( !file_exists("./products_pictures/".$picture) )
					$picture = "";
		
				$_POST['DATA']['picture'] = $picture;
				
				$stamp = microtime();
				$stamp = explode(" ", $stamp);
				$_POST['DATA']['add_stamp'] = $stamp[1];
				
				if ( !isset($_POST['DATA']['emailed']) )$_POST['DATA']['textToMail']='';

				
				$NID = $this->addNews( $_POST['DATA'] );
				
				if ( isset($_POST['DATA']['emailed']) )
					$this->sendNews($NID);
		
				Redirect(set_query('&msg=add_ok'));
		}
		return false;
	}
	
	function getNewsNumber(){
		
		$sql = '
			SELECT COUNT(*) FROM '.$this->DB_TABLE.'
		';

		@list($Number) = db_fetch_row(db_query($sql));
		return $Number;
	}
	
	function getNews($_ID = null, $CurrentPage = null, $NewsPerPage = null){
		
		if(!isset($_ID))
		{
			
			$News = array();
			$NewsPerPage = isset($NewsPerPage)?$NewsPerPage:$this->NewsPerPage;
			$CurrentPage = isset($CurrentPage)?$CurrentPage:$this->CurrentPage;
			
			$sql = '
				SELECT * FROM '.$this->DB_TABLE.'
				ORDER BY priority DESC, add_stamp DESC
				LIMIT '.(($CurrentPage-1)*$NewsPerPage).', '.$NewsPerPage.'
			';
			$Result = db_query($sql);
			while($_Row = db_fetch_row($Result)){
				
				$_Row['picture_exists'] = $_Row['picture']?file_exists("./products_pictures/".$_Row['picture']):false;
				$News[] = $_Row;
			}
		}else {
			
			$sql = '
				SELECT * FROM '.$this->DB_TABLE.' WHERE NID="'.xEscapeSQLstring($_ID).'"
			';
			$News = db_fetch_row(db_query($sql));
			if(!(isset($News['NID']) && (int)$News['NID'])){
				$News = null;
			}
			$News['picture_exists'] = $News['picture']?file_exists("./products_pictures/".$News['picture']):false;
		}
		/*echo '<pre>';
		print_r($News);
		echo '</pre>';*/
		//$News
		return $News;
	}
	
	function saveNews($_Info){
		
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
	
	function addNews($_Info){
		
		if(!count($_Info))return false;
		
		$sql = '
			INSERT INTO '.$this->DB_TABLE.'
			(`'.implode('`, `', xEscapeSQLstring(array_keys($_Info))).'`)
			VALUES("'.implode('", "', xEscapeSQLstring($_Info)).'")
		';
		db_query($sql);
		
		return db_insert_id();
	}

	function sendNews($_ID){
		
		$sql = '
			SELECT textToMail FROM '.$this->DB_TABLE.'
			WHERE NID="'.xEscapeSQLstring($_ID).'"
		';
		$News = db_fetch_row(db_query($sql));
	
		$callBackParam = '';
		$count_row = '';
		$Subscribers = subscrGetAllSubscriber( $callBackParam, $count_row);
		
		foreach ($Subscribers as $subscriber){
			
			if (eregi("^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$", $subscriber["Email"])){
				
				ss_mail($subscriber["Email"], EMAIL_NEWS_OF." ".
					CONF_SHOP_NAME, $News["textToMail"]."\n\n".
					EMAIL_SINCERELY.", ".
					CONF_SHOP_NAME."\n".
					CONF_SHOP_URL, "From: \"".
					CONF_SHOP_NAME."\"<".
					CONF_GENERAL_EMAIL.">\n".stripslashes(EMAIL_MESSAGE_PARAMETERS).
					"\nReturn-path: <".CONF_GENERAL_EMAIL.">" );
			}
		}
	}
	
	function deleteNews($_ID){
		
		$sql = '
			DELETE FROM '.$this->DB_TABLE.'
			WHERE NID="'.xEscapeSQLstring($_ID).'"
		';
		db_query($sql);
		return true;
	}
}
?>