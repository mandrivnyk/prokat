<?php


$brendsAll = GetProductBrends();

for($i=0;$i<count($brendsAll);$i++)
{
	if(isset($_FILES['img_'.$brendsAll[$i]['id']]) && ($_FILES['img_'.$brendsAll[$i]['id']]['name'] !== ''))
	{
			
		 AddNewPicturesBrends( $brendsAll[$i]['id'], 'img_'.$brendsAll[$i]['id']);
	}
	
}



	if(isset($_GET['brendID']))
	{
		
		DeletePicturesBrend( $_GET['brendID'] );
	}
	
	
	$brendsAll = GetProductBrends();

	$smarty->assign( "brendsAll", $brendsAll );

	$smarty->assign("admin_sub_dpt", "modules_brends.tpl.html");

?>