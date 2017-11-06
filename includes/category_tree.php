<?php
	// category navigation form
	if ( isset($categoryID) )
		$out = catGetCategoryCompactCList( $categoryID );
	else
		$out = catGetCategoryCompactCList( 1 );
/*echo '<pre>';
		print_r($out);
	echo '</pre>';
	
	for($i=0;$i<count($out);$i++)
	{
		$out[$i]['name']
		
	}*/
	
	$smarty->assign( "categories_tree", $out );

?>