<?php
// *****************************************************************************
// Purpose	gets pictures by product
// Inputs   $productID - product ID
// Remarks	
// Returns	array of item
//				each item consits of
//				"photoID"			- photo ID
//				"productID"			- product ID
//				"filename"			- conventional photo filename
//				"thumbnail"			- thumbnail photo filename
//				"enlarged"			- enlarged photo filename
//				"default_picture"	- 1 if default picture, otherwise 0
function GetPictures( $productID )
{
	$sql = "select photoID, productID, filename, thumbnail, enlarged from "
		.PRODUCT_PICTURES." where productID = ".$productID;
	$q=db_query( $sql );
	$q2 = db_query("select default_picture from ".PRODUCTS_TABLE.
					" where productID = ".$productID );
	$product = db_fetch_row($q2);
	$default_picture = $product[0];
	$res = array();
	while( $row = db_fetch_row($q) )
	{
		if ( (string)$row["photoID"] == (string)$default_picture )
				$row["default_picture"] = 1;
		else 
			$row["default_picture"] = 0;
		$res[] = $row;
	}
	return $res;
}

function GetPicturesTOPSALE(  )
{
	$sql = "select * FROM ".PRODUCTS_TOPSALE."";
	$q=db_query( $sql );	
	
	while( $row = db_fetch_row($q) )
	{
		
		$res[] = $row;
	}
	return $res;
}


// *****************************************************************************
// Purpose	deletes three pictures (filename, thumbnail, enlarged) for product
// Inputs   $photoID - picture ID ( PRODUCT_PICTURES table )
// Remarks	$photoID identifier is corresponded three pictures ( see PRODUCT_PICTURES 
//				table in database_structure.xml )
// Returns	nothing
function DeleteThreePictures( $photoID )
{
	$q=db_query("select filename, thumbnail, enlarged, productID from ".
			PRODUCT_PICTURES." where photoID=".$photoID );
	if ( $picture=db_fetch_row($q) )
	{
		if ( $picture["filename"]!="" && $picture["filename"]!=null )
			if ( file_exists("./products_pictures/".$picture["filename"]) )
				unlink("./products_pictures/".$picture["filename"]);

		if ( $picture["thumbnail"]!="" && $picture["thumbnail"]!=null )
			if ( file_exists("./products_pictures/".$picture["thumbnail"]) )
				unlink("./products_pictures/".$picture["thumbnail"]);

		if ( $picture["enlarged"]!="" && $picture["enlarged"]!=null )
			if ( file_exists("./products_pictures/".$picture["enlarged"]) )
				unlink("./products_pictures/".$picture["enlarged"]);

		$q1 = db_query("select default_picture from ".PRODUCTS_TABLE.
			" where productID=".$picture["productID"]);
		if ( $product = db_fetch_row($q1) )
		{
			if ( $product["default_picture"] == $photoID )
				db_query("update ".PRODUCTS_TABLE." set default_picture=NULL ".
					" where productID=".$_GET["productID"] );
		}
		db_query("delete from ".PRODUCT_PICTURES." where photoID=".$photoID );
	}
}



// *****************************************************************************
// Purpose	deletes main picture for product
// Inputs   $photoID - picture ID ( see PRODUCT_PICTURES table )
// Remarks	$photoID identifier is corresponded three pictures ( see PRODUCT_PICTURES 
//				table in database_structure.xml ), but this function delelete only thumbnail 
//					picture from server and set thumbnail column value to ''
// Returns	nothing
function DeleteFilenamePicture( $photoID )
{
	$q=db_query("select filename from ".PRODUCT_PICTURES." where photoID=".
				$photoID );
	if ( $filename = db_fetch_row($q) )
	{
		if ( file_exists("./products_pictures/".$filename["filename"]) )
				unlink("./products_pictures/".$filename["filename"]);
		db_query("update ".PRODUCT_PICTURES." set filename=''".
				" where photoID=".$photoID );
	}
}


// *****************************************************************************
// Purpose	deletes thumbnail picture for product
// Inputs   $photoID - picture ID ( see PRODUCT_PICTURES table )
// Remarks	$photoID identifier is corresponded three pictures ( see PRODUCT_PICTURES 
//				table in database_structure.xml ), but this function delelete only thumbnail 
//					picture from server and set thumbnail column value to ''
// Returns	nothing
function DeleteThumbnailPicture( $photoID )
{
	$q=db_query("select thumbnail from ".PRODUCT_PICTURES." where photoID=".
				$photoID );
	if ( $thumbnail=db_fetch_row($q) )
	{
		if ( file_exists("./products_pictures/".$thumbnail["thumbnail"]) )
				unlink("./products_pictures/".$thumbnail["thumbnail"]);
		db_query("update ".PRODUCT_PICTURES." set thumbnail=''".
				" where photoID=".$photoID );
	}
}


