<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	// show aux page

	if ( isset($show_aux_page) )
	{
		$aux_page = auxpgGetAuxPage( $show_aux_page );

		if ( $aux_page )
		{
			if ( $aux_page["aux_page_text_type"] != 1 )
	 			$aux_page["aux_page_text"] = 
					nl2br(  str_replace("<","&lt;",$aux_page["aux_page_text"]) );
			$smarty->assign("page_body", $aux_page["aux_page_text"] );
			$smarty->assign("show_aux_page", $show_aux_page );
			$smarty->assign("main_content_template", "show_aux_page.tpl.html" );
		}
		else
		{
			$smarty->assign("main_content_template", "page_not_found.tpl.html" );
		}

	}

?>