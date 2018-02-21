<?php

require_once('./classes/class.sklad.php');
    // compare two configuration
    function CompareConfiguration($variants1, $variants2)
    {
        if ( count($variants1) != count($variants2))
            return false;

        foreach($variants1 as $variantID)
        {
            $count1 = 0;
            $count2 = 0;

            for($i=0; $i<count($variants1); $i++)
                if ( (int)$variants1[$i] == (int)$variantID )
                    $count1 ++;

            for($i=0; $i<count($variants1); $i++)
                if ( (int)$variants2[$i] == (int)$variantID )
                    $count2 ++;

            if ( $count1 != $count2 )
                return false;
        }
        return true;
    }

    // search configuration in session variable
    function SearchConfigurationInSessionVariable($variants, $productID)
    {
        //session_start();
        /*echo '<pre>';
            print_r($_SESSION);
        echo '</pre>';*/
        foreach( $_SESSION["configurations"] as $key => $value )
        {
            /*echo '<pre>';
            print_r($_SESSION);
        echo '</pre>'; */
            if ( (int)$_SESSION["gids"][$key] != (int)$productID )
                continue;
            if ( CompareConfiguration($variants, $value) )
                return $key;
        }
        return -1;
    }

    // search configuration in database
    function SearchConfigurationInDataBase($variants, $productID)
    {
        $q=db_query( "select itemID from ".SHOPPING_CARTS_TABLE.
            " where customerID='".regGetIdByLogin($_SESSION["log"])."'" );
        while( $r = db_fetch_row($q) )
        {
            $q1=db_query( "select COUNT(itemID) from ".SHOPPING_CART_ITEMS_TABLE.
                " where productID='".$productID."' AND itemID='".$r["itemID"]."'" );
            $r1=db_fetch_row($q1);
            if ( $r1[0] != 0 )
            {
                $variants_from_db=GetConfigurationByItemId( $r["itemID"] );
                if ( CompareConfiguration($variants, $variants_from_db) )
                    return $r["itemID"];
            }
        }
        return -1;
    }


    function GetConfigurationByItemId($itemID)
    {
        $q=db_query("select variantID from ".
            SHOPPING_CART_ITEMS_CONTENT_TABLE." where itemID='".$itemID."'" );
        $variants=array();
        while( $r=db_fetch_row( $q ) )
            $variants[]=$r["variantID"];
        return $variants;
    }

    function InsertNewItem(
    $variants,
    $productID)
    {
        db_query( "insert into ".SHOPPING_CART_ITEMS_TABLE.
            "(productID) values('".$productID."')" );
        $itemID=db_insert_id();
        foreach( $variants as $var )
        {
            db_query("insert into ".
                SHOPPING_CART_ITEMS_CONTENT_TABLE."(itemID, variantID) ".
                "values( '".$itemID."', '".$var."')" );
        }
        return $itemID;
    }

    function InsertItemIntoCart($itemID)
    {
        db_query("insert ".SHOPPING_CARTS_TABLE.
            "(customerID, itemID, Quantity)".
            "values( '".regGetIdByLogin($_SESSION["log"])."', '".$itemID."', 1 )" );
    }

    function GetStrOptions($variantNumArr, $productCode)
    {
        $res = "";
        $sklad = new sklad();

        if (strpos($productCode, '/') !== false) {
            $productCodeArr = explode("/", $productCode);
        }
        else {
            $productCodeArr[] = $productCode;
        }

        foreach ($productCodeArr as $key=>$productCode){


            if(trim($variantNumArr[$key]) !== ""){
                if($key == 1){
                    $res .= "<br>";
                }
                $res .= $sklad->getVariantStringFromFile($productCode, $variantNumArr[$key]);
            }
        }

        return $res;
    }

    function CodeItemInClient($variants, $productID)
    {
        $array=array();
        $array[]=$productID;
        foreach($variants as $var)
            $array[]=$var;
        return implode("_", $array);
    }

    function DeCodeItemInClient($str)
    {
        // $variants, $productID
        $array=explode("_", $str );
        $productID=$array[0];
        $variants=array();
        for($i=1; $i<count($array); $i++)
            $variants[]=$array[$i];
        $res=array();
        $res["productID"]=$productID;
        $res["variants"]=$variants;
        return $res;
    }

    function GetProductInStockCount($productID)
    {
        $q=db_query("select in_stock from ".
            PRODUCTS_TABLE.
                " where productID='".$productID."'" );
        $is=db_fetch_row($q);
        return $is[0];
    }

    function GetPriceProductWithOption($variants, $productID)
    {
        $q=db_query("select Price from ".PRODUCTS_TABLE.
            " where productID='".$productID."'");
        $r=db_fetch_row($q);
        $base_price = (float)$r[0];
        $full_price = (float)$base_price;
        foreach($variants as $var)
        {
            $q1=db_query("select price_surplus from ".PRODUCTS_OPTIONS_SET_TABLE.
                " where productID='".$productID."' AND variantID='".$var."'");
            $r1=db_fetch_row($q1);
            $full_price += $r1["price_surplus"];
        }
        return $full_price;
    }


    function GetProductIdByItemId($itemID)
    {
        $q=db_query("select productID from ".SHOPPING_CART_ITEMS_TABLE.
            " where itemID='".$itemID."'");
        $r=db_fetch_row($q);
        return $r["productID"];
    }


    // *****************************************************************************
    // Purpose	move cart content ( SHOPPING_CARTS_TABLE ) into ordered carts ( ORDERED_CARTS_TABLE )
    // Inputs		$orderID - order ID
    //				$shippingMethodID		- shipping method ID
    //				$paymentMethodID		- payment method ID
    //				$shippingAddressID		- shipping address ID
    //				$billingAddressID		- billing address ID
    //				$shippingModuleFiles	- content modules/shipping directories
    //				$paymentModulesFiles	- content modules/payment directories
    // Remarks	this funcgtion is called by ordOrderProcessing to order comete
    // Returns	nothing
    function cartMoveContentFromShoppingCartsToOrderedCarts( $orderID,
            $shippingMethodID, $paymentMethodID,
            $shippingAddressID, $billingAddressID,
            $shippingModuleFiles, $paymentModulesFiles )
    {
        $q = db_query( "select statusID from ".ORDERS_TABLE." where orderID=$orderID" );
        $order = db_fetch_row( $q );
        $statusID = $order["statusID"];
        $variants = '';

        $sql = "
            DELETE FROM ".ORDERED_CARTS_TABLE." WHERE orderID=".intval($orderID)."
        ";
        db_query($sql);
        // select all items from SHOPPING_CARTS_TABLE
        $q_items = db_query("SELECT itemID, Quantity FROM ".
                SHOPPING_CARTS_TABLE." WHERE customerID='".
                    regGetIdByLogin($_SESSION["log"])."'");
        while($item = db_fetch_row($q_items))
        {
            $productID = GetProductIdByItemId( $item["itemID"] );
            if ( $productID == null || trim($productID) == "" )
                continue;

            // get product by ID
            $q_product = db_query("select name, product_code from ".PRODUCTS_TABLE.
                " where productID='".$productID."'");
            $product = db_fetch_row( $q_product );

            // get full product name ( complex product name - $productComplexName ) -
            // name with configurator options
            $variants = GetConfigurationByItemId( $item["itemID"] );

            $options = GetStrOptions( $variants );
            if ( $options != "" )
                $productComplexName = $product["name"]."(".$options.")";
            else
                $productComplexName = $product["name"];

            if ( strlen($product["product_code"]) > 0 )
                $productComplexName = "[".$product["product_code"]."] ".$productComplexName;

            //
            $price = GetPriceProductWithOption( $variants, $productID );
            $tax = taxCalculateTax( $productID, $shippingAddressID, $billingAddressID );
            db_query("INSERT INTO ".ORDERED_CARTS_TABLE.
                 "(	itemID, orderID, name, ".
                 "	Price, Quantity, tax ) ".
                 "  VALUES ".
                 " 	(".$item["itemID"].",".$orderID.", '".xEscapeSQLstring($productComplexName)."', ".$price.
                 ", ".$item["Quantity"].", ".$tax." )");
            if ( $statusID != ostGetCanceledStatusId() && CONF_CHECKSTOCK )
            {
                db_query( "update ".PRODUCTS_TABLE." set in_stock = in_stock - ".$item["Quantity"].
                            " where productID='".$productID."'" );
            }
        }
        db_query("DELETE FROM ".SHOPPING_CARTS_TABLE.
                " WHERE customerID='".regGetIdByLogin($_SESSION["log"])."'");
    }



    // *****************************************************************************
    // Purpose	clear cart content
    // Inputs
    // Remarks
    // Returns
    function cartClearCartContet()
    {
        //session_start();
        if ( isset($_SESSION["log"]) )
            db_query("DELETE FROM ".
                    SHOPPING_CARTS_TABLE.
                    " WHERE customerID='".regGetIdByLogin($_SESSION["log"])."'");
        else
        {

            unset($_SESSION["gids"]);
            unset($_SESSION["counts"]);
            unset($_SESSION["configurations"]);
        }
    }

    // *****************************************************************************
    // Purpose	clear cart content
    // Inputs
    // Remarks
    // Returns
    function cartGetCartContent()
    {
        $cart_content 	= array();
        $total_price 	= 0;
        $freight_cost	= 0;
        $variants 		= '';


        //unauthorized user - get cart from session vars

            //echo '2';
            //print_r($_SESSION['gids']);
        //session_start();
//        echo '<pre>';
//        print_r($_SESSION);
//    echo '</pre>';
//    print_r($_SESSION);
            $total_price 	= 0; //total cart value
            $cart_content	= array();


            //shopping cart items count
            if ( isset($_SESSION["gids"]) )
                for ($j=0; $j<count($_SESSION["gids"]); $j++)
                {
                    if ($_SESSION["gids"][$j])
                    {
                        $session_items[]=CodeItemInClient($_SESSION["configurations"][$j], $_SESSION["gids"][$j]);


                        $q = db_query("SELECT name, Price, shipping_freight, free_shipping, product_code FROM ".PRODUCTS_TABLE." WHERE productID='".$_SESSION["gids"][$j]."'");

                        if ($r = db_fetch_row($q))
                        {
                            $costUC = GetPriceProductWithOption($_SESSION["configurations"][$j], $_SESSION["gids"][$j])/* * $_SESSION["counts"][$j]*/;
                            $id = $_SESSION["gids"][$j];
                            $info_prod = '';
                            if (count($_SESSION["configurations"][$j]) > 0)
                            {
                                for ($tmp1=0;$tmp1<count($_SESSION["configurations"][$j]);$tmp1++)
                                {

                                    $id .= "_".$_SESSION["configurations"][$j][$tmp1];
                                    $info_prod .= " ".$_SESSION["configurations"][$j][$tmp1].'; ';
                                }
                            }

                            $tmp = array(
                                    "productID"	=>  $_SESSION["gids"][$j],
                                    "product_code"	=>  $r["product_code"],
                                    "id"		=>	$id, //$_SESSION["gids"][$j],
                                    "info_prod"		=>	$info_prod,
                                    "name"		=>	$r[0],
                                    "quantity"	=>	$_SESSION["counts"][$j],
                                    "free_shipping"	=>	$r["free_shipping"],
                                    "costUC"	=>	$costUC,
                                    "cost"		=>	show_price($costUC * $_SESSION["counts"][$j])
                                );
                            $strOptions= "";
                            if(isset($_SESSION["configurations"][$j][0])) {
//                                    echo '<pre>';
//                                        print_r($_SESSION);
//                                    echo '</pre>';
//                                    echo '<pre>';
//                                        print_r($_SESSION["configurations"][$j]);
//                                    echo '</pre>';
//                                    exit();
                                $strOptions = GetStrOptions( $_SESSION["configurations"][$j], $r["product_code"] );
                            }

                            if (  !empty($strOptions)){
                                $tmp["name"].="<br>(".$strOptions.")";
                            }


                            $q_product = db_query( "select min_order_amount, shipping_freight from ".PRODUCTS_TABLE.
                                    " where productID=".
                                    $_SESSION["gids"][$j] );
                            $product = db_fetch_row( $q_product );
                            if ( $product["min_order_amount"] > $_SESSION["counts"][$j] )
                                $tmp["min_order_amount"] = $product["min_order_amount"];

                            $freight_cost += $_SESSION["counts"][$j]*$product["shipping_freight"];

                            $cart_content[] = $tmp;

                            $total_price += GetPriceProductWithOption(
                                        $_SESSION["configurations"][$j],
                                        $_SESSION["gids"][$j] )*$_SESSION["counts"][$j];
                        }
                    }
                }

    /*echo '1';
    print_r($cart_content);
    exit();*/
        return array(
                "cart_content"	=> $cart_content,
                "total_price"	=> $total_price,
                "freight_cost"	=> $freight_cost );

    }


    function cartCheckMinOrderAmount()
    {
        $cart_content = cartGetCartContent();
        $cart_content = $cart_content["cart_content"];
        foreach( $cart_content as $cart_item )
            if ( isset($cart_item["min_order_amount"]) )
                return false;
        return true;
    }

    function cartCheckMinTotalOrderAmount(){

            $res = cartGetCartContent();
            $d = oaGetDiscountPercent( $res, "" );
            $order["order_amount"] = $res["total_price"] - ($res["total_price"]/100)*$d;
            if($order["order_amount"]<CONF_MINIMAL_ORDER_AMOUNT)
                return false;
            else
                return true;
    }


    function cartAddToCart( $productID, $variants)
    {

        $is=GetProductInStockCount( $productID );

//        $q = db_query( "select min_order_amount from ".PRODUCTS_TABLE.
//            " where productID=".$productID );
//        $min_order_amount = db_fetch_row( $q );
//        $min_order_amount = $min_order_amount[ 0 ];
        $min_order_amount = 1;
        $count_to_order = 1;


            //$_SESSION["gids"] contains product IDs
            //$_SESSION["counts"] contains product quantities
            //			($_SESSION["counts"][$i] corresponds to $_SESSION["gids"][$i])
            //$_SESSION["configurations"] contains variants
            //$_SESSION[gids][$i] == 0 means $i-element is 'empty'

            if (!isset($_SESSION["gids"]))
            {
                $_SESSION["gids"]		= array();
                $_SESSION["counts"]		= array();
                $_SESSION["configurations"] = array();
            }

            //check for current item in the current shopping cart content
            $item_index=SearchConfigurationInSessionVariable( $variants, $productID );

            if ( $item_index == -1 )
                    $count_to_order = $min_order_amount;

            if ( $item_index!=-1 ) //increase current product's quantity
            {
                if (CONF_CHECKSTOCK==0 || $_SESSION["counts"][$item_index]+$count_to_order <= $is)
                    $_SESSION["counts"][$item_index] += $count_to_order;
                else
                    return false;
            }
            else if (CONF_CHECKSTOCK==0 || $is >= $count_to_order) //no item - add it to $gids array
            {
                $_SESSION["gids"][] = $productID;
                $_SESSION["counts"][] = $count_to_order;
                $_SESSION["configurations"][]=$variants;
            }
            else
                return false;


        return true;
    }



    // *****************************************************************************
    // Purpose
    // Inputs	$customerID - customer ID
    // Remarks
    // Returns	returns true if cart is empty for this customer
    function cartCartIsEmpty( $log )
    {
        $customerID = regGetIdByLogin( $log );
        if ( (int)$customerID > 0 )
        {
            $customerID = (int)$customerID;
            $q_count = db_query( "select count(*) from ".SHOPPING_CARTS_TABLE." where customerID=".$customerID );
            $count = db_fetch_row( $q_count );
            $count = $count[0];
            return ( $count == 0 );
        }
        else
            return true;
    }



    ?>