// *****************************************************************************
// Purpose	deletes enlarged picture for product
// Inputs   $photoID - picture ID ( see PRODUCT_PICTURES table )
// Remarks	$photoID identifier is corresponded three pictures ( see PRODUCT_PICTURES 
//				table in database_structure.xml ), but this function delelete only enlarged
//					picture from server and set thumbnail column value to ''
// Returns	nothing
function DeleteEnlargedPicture( $photoID )
{
	$q=db_query("select enlarged from ".PRODUCT_PICTURES." where photoID=".
				$photoID );
	if ( $enlarged=db_fetch_row($q) )
	{
		if ( file_exists("./products_pictures/".$enlarged["enlarged"]) )
				unlink("./products_pictures/".$enlarged["enlarged"]);
		db_query("update ".PRODUCT_PICTURES." set enlarged=''".
				" where photoID=".$photoID["enlarged"]);
	}
}


// *****************************************************************************
// Purpose	updates filenames
// Inputs   $fileNames array of	items
//				each item consits of			
//					"filename"		- normal picture
//					"thumbnail"		- thumbnail picture
//					"enlarged"		- enlarged picture
//				key is picture ID ( see PRODUCT_PICTURES  )
// Remarks	
//				if $default_picture == -1 then default picture is not set
// Returns	nothing
function UpdatePictures( $productID, $fileNames, $default_picture )
{
	foreach( $fileNames as $key => $value )
	{
		db_query("update ".PRODUCT_PICTURES." set ".
			"	filename='".$value["filename"]."',  ".
			"	thumbnail='".$value["thumbnail"]."' , ".
			"	enlarged='".$value["enlarged"]."' ". 
			"where photoID=".$key );
	}
	if ( $default_picture != -1 )
		db_query("update ".PRODUCTS_TABLE." set default_picture = ".
			$default_picture." where productID='".$productID."'");
}



// *****************************************************************************
// Purpose	adds new picture
// Inputs	$filename, $thumbnail, $enlarged - keys of item in $_FILES 
//				corresponded to these file names
//			$productID - product ID
//			$default_picture - default picture ID
// Remarks	
//			if $new_filename == "" then function does not something
//			if $default_picture == -1 then default picture is set to new inserted 
//					item to PRODUCT_PICTURES
// Returns	nothing
function AddNewPictures( $productID, 
						 $filename, $thumbnail, $enlarged, 
						 $default_picture )
{
	if ( trim($_FILES[$filename]["name"]) != "" )
	{
		$new_filename="";
		$new_thumbnail="";
		$new_enlarged="";
		
		$timestamp = rand(1,5);
		$_SESSION['timestamp'] = $timestamp;
		//$z_img_orig = $timestamp.'_'.save_img_z($i);
		
/*echo '<pre>';
print_r($_FILES);
echo '</pre>';*/
		$r = false;
		$filenam= "./products_pictures/".$timestamp.'_750_'.$_FILES[$filename]["name"];
//echo $filenam;
		if ( $_FILES[$filename]["size"]!=0 && preg_match('/\.(jpg|jpeg|gif|jpe|pcx|bmp)$/i', $_FILES[$filename]["name"]) )
				$r = move_uploaded_file($_FILES[$filename]["tmp_name"], $filenam);
		if ($r)
		{ 
			$new_filename = $timestamp.'_750_'.$_FILES[$filename]["name"];
			SetRightsToUploadedFile( $filenam );
		}

		$r = false;
		$thumb = "./products_pictures/".$timestamp.'_100_'.$_FILES[$thumbnail]["name"];
		if ( $_FILES[$thumbnail]["size"]!=0  && preg_match('/\.(jpg|jpeg|gif|jpe|pcx|bmp)$/i', $_FILES[$thumbnail]["name"]))
			$r = move_uploaded_file($_FILES[$thumbnail]["tmp_name"], $thumb);
		if ($r) 
		{
			$new_thumbnail=$timestamp.'_100_'.$_FILES[$thumbnail]["name"];
			SetRightsToUploadedFile( $thumb );
		}

		$r = false;
		$enlarg = "./products_pictures/".$timestamp.'_'.$_FILES[$enlarged]["name"];
		if ( $_FILES[$enlarged]["size"]!=0  && preg_match('/\.(jpg|jpeg|gif|jpe|pcx|bmp)$/i', $_FILES[$enlarged]["name"]))
			$r = move_uploaded_file($_FILES[$enlarged]["tmp_name"], $enlarg);
		if ($r)
		{ 
			$new_enlarged=$timestamp.'_'.$_FILES[$enlarged]["name"];
			SetRightsToUploadedFile( $enlarg );
		}
//echo '<br>http://www.prokat.ho.com.ua/products_pictures/new_filename = '.'http://www.prokat.ho.com.ua/products_pictures/'.$new_filename.'';
		$rimg1=new RESIZEIMAGE('./products_pictures/'.$new_filename.'');
							//echo $rimg->error();
				$rimg1->resize_limitwh(750,750, 1);
				$rimg1->close();
		$rimg1=new RESIZEIMAGE('./products_pictures/'.$new_thumbnail.'');
							//echo $rimg->error();
				$rimg1->resize_limitwh(100,100, 1);
				$rimg1->close();
		
							
		
		
		if ( $new_filename!="" )
		{
			db_query("insert into ".PRODUCT_PICTURES.
					 "(productID, filename, thumbnail, enlarged)".
					 "		values( ".
						$productID.", ".
						" '".$new_filename."', ".
						" '".$new_thumbnail."', ".
						" '".$new_enlarged."' ) " );
			if ( $default_picture == -1 )
			{
				$default_pictureID = db_insert_id();
				db_query("update ".PRODUCTS_TABLE." set default_picture = ".
					$default_pictureID." where productID='".$productID."'");
			}
		}
	}
}


