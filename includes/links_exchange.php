<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
    if ( isset($_POST['links_exchange']) || isset($_GET['links_exchange']) )
	{
		if(isset($_POST['fACTION']))
		if($_POST['fACTION'] == 'ADD_LINK'){
			
			do{
				
				if(!strlen(str_replace('http://','',$_POST['LINK']['le_lURL']))){
					
					$error = STRING_ERROR_LE_ENTER_LINK;
					break;
				}
				if(!strlen($_POST['LINK']['le_lText'])){
					
					$error = STRING_ERROR_LE_ENTER_TEXT;
					break;
				}
				if(strpos($_POST['LINK']['le_lURL'],'http://'))
					$_POST['LINK']['le_lURL'] = 'http://'.$_POST['LINK']['le_lURL'];
				if(le_addLink($_POST['LINK']))break;
				else $error = STRING_ERROR_LE_LINK_EXISTS;
			}while(0);
			
			if(!isset($error))Redirect(set_query('added=ok', $_POST['fREDIRECT']));
		}
		
		#Links number per page
		$ob_per_list = 20;
		
		if(empty($_GET['le_categoryID']))$_GET['le_categoryID'] = 0;
		else $_GET['le_categoryID'] = intval($_GET['le_categoryID']);
		
		$TotalPages = ceil(le_getLinksNumber(($_GET['le_categoryID']?"le_lCategoryID = {$_GET['le_categoryID']}":'1').' AND le_lVerified IS NOT NULL')/$ob_per_list);
		
		if(empty($_GET['page']))$_GET['page'] = 1;
		else $_GET['page'] = intval($_GET['page'])>$TotalPages?$TotalPages:intval($_GET['page']);
		
		if(isset($_GET['added'])||isset($_POST['added']))$error = STRING_ERROR_LE_LINK_ADDED;
		$_SERVER['REQUEST_URI'] = set_query('added=');
		$lister = getListerRange($_GET['page'], $TotalPages);
		$le_Categories = html_spchars(le_getCategories());
		
		if(isset($_GET['show_all'])||isset($_POST['show_all'])){
		
			$ob_per_list = $ob_per_list*$TotalPages;
			$smarty->assign('showAllLinks', '1');
			$_GET['page'] = 1;
		}
		
		$smarty->assign('REQUEST_URI', $_SERVER['REQUEST_URI']);
		$smarty->assign('url_allcategories', set_query('le_categoryID='));
		$smarty->assign('le_categories', $le_Categories);
		$smarty->assign('le_CategoryID', $_GET['le_categoryID']);
		$smarty->assign('curr_page',$_GET['page']);
		$smarty->assign('last_page', $TotalPages);
		if(isset($error)){
			
			if($error!=STRING_ERROR_LE_LINK_ADDED){
				
				$smarty->assign('error',$error);
				$smarty->assign('pst_LINK',html_spchars(xStripSlashesGPC($_POST['LINK'])));
			}
			else 
				$smarty->assign('error_ok',$error);
		}
		$smarty->assign('le_links', 
			html_spchars(le_getLinks(
				$_GET['page'], 
				$ob_per_list, 
				($_GET['le_categoryID']?"le_lCategoryID = {$_GET['le_categoryID']}":'1')." AND (le_lVerified IS NOT NULL AND le_lVerified <>'0000-00-00 00:00:00' )", 
				'le_lID, le_lText, le_lURL, le_lCategoryID, le_lVerified', 
				'le_lVerified ASC, le_lURL ASC')));
		if($lister['start']<$lister['end'])$smarty->assign('le_lister_range', range($lister['start'], $lister['end']));
		$smarty->assign('le_categories_pr', ceil(count($le_Categories)/2));
		
		$smarty->assign("main_content_template", "links_exchange.tpl.html");
	}

?>