<?php

class Cart
{
    private $smarty;
    private $smarty_mail;
    
    public  function __construct($smarty, $smarty_mail){
        $this->smarty = $smarty;
        $this->smarty_mail = $smarty_mail;
    }

    
    public function shoppingCart(){
        // shopping cart

        //session_start();

        if ((int)CONF_SMARTY_FORCE_COMPILE) //this forces Smarty to recompile templates each time someone runs cart.php
        {
            $this->smarty->force_compile = true;
            $this->smarty_mail->force_compile = true;
        }

        if (isset($_GET["shopping_cart"]) || isset($_POST["shopping_cart"]))
        {
            $cart_php_file = "index.php";
            $this->smarty->assign("cart_php_file", $cart_php_file);


//            if ( isset($_GET["make_more_exact_cart_content"]) )
//                $this->smarty->assign( "make_more_exact_cart_content", 1 );

            //add product to cart with productID=$add
            if ( (isset($_POST["addproduct"]) && $_POST["addproduct"]>0) || (isset($_GET["addproduct"]) && $_GET["addproduct"]>0))
            {

                $productID = (isset($_POST["addproduct"])?$_POST["addproduct"]:$_GET["addproduct"]);
//                if (isset($_SESSION["variants"]))
//                {
//                    $variants=$_SESSION["variants"];
//                    unset($_SESSION["variants"]);
//                    //session_unregister("variants"); //calling session_unregister() is required since unset() may not work on some systems
//                }
//                else
//                {
//                    $variants = array();
//                }
                //should we add products to cart?
//                if ( isset($_POST["addproduct"]) )
//                {
                    $variants=array();
                    //            foreach( $_POST as $key => $val )
                    //            {
                    //                if(strstr($key, "option_select_hidden_"))
                    //                    $variants[]=$val;
                    //            }



                    //            print_r($_GET);
                    //            print_r($variants);
                    if(isset($_POST["select_variants_0"]))
                    {
                        $select_variants_0 =  @iconv("UTF-8","windows-1251",$_POST['select_variants_0']);
                        $variants_length = count($variants);
                        $variants[$variants_length] = str_replace(" ","+", ''.$select_variants_0);
                    }

                    if(isset($_POST["select_variants_1"]) && !empty($_POST["select_variants_1"]))
                    {


                        $variants_length = count($variants);
                        $variants[$variants_length] = $_POST['select_variants_1'];

                    }

                    if(isset($_POST["size_sel"]))
                    {
                        $size_sel =  @iconv("UTF-8","windows-1251",$_POST['size_sel']);
                        $variants_length = count($variants);
                        $variants[$variants_length] = str_replace(" ","+", ' Размер: '.$size_sel);
                        //echo '$variants[$variants_length]= '.$variants[$variants_length];

                    }

                    if(isset($_POST["color_sel"]))
                    {
                        $color_sel =  @iconv("UTF-8","windows-1251",$_GET['color_sel']);
                        $variants_length = count($variants);
                        $variants[$variants_length] =  str_replace(" ","+", ' Цвет: '.$color_sel);
                    }


                    unset( $_SESSION["variants"] );
                    $_SESSION["variants"]=$variants;

//                    $this->shoppingCart((int)$_POST["addproduct"]);
                    cartAddToCart( $productID, $variants );
            }

                //Redirect( $cart_php_file."?shopping_cart=yes" );



            if (isset($_GET["remove"]) && $_GET["remove"] > 0) //remove from cart product with productID == $remove
            {
                    //remove from session vars
                    $res=DeCodeItemInClient( $_GET["remove"] );
                    $i=SearchConfigurationInSessionVariable(
                        $res["variants"], $res["productID"] );
                    if ( $i!=-1 ) $_SESSION["gids"][$i] = 0;

                Redirect( $cart_php_file."?shopping_cart=yes" );
            }


            if (isset($_POST["update"])) //update shopping cart content
            {
                foreach ($_POST as $key => $val)
                {
                    if (strstr($key, "count_"))
                    {

                            $res=DeCodeItemInClient( str_replace("count_","", $key) );

                            $is=GetProductInStockCount( $res["productID"] );

                            if ($val > 0)
                            {
                                $i=SearchConfigurationInSessionVariable($res["variants"], $res["productID"] );//check stock level

                                if (CONF_CHECKSTOCK==1)
                                    $val = min($val, $is);
                                $_SESSION["counts"][$i] = floor($val);
                            }
                            else //remove
                            {
                                $i=SearchConfigurationInSessionVariable(
                                    $res["variants"], $res["productID"] );
                                $_SESSION["gids"][$i] = 0;
                            }

                    }
                }
                Redirect( $cart_php_file."?shopping_cart=yes" );
            }

            if (isset($_GET["clear_cart"])) //completely clear shopping cart
            {
                cartClearCartContet();
                Redirect( $cart_php_file."?shopping_cart=yes" );
            }


            $resCart = cartGetCartContent();

            $resDiscount = dscCalculateDiscount( $resCart["total_price"],
                isset($_SESSION["log"])?$_SESSION["log"]:"" );
            $discount_value		= addUnitToPrice( $resDiscount["discount_current_unit"] );
            $discount_percent	= $resDiscount["discount_percent"];

            $this->smarty->assign("cart_content", $resCart["cart_content"] );
            $this->smarty->assign("cart_amount", $resCart["total_price"] - $resDiscount["discount_standart_unit"]);
            $this->smarty->assign('cart_min', show_price(CONF_MINIMAL_ORDER_AMOUNT));
            $this->smarty->assign("cart_total",	show_price( $resCart["total_price"] - $resDiscount["discount_standart_unit"] ) );

            // discount_prompt = 0 ( discount information is not shown )
            // discount_prompt = 1 ( discount information is showed simply without prompt )
            // discount_prompt = 2 ( discount information is showed with
            //			STRING_UNREGISTERED_CUSTOMER_DISCOUNT_PROMPT )
            // discount_prompt = 3 ( discount information is showed with
            //			STRING_UNREGISTERED_CUSTOMER_COMBINED_DISCOUNT_PROMPT )
            switch( CONF_DISCOUNT_TYPE )
            {
                // discount is switched off
                case 1:
                    $this->smarty->assign( "discount_prompt", 0 );
                    break;

                // discount is based on customer group
                case 2:
                    if ( isset($_SESSION["log"]) )
                    {
                        $this->smarty->assign( "discount_value", $discount_value );
                        $this->smarty->assign( "discount_percent", $discount_percent );
                        $this->smarty->assign( "discount_prompt", 1 );
                    }
                    else
                    {
                        $this->smarty->assign( "discount_value", $discount_value );
                        $this->smarty->assign( "discount_percent", $discount_percent );
                        $this->smarty->assign( "discount_prompt", 2 );
                    }
                    break;

                // discount is calculated with help general order price
                case 3:
                    $this->smarty->assign( "discount_prompt", 1 );
                    $this->smarty->assign( "discount_value", $discount_value );
                    $this->smarty->assign( "discount_percent", $discount_percent );
                    break;

                // discount equals to discount is based on customer group plus
                //		discount calculated with help general order price
                case 4:
                    if ( isset($_SESSION["log"]) )
                    {
                        $this->smarty->assign("discount_prompt", 1 );
                        $this->smarty->assign("discount_value", $discount_value );
                        $this->smarty->assign("discount_percent", $discount_percent );
                    }
                    else
                    {
                        $this->smarty->assign("discount_prompt", 3 );
                        $this->smarty->assign("discount_value", $discount_value );
                        $this->smarty->assign("discount_percent", $discount_percent );
                    }
                    break;

                // discount is calculated as MAX( discount is based on customer group,
                //			discount calculated with help general order price  )
                case 5:
                    if ( isset($_SESSION["log"]) )
                    {
                        $this->smarty->assign("discount_prompt", 1 );
                        $this->smarty->assign("discount_value", $discount_value );
                        $this->smarty->assign("discount_percent", $discount_percent );
                    }
                    else
                    {
                        $this->smarty->assign("discount_prompt", 3 );
                        $this->smarty->assign("discount_value", $discount_value );
                        $this->smarty->assign("discount_percent", $discount_percent );
                    }
                    break;
            }
        }
        $this->smarty->assign( "main_content_template", "shopping_cart.tpl.html" );
    }
}
