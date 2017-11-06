<?
	//include("./core_functions/picture_functions.php");

if(isset($_POST['categoryID']))
{
	
	$smarty->assign( "categoryID", $_POST['categoryID'] );
	$smarty->assign( "title_one", $_POST['title_one'] );
	$smarty->assign( "title_two", $_POST['title_two'] );
	$smarty->assign( "url_to_pars_from", $_POST['url_to_pars_from'] );
	$smarty->assign( "producer", $_POST['producer'] );
	$smarty->assign( "meta_description", $_POST['meta_description'] );
	$smarty->assign( "meta_keywords", $_POST['meta_keywords'] );
}






include_once('js/simplehtmldom_1_5/simple_html_dom.php');
$html = file_get_html('http://www.reusch.com/winter/products/board-park/reusch-booter-r-tex-xt.html');
echo $html;
exit(); 
$divNum = 'div[id=pd_top]';
	 if($html->find($divNum))//'table.hello td'
					{
						$res = $html->find($divNum,0)->find('h3');
						//$res = $html->find('div.scroll-box a');
						
						foreach($res as $element)
						{
							//echo $element->href.'<br>';
							$res_clear_arr[] = $element; 
						}
						echo '<pre>';
							print_r($res_clear_arr);
						echo '</pre>';
					}
									
	
	
	
	
	
	$smarty->assign("admin_sub_dpt", "modules_pars.tpl.html");

?>