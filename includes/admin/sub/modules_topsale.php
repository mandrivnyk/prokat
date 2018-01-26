<?
	//include("./core_functions/picture_functions.php");
/*echo '<pre>';
	print_r($_FILES);
echo '</pre>';*/




	if(isset($_FILES['new_filename1']) && isset($_POST['num_topsale']))
	{
		
		AddNewPicturesTOPSALE( $_POST['num_topsale'], 'new_filename1');			
	}

	if(isset($_GET['top_saleID']))
	{
		/*echo 'tut';
		echo '<pre>';
	print_r($_GET);
echo '</pre>';
		exit();*/
		DeletePicturesTOPSALE( $_GET['top_saleID'] );
	}
$imgs_topsale = GetPicturesTOPSALE();
/*echo '<pre>';
	print_r($imgs_topsale);
echo '</pre>';*/
	$smarty->assign( "imgs_topsale", $imgs_topsale );

	$smarty->assign("admin_sub_dpt", "modules_topsale.tpl.html");

?>