function AddNewPicturesTOPSALE( $num_topsale, $new_filename)
{
	
	
	if ( trim($_FILES[$new_filename]["name"]) != "" )
	{
		$r = false;
		$enlarg = "./images/topsale/".$_FILES[$new_filename]["name"];
		if ( $_FILES[$new_filename]["size"]!=0  && preg_match('/\.(jpg|jpeg|gif|jpe|pcx|png|bmp)$/i', $_FILES[$new_filename]["name"]))
			$r = move_uploaded_file($_FILES[$new_filename]["tmp_name"], $enlarg);
		if ($r)
		{ 
			//$new_enlarged=$timestamp.''.$_FILES[$enlarged]["name"];
		//	SetRightsToUploadedFile( $enlarg );
			
		}
	}						
		
		
	if ( $new_filename!="" )
		{
			//echo $num_topsale;	
			//echo "<br> insert into ".PRODUCTS_TOPSALE." ( num_topsale, filename)	values(".$num_topsale.", '".$_FILES[$new_filename]["name"]."') ";
		//	exit();
			db_query("insert into ".PRODUCTS_TOPSALE." ( num_topsale, filename)	values(".$num_topsale.", '".$_FILES[$new_filename]["name"]."') " );
			/*if ( $default_picture == -1 )
			{
				$default_pictureID = db_insert_id();
				db_query("update ".PRODUCTS_TABLE." set default_picture = ".
					$default_pictureID." where productID='".$productID."'");
			}*/
		}
	
}
function AddNewPicturesBrends( $idBrend, $new_filename)
{
	
	
	if ( trim($_FILES[$new_filename]["name"]) != "" )
	{
		$r = false;
		$enlarg = "./images/brends/".$_FILES[$new_filename]["name"];
		if ( $_FILES[$new_filename]["size"]!=0  && preg_match('/\.(jpg|jpeg|gif|jpe|pcx|png|bmp)$/i', $_FILES[$new_filename]["name"]))
			$r = move_uploaded_file($_FILES[$new_filename]["tmp_name"], $enlarg);
		if ($r)
		{ 
			//$new_enlarged=$timestamp.''.$_FILES[$enlarged]["name"];
		//	SetRightsToUploadedFile( $enlarg );
			
		}
	}						
		
		
	if ( $new_filename!="" )
		{
			//echo $num_topsale;	
			//echo "<br> insert into ".PRODUCTS_TOPSALE." ( num_topsale, filename)	values(".$num_topsale.", '".$_FILES[$new_filename]["name"]."') ";
		//	exit();
			db_query("UPDATE ".PRODUCTS_BRENDS." SET FILENAME = '".$_FILES[$new_filename]["name"]."' WHERE id = '".$idBrend."'");
		//	echo $new_filename." UPDATE ".PRODUCTS_BRENDS." SET FILENAME = '".$_FILES[$new_filename]["name"]."' WHERE id = '".$idBrend."'";
			
			/*if ( $default_picture == -1 )
			{
				$default_pictureID = db_insert_id();
				db_query("update ".PRODUCTS_TABLE." set default_picture = ".
					$default_pictureID." where productID='".$productID."'");
			}*/
		}
	
}

