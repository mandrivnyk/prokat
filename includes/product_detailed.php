<?php
	if ( isset($_POST["cart_x"]) ) //add product to cart
	{
		$variants=array();
		foreach( $_POST as $key => $val )
		{
			if(strstr($key, "option_select_hidden_"))
				$variants[]=$val;
		}
		unset( $_SESSION["variants"] );
		$_SESSION["variants"] = $variants;
		header("Location: index.php?shopping_cart=yes&add2cart=".(int)$_GET['productID'] );
	}
	// product detailed information view
	if (isset($_GET["vote"]) && isset($productID)) //vote for a product
	{
		if (!isset($_SESSION["vote_completed"][ $productID ]) && isset($_GET["mark"]) && strlen($_GET["mark"])>0)
		{
			$mark = (float) $_GET["mark"];
			if ($mark>0 && $mark<=5)
			{
				db_query("UPDATE ".PRODUCTS_TABLE." SET customers_rating=(customers_rating*customer_votes+'".$mark."')/(customer_votes+1), customer_votes=customer_votes+1 WHERE productID='".$productID."'") or die (db_error());
			}
		}
		$_SESSION["vote_completed"][ $productID ] = 1;
	}
	if (isset($_POST["request_information"])) //email inquiry to administrator
	{
		$customer_name = xStripSlashesGPC( $_POST["customer_name"] );
		$customer_email = xStripSlashesGPC( $_POST["customer_email"] ) ;
		$message_subject = xStripSlashesGPC( $_POST["message_subject"] ) ;
		$message_text = xStripSlashesGPC( $_POST["message_text"] );
		//validate input data
		if (trim($customer_email)!="" && trim($customer_name)!="" && trim($message_subject)!="" && trim($message_text)!="" && eregi("^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$", $customer_email))
		{
			$customer_name = str_replace(array('@','<',"\n"), array('[at]', '', ''), $customer_name);
			$customer_email = str_replace(array("\n",'<'), '', $customer_email);
			//send a message to store administrator
			ss_mail(CONF_GENERAL_EMAIL, $message_subject, $message_text, "From: \"".$customer_name."\"<".$customer_email.">\n".
				EMAIL_MESSAGE_PARAMETERS."\nReturn-path: <".$customer_email.">");
			header("Location: index.php?productID=".(int)$_POST["productID"]."&sent=yes");
		}
		else if (isset($_POST["request_information"])) $smarty->assign("error",1);
	}
	//show product information
	if (isset($productID) && $productID>=0 &&
		!isset($_POST["add_topic"]) && !isset($_POST["discuss"]) )
	{
		$product=GetProduct($productID);
		if (  !$product || $product["enabled"] == 0  )
		{
			header("Location: index.php");
		}
		else
		{
			if ( !isset($_GET["vote"]) )
				IncrementProductViewedTimes($productID);
			$dontshowcategory = 1;
			//$CloudTags = array();
			/*echo '<pre>';
				print_r($_SERVER);
			echo '</pre>';*/
			if($_SERVER['SCRIPT_NAME'] !== '/printable.php')
			{
				//-------------------------------Формируем облако тегов------------------------------------
				$q = db_query("SELECT productID, title_one, url_name, producer, name, enabled FROM ".PRODUCTS_TABLE." WHERE categoryID=".$categoryID." && enabled <> 0 && Price <>0 ORDER BY RAND() LIMIT 0,4") or die (db_error());
						/*while ($rowCloud = db_fetch_row($q))
						{
							$CloudTags[] = $rowCloud;
						}*/
							$CloudTags = array();
							while ($rowCloud = db_fetch_row($q))
							{
								$q1 = db_query("SELECT * FROM ".PRODUCTS_TABLE." t1 LEFT JOIN ".PRODUCT_PICTURES." t2 ON t1.productID= t2.productID WHERE t1.productID =".$rowCloud['productID']." AND t1.enabled =1"  ) or die (db_error());
								$CloudTags[] = db_fetch_row($q1);
								//echo $row['productID'].'<br>';
								/*echo '<pre>';
								print_r($row);
								echo '</pre>';*/
							}
				/*echo '<pre>';
					print_r($CloudTags);
				echo '</pre>';*/ 
                               if(count($CloudTags) >0 )
				$smarty->assign("CloudTags", $CloudTags);
				//-----------------------------------------------------------------------------------------
			}
			$smarty->assign("main_content_template", "product_detailed.tpl.html");
			$a = $product;
			$a["PriceWithUnit"] = show_price( $a["Price"] );
			$a["list_priceWithUnit"] = show_price( $a["list_price"] );
			if ( ((float)$a["shipping_freight"]) > 0 )
				$a["shipping_freightUC"] = show_price( $a["shipping_freight"] );
			if ( isset($_GET["picture_id"]) )
			{
				$picture = db_query("select filename, thumbnail, enlarged from ".
					PRODUCT_PICTURES." where photoID=".(int)$_GET["picture_id"] );
				$picture_row = db_fetch_row( $picture );
			}
			else if ( !is_null($a["default_picture"]) )
			{
				$picture = db_query("select filename, thumbnail, enlarged from ".
					PRODUCT_PICTURES." where photoID=".$a["default_picture"] );
				$picture_row = db_fetch_row( $picture );
			}
			else
			{
				$picture = db_query(
					"select filename, thumbnail, enlarged, photoID from ".PRODUCT_PICTURES.
						" where productID='".$productID."'" );
				if ( $picture_row = db_fetch_row( $picture ) )
					$a["default_picture"]=$picture_row["photoID"];
				else
					$picture_row=null;
			}
			if ( $picture_row )
			{
				$a["picture"]	= $picture_row[ 0 ];
				$a["thumbnail"] = $picture_row[ 1 ];
				$a["big_picture"]  = $picture_row[ 2 ];
			}
			else
			{
				$a["picture"]	= "";
				$a["thumbnail"] = "";
				$a["big_picture"]  = "";
			}
			if ($a) //product found
			{
				if (!isset($categoryID)) $categoryID = $a["categoryID"];
				//get selected category info
				$q = db_query("SELECT categoryID, name, description, picture FROM ".CATEGORIES_TABLE." WHERE categoryID='$categoryID'") or die (db_error());
				$row = db_fetch_row($q);
				if ($row)
				{
					if (!file_exists("./products_pictures/".$row[3])) $row[3] = "";
					$smarty->assign("selected_category", $row);
				}
				else
					$smarty->assign("selected_category", NULL);
				//calculate a path to the category
				$smarty->assign("product_category_path",  catCalculatePathToCategory( $categoryID ) );
				//reviews number
				$q = db_query("SELECT count(*) FROM ".DISCUSSIONS_TABLE." WHERE productID='$productID'") or die (db_error());
				$k = db_fetch_row($q); $k = $k[0];
				//extra parameters
				$extra = GetExtraParametrs($productID);
				/*echo '<pre>';
					print_r($extra);
				echo '</pre>';*/
				for($i=0;$i<count($extra); $i++)
				{
					if(($extra[$i]['name'] == 'Цвет')||($extra[$i]['name'] == 'Цвет внешнего тента'))
					{
						//echo $extra[$i]['option_value'];
						$colors_arr = explode(',', $extra[$i]['option_value']);
						if(count($colors_arr)>1)
								$smarty->assign("colors_arr", $colors_arr);
						/*echo '<pre>';
							print_r($colors_arr);
						echo '</pre>';*/
					}
					if(($extra[$i]['name'] == 'Размер')||($extra[$i]['name'] == 'Размер (унисекс)'))
					{
						//echo $extra[$i]['option_value'];
						$size_arr = explode(',', $extra[$i]['option_value']);
						if(count($size_arr)>1)
							$smarty->assign("size_arr", $size_arr);
						/*echo '<pre>';
							print_r($size_arr);
						echo '</pre>';*/
					}
				}
				//related items
				$related = array();
				$q = db_query("SELECT count(*) FROM ".RELATED_PRODUCTS_TABLE." WHERE Owner='$productID'") or die (db_error());
				$cnt = db_fetch_row($q);
				$smarty->assign("product_related_number", $cnt[0]);
				if ($cnt[0] > 0)
				{
					$q = db_query("SELECT productID FROM ".RELATED_PRODUCTS_TABLE." WHERE Owner='$productID'") or die (db_error());
					while ($row = db_fetch_row($q))
					{
						$p = db_query("SELECT productID, name, Price FROM ".PRODUCTS_TABLE." WHERE productID=$row[0]") or die (db_error());
						if ($r = db_fetch_row($p))
						{
						  $r[2] = show_price($r[2]);
						  $RelatedPictures = GetPictures($r['productID']);
						  foreach($RelatedPictures as $_RelatedPicture){
							if(!$_RelatedPicture['default_picture'])continue;
							if(!file_exists("./products_pictures/".$_RelatedPicture['thumbnail']))break;
							$r['pictures'] = array('default' => $_RelatedPicture);
							break;
						  }
//						  $r['thumb'] =
						  $related[] = $r;
						}
					}
				}
				//update several product fields
				if (!file_exists("./products_pictures/".$a["picture"] )) $a["picture"] = 0;
				if (!file_exists("./products_pictures/".$a["thumbnail"] )) $a["thumbnail"] = 0;
				if (!file_exists("./products_pictures/".$a["big_picture"] )) $a["big_picture"] = 0;
				else if ($a["big_picture"])
				{
					$size = getimagesize("./products_pictures/".$a["big_picture"] );
					$a[16] = $size[0]+40;
					$a[17] = $size[1]+30;
				}
				$a[12] = show_price( $a["Price"] );
				$a[13] = show_price( $a["list_price"] );
				$a[14] = show_price( $a["list_price"] - $a["Price"]); //you save (value)
				$a["PriceWithOutUnit"]=show_priceWithOutUnit( $a["Price"] );
				if ( $a["list_price"] ) $a[15] =
					ceil(((($a["list_price"]-$a["Price"])/
						$a["list_price"])*100)); //you save (%)
				if ( isset($_GET["picture_id"]) )
				{
					$pictures = db_query("select photoID, filename, thumbnail, enlarged from ".
						PRODUCT_PICTURES." where photoID!=".(int)$_GET["picture_id"].
						" AND productID=".(string)$productID );
				}
				else if ( !is_null($a["default_picture"]) )
				{
					$pictures = db_query("select photoID, filename, thumbnail, enlarged from ".
						PRODUCT_PICTURES." where photoID!=".$a["default_picture"].
						" AND productID=".(string)$productID );
				}
				else
				{
					$pictures = db_query("select photoID, filename, thumbnail, enlarged from ".
						PRODUCT_PICTURES." where productID=".(string)$productID );
				}
				$all_product_pictures = array();
				$all_product_pictures_id = array();
		$i=0;		
				while( $picture=db_fetch_row($pictures) )
				{
/*echo '<pre>';
	print_r($picture);
echo '</pre>';*/
						if ( file_exists("./products_pictures/".$picture[1]) )
							{
								$all_product_pictures[$i]['filename']=$picture[1];
								$all_product_pictures_id[$i] = $picture[0];
							}
						if( file_exists("./products_pictures/".$picture[2]) )
							{
								$all_product_pictures[$i]['thumbnail']=$picture[2];
							}
						if( file_exists("./products_pictures/".$picture[3]) )
							{
								$all_product_pictures[$i++]['enlarged']=$picture[3];
							}				
				}
				//eproduct
				if (strlen($a["eproduct_filename"]) > 0 && file_exists("products_files/".$a["eproduct_filename"]) )
				{
					$size = filesize("products_files/".$a["eproduct_filename"]);
					if ($size > 1000) $size = round ($size / 1000);
					$a["eproduct_filesize"] = $size." Kb";
				}
				else
				{
					$a["eproduct_filename"] = "";
				}
				//initialize product "request information" form in case it has not been already submitted
				if (!isset($_POST["request_information"]))
				{
					if (!isset($_SESSION["log"]))
					{
						$customer_name = "";
						$customer_email = "";
					}
					else
					{
						$custinfo = regGetCustomerInfo2( $_SESSION["log"] );
						$customer_name = $custinfo["first_name"]." ".$custinfo["last_name"];
						$customer_email = $custinfo["Email"];
					}
					$message_text = "";
				}
				$smarty->hassign("customer_name", $customer_name);
				$smarty->hassign("customer_email", $customer_email);
				$smarty->hassign("message_text", $message_text);
				if (isset($_GET["sent"]))
					$smarty->assign("sent",1);
				$smarty->assign("all_product_pictures_id", $all_product_pictures_id );
				$smarty->assign("all_product_pictures", $all_product_pictures );
/*echo '<pre>';
	print_r($a);
echo '</pre>';*/
				$smarty->assign("product_info", $a);
				$imgs_topsale = GetPicturesTOPSALE();
			$smarty->assign( "imgs_topsale", $imgs_topsale);
				$smarty->assign("title_one", $a['title_one']);
				$smarty->assign("title_two", $a['title_two']);
				$smarty->assign("name", $a['name']);
				$smarty->assign("producer", $a['producer']);
				$smarty->assign("product_reviews_count", $k);
				$smarty->assign("product_extra", $extra);
				$smarty->assign("product_related", $related);
			}
			else
			{
				//product not found
				header("Location: index.php");
			}
		}
	}
?>