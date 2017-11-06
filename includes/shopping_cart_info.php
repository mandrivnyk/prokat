<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	// shopping cart brief info

	//calculate shopping cart value
	$k=0;
	$cnt = 0;
	$variants = array();
	if (isset($_SESSION["log"])) //taking products from database
	{
		$q = db_query("SELECT itemID, Quantity FROM ".SHOPPING_CARTS_TABLE.
				" WHERE customerID='".regGetIdByLogin($_SESSION["log"])."'") or die (db_error());
		while ($row = db_fetch_row($q))
		{
			$q1=db_query("select productID from ".SHOPPING_CART_ITEMS_TABLE.
				" where itemID='".$row["itemID"]."'");
			$r1=db_fetch_row($q1);

			$variants=GetConfigurationByItemId( $row["itemID"] );
			$k += GetPriceProductWithOption($variants, $r1["productID"])*$row["Quantity"];
			$cnt+=$row["Quantity"];
		}
	}
	else
	if (isset($_SESSION["gids"])) //...session vars
	{
		for ($i=0; $i<count($_SESSION["gids"]); $i++)
		{
			if ($_SESSION["gids"][$i])
			{
				$t = db_query("SELECT Price FROM ".PRODUCTS_TABLE." WHERE productID='".$_SESSION["gids"][$i]."'") or die (db_error());
				$rr = db_fetch_row($t);

				$sum=$rr["Price"];

				// $rr["Price"]
				foreach( $_SESSION["configurations"][$i] as $var )
				{
					$q1=db_query("select price_surplus from ".
						PRODUCTS_OPTIONS_SET_TABLE.
						" where variantID='".$var."' AND productID='".$_SESSION["gids"][$i]."'");
					$r1=db_fetch_row($q1);
					$sum+=$r1["price_surplus"];
				}

				$k += $_SESSION["counts"][$i]*$sum;
				$cnt += $_SESSION["counts"][$i];
			}
		}
	}
	
	if ( isset($_SESSION["log"]) )
		$log = $_SESSION["log"];
	else
		$log = null;

	$d = oaGetDiscountPercent( cartGetCartContent(), $log );
	$k = $k*((100-$d)/100);
	
	$smarty->assign("shopping_cart_value", $k);
	$smarty->assign("shopping_cart_value_shown", show_price($k));
	$smarty->assign("shopping_cart_items", $cnt);

?>