function DeletePicturesTOPSALE( $top_saleID )
{
	//echo $top_saleID;
	//echo "<br>select filename from ".PRODUCTS_TOPSALE." where top_saleID=".$top_saleID."";
	$q=db_query("select filename from ".PRODUCTS_TOPSALE." where top_saleID=".$top_saleID."" );
	if ( $filename = db_fetch_row($q) )
	{
		//echo '1';
		if ( file_exists("./images/topsale/".$filename["filename"]) )
				unlink("./images/topsale/".$filename["filename"]);
		db_query("DELETE FROM ".PRODUCTS_TOPSALE." WHERE top_saleID=".$top_saleID."" );
		//echo "DELETE FROM ".PRODUCTS_TOPSALE." WHERE top_saleID=".$top_saleID."";
		//exit();		
				//DELETE FROM `mandrivnu1_zoo`.`SS_product_top_sale` WHERE `SS_product_top_sale`.`top_saleID` = 7
	}
}
function DeletePicturesBrend( $brendID )
{
	//echo $top_saleID;
	//echo "<br>select filename from ".PRODUCTS_TOPSALE." where top_saleID=".$top_saleID."";
	$q=db_query("select filename from ".PRODUCTS_BRENDS." where id=".$brendID."" );
	if ( $filename = db_fetch_row($q) )
	{
		//echo '1';
		if ( file_exists("./images/brends/".$filename["filename"]) )
				@unlink("./images/brends/".$filename["filename"]);
		db_query("UPDATE ".PRODUCTS_BRENDS." SET filename='' WHERE id=".$brendID." ");
		//echo "UPDATE ".PRODUCTS_BRENDS." SET filename='' WHERE id=".$brendID."<br>"; 
		//exit();		
				//DELETE FROM `mandrivnu1_zoo`.`SS_product_top_sale` WHERE `SS_product_top_sale`.`top_saleID` = 7
	}
}
// *****************************************************************************
// Purpose	gets thumbnail file name
// Inputs	$productID - product ID
// Remarks	
// Returns	file name, it is not full path 
function GetThumbnail($productID)
{
	$q=db_query( "select default_picture from ".PRODUCTS_TABLE.
			" where productID=".$productID );
	if ( $product = db_fetch_row($q) )
	{
		$q2 = db_query("select filename, thumbnail, enlarged from ".PRODUCT_PICTURES.
			" where photoID='".$product["default_picture"]."' and productID=".$productID);
		if ( $picture=db_fetch_row($q2) )
		{
			if ( file_exists("./products_pictures/".$picture["thumbnail"]) && strlen($picture["thumbnail"])>0 )
				return $picture["thumbnail"];
			else if ( file_exists("./products_pictures/".$picture["filename"]) && strlen($picture["filename"])>0 )
				return $picture["filename"];
		}
		else //default picture is not defined - get one of the pics if there are any
		{

			$q2 = db_query( "select filename, thumbnail, enlarged from ".PRODUCT_PICTURES." where productID=".$productID );
			if ( $picture=db_fetch_row($q2) )
			{
				if ( file_exists("./products_pictures/".$picture["thumbnail"]) && strlen($picture["thumbnail"])>0 )
					return $picture["thumbnail"];
				if ( file_exists("./products_pictures/".$picture["filename"]) && strlen($picture["filename"])>0 )
					return $picture["filename"];
			}

		}
	}
	return "";	
}


function GetPictureCount( $productID )
{
	$count_pict=db_query("select COUNT(photoID) from ".PRODUCT_PICTURES.
				" where productID=".$productID." ".
				"AND filename!=''" );
	$count_pict_row=db_fetch_row($count_pict);
	return $count_pict_row[0];
}

function GetThumbnailCount( $productID )
{
	$count_pict=db_query("select COUNT(photoID) from ".PRODUCT_PICTURES.
				" where productID=".$productID." ".
				"AND thumbnail!=''" );
	$count_pict_row=db_fetch_row($count_pict);
	return $count_pict_row[0];
}

function GetEnlargedPictureCount( $productID )
{
	$count_pict=db_query("select COUNT(photoID) from ".PRODUCT_PICTURES.
				" where productID=".$productID." ".
				"AND enlarged!=''" );
	$count_pict_row=db_fetch_row($count_pict);
	return $count_pict_row[0];	
}


?>