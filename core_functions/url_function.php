<?
		function getUrl(){

			//----------Собираем URL с таблицы категорий-----------------------------------
			$sql = 'SELECT categoryID, url_name FROM '.CATEGORIES_TABLE.'';
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
		
		return $Url;
	}

	
	function getHtaccess_base()
	{
		
		$file = file_get_contents('./htaccess_base.txt');
		
		return $file;
	}
?>