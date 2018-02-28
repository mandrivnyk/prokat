<?php

//ADMIN :: products managment

include("./cfg/connect.inc.php");

include("./includes/database/".DBMS.".php");

include("./core_functions/category_functions.php");

include("./core_functions/product_functions.php");

include("./core_functions/picture_functions.php");

include("./core_functions/configurator_functions.php");

include("./core_functions/datetime_functions.php");

include("./core_functions/tax_function.php");

include("./core_functions/setting_functions.php" );

include( "./core_functions/functions.php" );

include_once("./js/fckeditor/fckeditor.php");

include_once("./resizeimage.inc.php");

include_once( "./core_functions/url_function.php" );

//authorized access check

session_start();

@set_time_limit(0);

MagicQuotesRuntimeSetting();

//connect 2 database

db_connect(DB_HOST,DB_USER,DB_PASS) or die (db_error());
$q = db_query("SET NAMES CP1251");
$q = db_query("SET COLLATION_CONNECTION=CP1251_GENERAL_CI");

db_select_db(DB_NAME) or die (db_error());

settingDefineConstants();

// Получаем картинки для TOP SALE--------------------------------

$imgs_topsale = GetPicturesTOPSALE();

if(isset($_POST["url_name"]))

{

    $_POST["url_name"] = trim($_POST["url_name"]);

    //чистим урл

    //$_POST["url_name"]	= eregi_replace("([\~\,\:\@\#\№\%\^\&\?\!\*\(\)\$\+\=\'\"\`\; а-яА-ЯёЁ])+", '',$_POST["url_name"]);

    $_POST["url_name"]	=  preg_replace('/[^A-Za-z0-9-]+/', '', $_POST["url_name"]);

}

if(isset($_POST["title_one"]))

{

    $_POST["title_one"] = trim($_POST["title_one"]);

}

if(isset($_POST["title_two"]))

{

    $_POST["title_two"] = trim($_POST["title_two"]);

}

//current language

include("./cfg/language_list.php");

if (!isset($_SESSION["current_language"]) ||

    $_SESSION["current_language"] < 0 || $_SESSION["current_language"] > count($lang_list))

    $_SESSION["current_language"] = 0; //set default language

//include a language file

if (isset($lang_list[$_SESSION["current_language"]]) &&

    file_exists("languages/".$lang_list[$_SESSION["current_language"]]->filename))

{

    //include current language file

    include("languages/".$lang_list[$_SESSION["current_language"]]->filename);

}

else

{

    die("<font color=red><b>ERROR: Couldn't find language file!</b></font>");

}

include("./checklogin.php");

if ( CONF_BACKEND_SAFEMODE != 1 && (!isset($_SESSION["log"]) || strcmp($_SESSION["log"],ADMIN_LOGIN))) //unauthorized

{

    die (ERROR_FORBIDDEN);

}

// several function

// *****************************************************************************

// Purpose	gets size

// Inputs

// Remarks

// Returns

function GetPictureSize( $filename )

{

    $size_info=getimagesize("./products_pictures/".$filename);

    return ((string)($size_info[0] + 40 )).", ".((string)($size_info[1] + 40 ));

}

// *****************************************************************************

// Purpose	gets client JavaScript to reload opener page

// Inputs

// Remarks

// Returns

function ReLoadOpener()

{

    if ( $_GET["productID"]==0 )

        $categoryID=$_POST["categoryID"];

    else

    {

        $q=db_query("select categoryID from ".PRODUCTS_TABLE.

            " where productID='".$_GET["productID"]."'");

        $r=db_fetch_row($q);

        $categoryID=$r["categoryID"];

    }

    echo("<script language='JavaScript'>");

    echo("	try");

    echo("	{");

    echo("		window.opener.location.reload();");

    echo("	}");

    echo("	catch(e) { }");

    echo("</script>");

}

// *****************************************************************************

// Purpose	gets client JavaScript to close page

// Inputs

// Remarks

// Returns

function CloseWindow()

{

    echo("<script language='JavaScript'>");

    echo("	window.close();\n");

    echo("</script>");

}

// *****************************************************************************

// Purpose	gets client JavaScript to open in new window

//							option_value_configurator.php

// Inputs

// Remarks

// Returns

function OpenConfigurator($optionID, $productID)

{

    $url = "option_value_configurator.php?optionID=".$optionID."&productID=".$productID;

    echo("<script language='JavaScript'>\n");

    echo("		w=400; \n");

    echo("		h=400; \n");

    echo("		link='".$url."'; \n");

    echo("		var win = 'width='+w+',height='+h+',menubar=no,location=no,resizable=yes,scrollbars=yes';\n");

    echo("		wishWin = window.open(link,'wishWin',win);\n");

    echo("</script>\n");

}

if ( isset($_GET["delete"]) )

{

    if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON

    {

        Redirect("products.php?safemode=yes&productID=".$_GET["productID"]);

    }

    DeleteProduct( $_GET["productID"] );

    CloseWindow();

    ReLoadOpener();

}

if (!isset($_GET["productID"])) $_GET["productID"]=0;

$_GET["productID"] = (int)$_GET["productID"];

$productID = $_GET["productID"];

if (isset($_POST) && count($_POST)>0)

{

    if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON

    {

        Redirect("products.php?safemode=yes&productID=".$productID);

    }

}

if ( !isset($_POST["eproduct_available_days"]) )

    $_POST["eproduct_available_days"] = 365;

if ( !isset($_POST["eproduct_download_times"]) )

    $_POST["eproduct_download_times"] = 1;

if ( isset($_POST["eproduct_download_times"]) )

    $_POST["eproduct_download_times"] = (int)$_POST["eproduct_download_times"];

// dublicate product *********************************

if ( isset($_POST["dbl_product"]) )

{

    $productID = AddProduct(

        $_POST["categoryID"],$_POST["title_one"],$_POST["title_two"],$_POST["url_name"], $_POST["name"],  $_POST["producer"],$_POST["num_topsale"], $_POST["price"], $_POST["description"], $_POST["description2"], $_POST["brief_description2"],

        $_POST["in_stock"],$_POST["skidka"],

        $_POST["brief_description"], $_POST["list_price"],

        $_POST["product_code"], $_POST["sort_order"],

        isset($_POST["ProductIsProgram"]), "eproduct_filename",

        $_POST["eproduct_available_days"],

        $_POST["eproduct_download_times"],

        $_POST["weight"], $_POST["meta_description"],

        $_POST["meta_keywords"], isset($_POST["free_shipping"]),

        $_POST["min_order_amount"], $_POST["shipping_freight"],

        $_POST["tax_class"] );

    $_GET["productID"] = $productID;

    $updatedValues = ScanPostVariableWithId( array( "option_value", "option_radio_type" ) );

    cfgUpdateOptionValue($productID, $updatedValues);

    if ( CONF_UPDATE_GCV == '1' )

        update_products_Count_Value_For_Categories(1);

    ReLoadOpener();

    if ( $_POST["save_product_without_closing"]=="0" )

        CloseWindow();

}

////////////////////////////////////////////////////////////////////

// save product

if ( isset($_POST["save_product"]) )

{

    if ( $_GET["productID"] == 0 )

    {

        $productID = AddProduct(

            $_POST["categoryID"],$_POST["title_one"],$_POST["title_two"],$_POST["url_name"], $_POST["name"],  $_POST["producer"],$_POST["num_topsale"], $_POST["price"], $_POST["description"], $_POST["description2"], $_POST["brief_description2"],

            $_POST["in_stock"],$_POST["skidka"],

            $_POST["brief_description"], $_POST["list_price"],

            $_POST["product_code"], $_POST["sort_order"],

            isset($_POST["ProductIsProgram"]), "eproduct_filename",

            $_POST["eproduct_available_days"],

            $_POST["eproduct_download_times"],

            $_POST["weight"], $_POST["meta_description"],

            $_POST["meta_keywords"], isset($_POST["free_shipping"]),

            $_POST["min_order_amount"], $_POST["shipping_freight"],

            $_POST["tax_class"] );

        $_GET["productID"] = $productID;

        /*echo '<pre>';

            print_r($_POST);

        echo '</pre>';

        exit();*/

        $updatedValues = ScanPostVariableWithId( array( "option_value", "option_radio_type" ) );

        cfgUpdateOptionValue($productID, $updatedValues);

        //------------------ URL REWRITE-------------------------------------

        include_once("./public_scripts/call_url_rewrite.php");

        //------------------------------------------------------------------

    }

    else

    {

        /*echo $_POST["url_name"];

        exit();*/

        UpdateProduct( $productID,

            $_POST["categoryID"],$_POST["title_one"],$_POST["title_two"],$_POST["url_name"], $_POST["name"],$_POST["producer"],$_POST["num_topsale"],  $_POST["price"], $_POST["description"], $_POST["description2"], $_POST["brief_description2"],

            $_POST["in_stock"],$_POST["skidka"], $_POST["rating"],

            $_POST["brief_description"], $_POST["list_price"],

            $_POST["product_code"], $_POST["sort_order"],

            isset($_POST["ProductIsProgram"]), "eproduct_filename",

            $_POST["eproduct_available_days"],

            $_POST["eproduct_download_times"],

            $_POST["weight"], $_POST["meta_description"],

            $_POST["meta_keywords"], isset($_POST["free_shipping"]),

            $_POST["min_order_amount"], $_POST["shipping_freight"],

            $_POST["tax_class"] );

        /*echo '<pre>';

            print_r($_POST);

        echo '</pre>';*/

        $updatedValues = ScanPostVariableWithId( array( "option_value", "option_radio_type" ) );

        cfgUpdateOptionValue($productID, $updatedValues);

        //exit();

        //------------------ URL REWRITE-------------------------------------

        include_once("./public_scripts/call_url_rewrite.php");

        //------------------------------------------------------------------

    }

    //exit();

    if ( CONF_UPDATE_GCV == '1' )

        update_products_Count_Value_For_Categories(1);

    ReLoadOpener();

    if ( $_POST["save_product_without_closing"]=="0" )

        CloseWindow();

}

// save pictures

if ( isset( $_POST["save_pictures"] ) )

{

    if ( $_GET["productID"] == 0 )

    {

        $productID = AddProduct(

            $_POST["categoryID"],$_POST["title_one"],$_POST["title_two"],$_POST["url_name"], $_POST["name"],  $_POST["producer"],$_POST["num_topsale"], $_POST["price"], $_POST["description"], $_POST["description2"], $_POST["brief_description2"],

            $_POST["in_stock"],$_POST["skidka"],

            $_POST["brief_description"], $_POST["list_price"],

            $_POST["product_code"], $_POST["sort_order"],

            isset($_POST["ProductIsProgram"]), "eproduct_filename",

            $_POST["eproduct_available_days"],

            $_POST["eproduct_download_times"],

            $_POST["weight"], $_POST["meta_description"],

            $_POST["meta_keywords"], isset($_POST["free_shipping"]),

            $_POST["min_order_amount"], $_POST["shipping_freight"],

            $_POST["tax_class"] );

        $_GET["productID"] = $productID;

    }

    AddNewPictures( $_GET["productID"], "new_filename", "new_thumbnail", "new_enlarged", $_POST["default_picture"] );

    $updatedFileNames = ScanPostVariableWithId(

        array( "filename", "thumbnail", "enlarged" ) );

    UpdatePictures( $_GET["productID"], $updatedFileNames, $_POST["default_picture"] );

    ReLoadOpener();

}

// delete three picture

if ( isset( $_GET["delete_pictures"] ) )

{

    if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON

    {

        Redirect("products.php?safemode=yes&productID=".$productID);

    }

    DeleteThreePictures( $_GET["photoID"] );

    ReLoadOpener();

}

// delete one picture

if ( isset( $_GET["delete_one_picture"] ) )

{

    if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON

    {

        Redirect("products.php?safemode=yes&productID=".$productID);

    }

    if ( isset( $_GET["filename"] ) )

        DeleteFilenamePicture( $_GET["filename"] );

    if ( isset( $_GET["thumbnail"] ) )

        DeleteThumbnailPicture( $_GET["thumbnail"] );

    if ( isset( $_GET["enlarged"] ) )

        DeleteEnlargedPicture( $_GET["enlarged"] );

    ReLoadOpener();

}

// add new product and open configurator

// it works when user click "setting..." and new product is added

if ( isset($_POST["AddProductAndOpenConfigurator"]) )

{

    $productID = AddProduct(

        $_POST["categoryID"],$_POST["title_one"],$_POST["title_two"],$_POST["url_name"], $_POST["name"],  $_POST["producer"],$_POST["num_topsale"], $_POST["price"], $_POST["description"], $_POST["description2"], $_POST["brief_description2"],

        $_POST["in_stock"],$_POST["skidka"],

        $_POST["brief_description"], $_POST["list_price"],

        $_POST["product_code"], $_POST["sort_order"],

        isset($_POST["ProductIsProgram"]), "eproduct_filename",

        $_POST["eproduct_available_days"],

        $_POST["eproduct_download_times"],

        $_POST["weight"], $_POST["meta_description"],

        $_POST["meta_keywords"], isset($_POST["free_shipping"]),

        $_POST["min_order_amount"], $_POST["shipping_freight"],

        $_POST["tax_class"] );

    $_GET["productID"] = $productID;

    $updatedValues = ScanPostVariableWithId( array( "option_value", "option_radio_type" ) );

    cfgUpdateOptionValue($productID, $updatedValues);

    OpenConfigurator($_POST["optionID"], $productID);

}

// remove product from appended category

if ( isset($_GET["remove_from_app_cat"]) )

{

    if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON

    {

        Redirect("products.php?safemode=yes&productID=".$productID);

    }

    catRemoveProductFromAppendedCategory( $_GET["productID"],

        $_GET["remove_from_app_cat"] );

    catUpdateProductCount($_GET["productID"], $_GET["remove_from_app_cat"], -1);

}

// add into new appended category

if ( isset($_POST["add_category"]) )

{

    if ( $_GET["productID"] == 0 )

    {

        $productID = AddProduct(

            $_POST["categoryID"],$_POST["title_one"],$_POST["title_two"],$_POST["url_name"], $_POST["name"],  $_POST["producer"],$_POST["num_topsale"], $_POST["price"], $_POST["description"], $_POST["description2"], $_POST["brief_description2"],

            $_POST["in_stock"],$_POST["skidka"],

            $_POST["brief_description"], $_POST["list_price"],

            $_POST["product_code"], $_POST["sort_order"],

            isset($_POST["ProductIsProgram"]), "eproduct_filename",

            $_POST["eproduct_available_days"],

            $_POST["eproduct_download_times"],

            $_POST["weight"], $_POST["meta_description"],

            $_POST["meta_keywords"], isset($_POST["free_shipping"]),

            $_POST["min_order_amount"], $_POST["shipping_freight"],

            $_POST["tax_class"] );

        $_GET["productID"] = $productID;

    }

    catAddProductIntoAppendedCategory($_GET["productID"],

        $_POST["new_appended_category"] );

    if ( CONF_UPDATE_GCV == '1' )

        catUpdateProductCount($_GET["productID"], $_POST["new_appended_category"]);

}

// show product

if ( $_GET["productID"] != 0 )

{

    $product = GetProduct($_GET["productID"]);

    //----- удаляем кеш файл, чтоб увидеть обновление--------------------------------

    if($product["url_name"] !== '')

    {

        if (file_exists('./cache/'.$product["url_name"].'.cache'))

            unlink('./cache/'.$product["url_name"].'.cache');

    }

    //-------------------------------------------------------------------------------

    $title = $product["name"];

}

else

{

    $product = array();

    $title = ADMIN_PRODUCT_NEW;

    $cat = isset($_GET["categoryID"]) ? $_GET["categoryID"] : 0;

    $product["categoryID"]			= $cat;

    $product["title_one"]			= "";

    $product["title_two"]			= "";

    $product["url_name"]			= "";

    $product["name"]				= "";

    $product["producer"]				= "";

    $product["num_topsale"]				= "";

    $product["description"]			= "";

    $product["description2"]			= "";

    $product["brief_description2"]			= "";

    $product["customers_rating"]	= "";

    $product["Price"]				= 0;

    $product["picture"]				= "";

    $product["in_stock" ]			= 5;

    $product["skidka" ]			= 0;

    $product["thumbnail" ]			= "";

    $product["big_picture" ]		= "";

    $product["brief_description"]	= "";

    $product["list_price"]			= 0;

    $product["product_code"]		= "";

    $product["sort_order"]			= 0;

    $product["date_added"]			= null;

    $product["date_modified"]		= null;

    $product["eproduct_filename"]			= "";

    $product["eproduct_available_days"]		= 365;

    $product["eproduct_download_times"]		= 1;

    $product["weight"]				= 0;

    $product["meta_description"]	= "";

    $product["meta_keywords"]		= "";

    $product["free_shipping"]		= 0;

    $product["min_order_amount"]	= 1;

    if ( CONF_DEFAULT_TAX_CLASS == '0' )

        $product["classID"]	= "null";

    else

        $product["classID"] = CONF_DEFAULT_TAX_CLASS;

    $product["shipping_freight"]	= 0;

}

// gets ALL product options

$options = cfgGetProductOptionValue( $_GET["productID"] );


$options = html_spchars($options);

// gets pictures

$picturies = GetPictures( $_GET["productID"] );

// get appended categories

$appended_categories = catGetAppendedCategoriesToProduct( $_GET["productID"] );

// hide/show tables

$showAppendedParentsTable = 0;

if ( isset($_POST["AppendedParentsTableHideTable_hidden"]) )

{

    if ( $_POST["AppendedParentsTableHideTable_hidden"] == "1" )

        $showAppendedParentsTable = 1;

}

else if ( isset($_GET["remove_from_app_cat"]) )

    $showAppendedParentsTable = 1;

$showConfiguratorTable = 0;

if ( isset($_POST["ConfiguratorHideTable_hidden"]) )

    if ( $_POST["ConfiguratorHideTable_hidden"] == "1" )

        $showConfiguratorTable = 1;

$showVariantsProduct = 0;


if ( isset($_POST["VariantsProductHideTable_hidden"]) )

    if ( $_POST["VariantsProductHideTable_hidden"] == "1" )

        $showVariantsProduct = 1;



$showPhotoTable = 0;

if ( isset($_POST["PhotoHideTable_hidden"]) )

{

    if ( $_POST["PhotoHideTable_hidden"] == "1" )

        $showPhotoTable = 1;

}

else if ( isset($_GET["delete_pictures"]) ||  isset($_GET["delete_one_picture"])  )

    $showPhotoTable = 1;

$tax_classes = taxGetTaxClasses();

$brends_arr = GetProductBrends();

?>

<html>

<head>

    <link rel=STYLESHEET href="style1.css" type="text/css">

    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo DEFAULT_CHARSET;?>">

    <title><?php echo ADMIN_PRODUCT_TITLE;?></title>

    <script>

        function confirmDelete(question, where)

        {

            temp = window.confirm(question);

            if (temp) //delete

            {

                window.location=where;

            }

        }

        function open_window(link,w,h) //opens new window

        {

            var win = "width="+w+",height="+h+",menubar=no,location=no,resizable=yes,scrollbars=yes";

            wishWin = window.open(link,'wishWin',win);

        }

        function position_this_window()

        {

            var x = (screen.availWidth - 795) / 2;

            window.resizeTo(795, screen.availHeight - 50);

            window.moveTo(Math.floor(x),25);

        }

        function add_from_select(el)

        {

            //var selct;

            //tagList = document.getElementsByName('myTag');

            //selct = document.getElementsByName(el[]);

            //alert (selct.option);

        }

    </script>

</head>

<body bgcolor=#FFFFE2 onLoad="position_this_window();">

<center>

    <p>

        <b><?php echo $title; ?></b>

        <?php

        if ( isset($_GET["couldntToDelete"]) )

        {

            ?>

            <br>

            <font color=red>

                <b><?php echo COULD_NOT_DELETE_THIS_PRODUCT?><b>

            </font>

            <?php

        }

        ?>

        <?php

        if ( isset($_GET["safemode"]) )

        {

            echo "<p><font color=red><b>".ADMIN_SAFEMODE_WARNING."<b></font>";

        }

        ?>

        <form enctype="multipart/form-data" action="products.php?productID=<?php echo $_GET["productID"]?>"

              method=post name="MainForm">

            <table width=100% border=0 cellpadding=3 cellspacing=0>

                <tr>

                    <td align=right><?php echo ADMIN_CATEGORY_PARENT;?></td>

                    <td>

                        <select name="categoryID" <?php

                        if (CONF_FULLY_EXPAND_CATEGORIES_IN_ADMIN_MODE == 0) // update list

                            echo "onChange=\"window.location='products.php?productID=".$_GET["productID"]."&change_category='+document.MainForm.categoryID.value;\"";

                        ?>>

                            <?php

                            if (CONF_FULLY_EXPAND_CATEGORIES_IN_ADMIN_MODE == 1) echo "<option value=\"1\">".ADMIN_CATEGORY_ROOT."</option>";

                            //show categories select element

                            $core_category = (isset($_GET["change_category"])) ? (int)$_GET["change_category"] : $product["categoryID"] ;

                            if (CONF_FULLY_EXPAND_CATEGORIES_IN_ADMIN_MODE == 0)

                                $cats = catGetCategoryCompactCList($core_category);

                            else

                                $cats = catGetCategoryCList();

                            for ($i=0; $i<count($cats); $i++)

                            {

                                echo "<option value=\"".$cats[$i]["categoryID"]."\"";

                                if ($core_category == $cats[$i]["categoryID"]) //select category

                                    echo " selected";

                                echo ">";

                                for ($j=0;$j<$cats[$i]["level"];$j++) echo "&nbsp;&nbsp;";

                                echo $cats[$i]["name"];

                                echo "</option>";

                            }

                            ?>

                        </select>

                    </td>

                </tr>

                <tr>

                    <td align=center colspan=2>

                        <a href="JavaScript:AppendedParentsTableHideTable();">

                            <?php echo ADMIN_CATEGORY_APPENDED_PARENTS;?>

                            <input type=hidden name='AppendedParentsTableHideTable_hidden'

                                   value='<?php echo $showAppendedParentsTable;?>'>

                        </a>

                        <script language='javascript'>

                            function AppendedParentsTableHideTable()

                            {

                                if ( AppendedParentsTable.style.display == 'none' )

                                {

                                    AppendedParentsTable.style.display = 'block';

                                    document.MainForm.AppendedParentsTableHideTable_hidden.value='1';

                                }

                                else

                                {

                                    AppendedParentsTable.style.display = 'none';

                                    document.MainForm.AppendedParentsTableHideTable_hidden.value='0';

                                }

                            }

                        </script>

                        <table border=0 cellpadding=5 cellspacing=1 bgcolor=#C3BD7C

                               id='AppendedParentsTable'>

                            <tr>

                                <td colspan=2 align=center>

                                    <b><?php echo ADMIN_CATEGORY_APPENDED_PARENTS;?>:</b>

                                </td>

                            </tr>

                            <tr bgcolor=#F5F5C5>

                                <td align=center><?php echo ADMIN_CATEGORY_TITLE;?></td>

                                <td width=1%>&nbsp;</td>

                            </tr>

                            <?php

                            foreach( $appended_categories as $app_cat )

                            {

                                ?>

                                <tr bgcolor=#FFFFE2>

                                    <td align=center>

                                        <?php echo $app_cat["category_name"];?>

                                    </td>

                                    <td width=1%>

                                        <a href="javascript:confirmDelete('<?php echo QUESTION_DELETE_CONFIRMATION;?>','products.php?productID=<?php echo $_GET["productID"]?>&remove_from_app_cat=<?php echo $app_cat["categoryID"]?>');">

                                            <img src="images/remove.jpg" border=0 alt="<?php echo DELETE_BUTTON?>">

                                        </a>

                                    </td>

                                </tr>

                                <?php

                            }

                            ?>

                            <tr>

                                <td align=center colspan=2>

                                    <?php echo ADD_BUTTON;?>:

                                </td>

                            </tr>

                            <tr bgcolor=white>

                                <td align=center>

                                    <select name='new_appended_category' <?php

                                    if (CONF_FULLY_EXPAND_CATEGORIES_IN_ADMIN_MODE == 0) // update list

                                        echo "onChange=\"window.location='products.php?productID=".$_GET["productID"]."&change_app_category='+document.MainForm.new_appended_category.value;\"";

                                    ?>>

                                        <?php

                                        $change_app_category = isset($_GET["change_app_category"]) ? (int)$_GET["change_app_category"] : $product["categoryID"];

                                        if (CONF_FULLY_EXPAND_CATEGORIES_IN_ADMIN_MODE == 0)

                                            $cats = catGetCategoryCompactCList($change_app_category);

                                        else

                                            $cats = catGetCategoryCList();

                                        for ($i=0; $i<count($cats); $i++)

                                            if ($cats[$i]["categoryID"] > 1)

                                            {

                                                echo "<option value=\"".$cats[$i]["categoryID"]."\"";

                                                if ($change_app_category == $cats[$i]["categoryID"]) echo " selected";

                                                echo ">";

                                                for ($j=0;$j<$cats[$i]["level"];$j++) echo "&nbsp;&nbsp;";

                                                echo $cats[$i]["name"];

                                                echo "</option>";

                                            }

                                        ?>

                                    </select>

                                </td>

                                <td width=1%>&nbsp;</td>

                            </tr>

                            <tr>

                                <td colspan=2 align=center>

                                    <input type=submit value='<?php echo ADD_BUTTON;?>' name='add_category'>

                                </td>

                            </tr>

                        </table>

                        <script language='JavaScript'>

                            <?php

                            if ( $showAppendedParentsTable == 0 && !isset($_GET["change_app_category"]) )

                            {

                            ?>

                            AppendedParentsTable.style.display = 'none';

                            <?php

                            }

                            ?>

                        </script>

                    </td>

                </tr>

                <tr>

                    <td align=right>

                        <?php echo ADMIN_CATEGORY_TITLE_ONE;?>

                    </td>

                    <td>

                        <input type="text"  size="40" name="title_one" value="<?php echo str_replace("\"","&quot;",$product["title_one"]); ?>">

                    </td>

                </tr>

                <tr>

                    <td align="center" colspan="2">

                        <?php echo ADMIN_CATEGORY_TITLE_TWO;?><br>

                        <textarea name='title_two' 	rows="3" cols="60"><?php echo str_replace("\"","&quot;",$product["title_two"]);?></textarea>

                    </td>

                </tr>

                <tr>

                    <td align=right>

                        <?php echo ADMIN_CATEGORY_URL_NAME;?>

                    </td>

                    <td>

                        <input type="text"  size="40" name="url_name" value="<?php echo str_replace("\"","&quot;",$product["url_name"]); ?>">

                    </td>

                </tr>

                <tr>

                    <td align=right>

                        <?php echo ADMIN_PRODUCT_NAME;?>

                    </td>

                    <td>

                        <input type="text"  size="40" name="name" value="<?php echo str_replace("\"","&quot;",$product["name"]); ?>">

                    </td>

                </tr>

                <tr>

                    <td align=right><?php echo ADMIN_PRODUCER;?></td>

                    <td>

                        <select name='producer'>

                            <option value='null'><?php echo ADMIN_NOT_DEFINED;?></option>

                            <?php

                            foreach( $brends_arr as $brends_el )

                            {

                                ?>

                                <option value='<?php echo $brends_el["name"];?>'

                                    <?php

                                    if ( $product["producer"] == $brends_el["name"] )

                                    {

                                        ?>

                                        selected

                                        <?php

                                    }

                                    ?>

                                >

                                    <?php echo $brends_el["name"];?>

                                </option>

                                <?php

                            }

                            ?>

                        </select>

                    </td>

                </tr>

                <tr>

                    <td align=right><?php echo ADMIN_TOPSALE;?></td>

                    <td>

                        <select name='num_topsale'>

                            <option value='0'><?php echo ADMIN_NOT_DEFINED;?></option>

                            <?php

                            foreach( $imgs_topsale as $imgs_topsale_el )

                            {

                                ?>

                                <option value='<?php echo $imgs_topsale_el["num_topsale"];?>'

                                    <?php

                                    if ( $product["num_topsale"] == $imgs_topsale_el["num_topsale"] )

                                    {

                                        ?>

                                        selected

                                        <?php

                                    }

                                    ?>

                                >

                                    <?php echo '('.$imgs_topsale_el["num_topsale"].') '.$imgs_topsale_el["filename"];?>

                                </option>

                                <?php

                            }

                            ?>

                        </select>

                    </td>

                </tr>

                <tr>

                    <td align=right>

                        <?php echo ADMIN_PRODUCT_CODE;?>

                    </td>

                    <td>

                        <input type="text" name="product_code"  size="40"

                               value="<?php echo str_replace("\"","&quot;",$product["product_code"] ); ?>">

                    </td>

                </tr>

                <tr>

                    <td align=right><?php echo ADMIN_TAX_CLASS;?></td>

                    <td>

                        <select name='tax_class'>

                            <option value='null'><?php echo ADMIN_NOT_DEFINED;?></option>

                            <?php

                            foreach( $tax_classes as $tax_class )

                            {

                                ?>

                                <option value='<?php echo $tax_class["classID"];?>'

                                    <?php

                                    if ( $product["classID"] == $tax_class["classID"] )

                                    {

                                        ?>

                                        selected

                                        <?php

                                    }

                                    ?>

                                >

                                    <?php echo $tax_class["name"];?>

                                </option>

                                <?php

                            }

                            ?>

                        </select>

                    </td>

                </tr>

                <?php

                if ( !is_null($product["date_added"])  )

                {

                    ?>

                    <tr>

                        <td align=right>

                            <?php echo ADMIN_DATE_ADDED;?>

                        </td>

                        <td>

                            <?php echo $product["date_added"]?>

                        </td>

                    </tr>

                    <?php

                }

                ?>

                <?php

                if ( !is_null($product["date_modified"]) )

                {

                    ?>

                    <tr>

                        <td align=right>

                            <?php echo ADMIN_DATE_MODIFIED;?>

                        </td>

                        <td>

                            <?php echo $product["date_modified"]?>

                        </td>

                    </tr>

                    <?php

                }

                ?>

                <?php

                if ($_GET["productID"])

                {

                    ?>

                    <tr>

                        <td align=right>

                            <?php echo ADMIN_PRODUCT_RATING;?>

                        </td>

                        <td>

                            <input type=text name="rating"

                                   value="<?php echo str_replace("\"","&quot;",$product["customers_rating"]); ?>">

                        </td>

                    </tr>

                <?php }; ?>

                <tr>

                    <td align=right>

                        <?php echo ADMIN_SORT_ORDER;?>

                    </td>

                    <td>

                        <input type="text" name="sort_order" value="<?php echo $product["sort_order"];?>">

                    </td>

                </tr>

                <tr>

                    <td align=right>

                        <?php echo ADMIN_PRODUCT_PRICE;?>, <?php echo STRING_UNIVERSAL_CURRENCY;?><br>(<?php echo STRING_NUMBER_ONLY;?>):

                    </td>

                    <td>

                        <input type="text" name="price" value=<?php echo $product["Price"]; ?>>

                    </td>

                </tr>

                <tr>

                    <td align=right>

                        <?php echo ADMIN_PRODUCT_LISTPRICE;?>, <?php echo STRING_UNIVERSAL_CURRENCY;?><br>(<?php echo STRING_NUMBER_ONLY;?>):

                    </td>

                    <td>

                        <input type="text" name="list_price" value=<?php echo $product["list_price"];?>>

                    </td>

                </tr>

                <?php

                if ($product["in_stock"]<0) $is = 0;

                else $is = $product["in_stock"];

                if (CONF_CHECKSTOCK == 1) {

                    $skidka = $product["skidka"];

                    ?>

                    <tr>

                        <td align=right><?php echo ADMIN_PRODUCT_INSTOCK;?>:</td>

                        <td><input type="text" name="in_stock" value="<?php echo $is;?>"></td>

                    </tr>

                <?php } else { ?>

                    <input type=hidden name="in_stock" value="<?php echo $is;?>">

                <?php } ?>

                <tr>

                    <td align=right>

                        <?php echo ADMIN_SKIDKA_PRODUCT;?>

                    </td>

                    <td>

                        <input type="text" name="skidka" value="<?php echo $skidka?>"  style="background: #eae8e3;">

                    </td>

                </tr>

                <tr>

                    <td align=right>

                        <?php echo ADMIN_SHIPPING_FREIGHT;?>

                    </td>

                    <td>

                        <input type="text" name="shipping_freight" value=<?php echo $product["shipping_freight"];?>>

                    </td>

                </tr>

                <tr>

                    <td align=right><?php echo ADMIN_PRODUCT_WEIGHT;?></td>

                    <td><input type=text name='weight'

                               value='<?php echo $product["weight"];?>'> <?php echo CONF_WEIGHT_UNIT;?></td>

                </tr>

                <tr>

                    <td align=right><?php echo ADMIN_FREE_SHIPPING;?></td>

                    <td><input type=checkbox name='free_shipping'

                            <?php

                            if ( $product["free_shipping"] )

                            {

                                ?>

                                checked

                                <?php

                            }

                            ?>

                               value='1'>

                    </td>

                </tr>

                <tr>

                    <td align=right><?php echo ADMIN_MIN_ORDER_AMOUNT;?></td>

                    <td><input type=text name='min_order_amount'

                               value='<?php echo $product["min_order_amount"];?>'>

                    </td>

                </tr>

                <!-- ************************  VARIANTS OF PRODUCT *********************** -->


                <tr><td align=center colspan=2>

                        <center>

                            <a href="JavaScript:VariantsProductHideTable();">

                                <?php echo ADMIN_VARIANTS_PRODUCTS;?>

                                <input type=hidden name='VariantsProductHideTable_hidden'

                                       value='<?php echo $showVariantsProduct ?>'>

                            </a>

                        </center>

                        <script language='javascript'>

                            function VariantsProductHideTable()

                            {

                                if ( VariantsProductTable.style.display == 'none' )

                                {

                                    VariantsProductTable.style.display = 'block';

                                    document.MainForm.VariantsProductHideTable_hidden.value='1';

                                }

                                else

                                {

                                    VariantsProductTable.style.display = 'none';

                                    document.MainForm.VariantsProductHideTable_hidden.value='0';

                                }

                            }

                        </script>
                        <table id='VariantsProductTable'>

                            <tr><td>
                                    <textarea name='variantsProduct' 	rows="3" cols="60"><?php echo $product["variantsProduct"];?></textarea>
                                </td></tr>
                        </table>




                        <!-- ************************ CONFIGUARTOR *********************** -->

                <tr><td align=center colspan=2>

                        <center>

                            <a href="JavaScript:ConfiguratorHideTable();">

                                <?php echo ADMIN_CONFIGURATOR;?>

                                <input type=hidden name='ConfiguratorHideTable_hidden'

                                       value='<?php echo $showConfiguratorTable;?>'>

                            </a>

                        </center>

                        <script language='javascript'>

                            function ConfiguratorHideTable()

                            {

                                if ( ConfiguratorTable.style.display == 'none' )

                                {

                                    ConfiguratorTable.style.display = 'block';

                                    document.MainForm.ConfiguratorHideTable_hidden.value='1';

                                }

                                else

                                {

                                    ConfiguratorTable.style.display = 'none';

                                    document.MainForm.ConfiguratorHideTable_hidden.value='0';

                                }

                            }

                        </script>

                        <table id='ConfiguratorTable'>

                            <tr><td>

                                    <script language='JavaScript'>

                                        function SetOptionValueTypeRadioButton( id, radioButtonState )

                                        {

                                            if ( radioButtonState == "UN_DEFINED" )

                                                document.all["option_radio_type_"+id][0].click();

                                            else if ( radioButtonState == "ANY_VALUE" )

                                                document.all["option_radio_type_"+id][1].click();

                                            else if ( radioButtonState == "N_VALUES" )

                                                document.all["option_radio_type_"+id][2].click();

                                        }

                                        function SetEnabledStateTextValueField( id, radioButtonState )

                                        {

                                            if ( radioButtonState == "UN_DEFINED" || radioButtonState == "N_VALUES" )

                                            {

                                                document.all["option_value_"+id].disabled=true;

                                                document.all["option_value_"+id].value="";

                                            }

                                            else

                                                document.all["option_value_"+id].disabled=false;

                                        }

                                    </script>

                                    <?php

                                    //product extra options

                                    foreach($options as $option)

                                    {

                                        $option_row = $option["option_row"];

                                        $value_row  = $option["option_value"];

                                        $ValueCount = $option["value_count"];

                                        ?>

                                        <table border='0' cellspacing='0' cellpadding='4' width=100%>

                                            <tr>

                                                <td align=left width=25%>

                                                    <b><?php echo $option_row["name"]?></b>:

                                                </td>

                                                <td>

                                                    <input name='option_radio_type_<?php echo $option_row["optionID"]?>'

                                                           type='radio' value="UN_DEFINED"

                                                           onclick="JavaScript:SetEnabledStateTextValueField(<?php echo $option_row['optionID']?>, 'UN_DEFINED' );"

                                                        <?php

                                                        if ( (is_null($value_row["option_value"]) || $value_row["option_value"] == '')

                                                            && $value_row["option_type"]==0 )

                                                            echo "checked";

                                                        ?>

                                                    >


                                                </td>

                                                <td>

                                                    <?php echo ADMIN_NOT_DEFINED;?>

                                                </td>

                                            </tr>

                                            <?php

                                            if($option_row["optionID"] == '17')///-------------------------------------------БРЕНДЫ---------------------------------------------

                                            {

                                                $brends_arr = GetProductBrends();

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name=option_value_'.$option_row["optionID"].'>';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                for($i=0; $i<count($brends_arr);$i++)

                                                {

                                                    echo '<option value="'.$brends_arr[$i]['name'].'"'; if($value_row['option_value']==$brends_arr[$i]['name'])

                                                { echo 'selected';} else echo ''; echo '>'.$brends_arr[$i]['name'].'</option>';

                                                }

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '16')///-------------------------------------------Класс палатки---------------------------------------------

                                            {

                                                $class_arr = GetProductClass();

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name=option_value_'.$option_row["optionID"].'>';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                for($i=0; $i<count($class_arr);$i++)

                                                {

                                                    echo '<option value="'.$class_arr[$i]['name'].'"'; if($value_row['option_value']==$class_arr[$i]['name'])

                                                { echo 'selected';} else echo ''; echo '>'.$class_arr[$i]['name'].'</option>';

                                                }

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '18')///-------------------------------------------Система палатки---------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name=option_value_'.$option_row["optionID"].'>';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="полусфера"';   if($value_row['option_value']=='полусфера')   { echo 'selected';} else echo ''; echo '>полусфера</option>';

                                                echo '<option value="полубочка"';   if($value_row['option_value']=='полубочка')   { echo 'selected';} else echo ''; echo '>полубочка</option>';

                                                echo '<option value="двухскатная"'; if($value_row['option_value']=='двухскатная') { echo 'selected';} else echo ''; echo '>двухскатная</option>';

                                                echo '<option value="подвесная"';   if($value_row['option_value']=='подвесная')   { echo 'selected';} else echo ''; echo '>подвесная</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '1')///-------------------------------------------Количество мест---------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="1"';   if($value_row['option_value']=='1')   { echo 'selected';} else echo ''; echo '>1</option>';

                                                echo '<option value="2"';   if($value_row['option_value']=='2')   { echo 'selected';} else echo ''; echo '>2</option>';

                                                echo '<option value="2+1"';   if($value_row['option_value']=='2+1')   { echo 'selected';} else echo ''; echo '>2+1</option>';

                                                echo '<option value="3"';   if($value_row['option_value']=='3')   { echo 'selected';} else echo ''; echo '>3</option>';

                                                echo '<option value="3+1"';   if($value_row['option_value']=='3+1')   { echo 'selected';} else echo ''; echo '>3+1</option>';

                                                echo '<option value="4"';   if($value_row['option_value']=='4')   { echo 'selected';} else echo ''; echo '>4</option>';

                                                echo '<option value="4+1"';   if($value_row['option_value']=='4+1')   { echo 'selected';} else echo ''; echo '>4+1</option>';

                                                echo '<option value="4+2"';   if($value_row['option_value']=='4+2')   { echo 'selected';} else echo ''; echo '>4+2</option>';

                                                echo '<option value="5"';   if($value_row['option_value']=='5')   { echo 'selected';} else echo ''; echo '>5</option>';

                                                echo '<option value="6"';   if($value_row['option_value']=='6')   { echo 'selected';} else echo ''; echo '>6</option>';

                                                echo '<option value="7"';   if($value_row['option_value']=='7')   { echo 'selected';} else echo ''; echo '>7</option>';

                                                echo '<option value="8"';   if($value_row['option_value']=='8')   { echo 'selected';} else echo ''; echo '>8</option>';

                                                echo '<option value="8+2"';   if($value_row['option_value']=='8+2')   { echo 'selected';} else echo ''; echo '>8+2</option>';

                                                echo '<option value="9"';   if($value_row['option_value']=='9')   { echo 'selected';} else echo ''; echo '>9</option>';

                                                echo '<option value="10"';   if($value_row['option_value']=='10')   { echo 'selected';} else echo ''; echo '>10</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '239')///-------------------------------------------Количество Клапанов---------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="1"';   if($value_row['option_value']=='1')   { echo 'selected';} else echo ''; echo '>1</option>';

                                                echo '<option value="2"';   if($value_row['option_value']=='2')   { echo 'selected';} else echo ''; echo '>2</option>';

                                                echo '<option value="3"';   if($value_row['option_value']=='3')   { echo 'selected';} else echo ''; echo '>3</option>';

                                                echo '<option value="4"';   if($value_row['option_value']=='4')   { echo 'selected';} else echo ''; echo '>4</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '240')///-------------------------------------------Кнопки для состегивания ковриков ---------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="Есть"';   if($value_row['option_value']=='Есть')   { echo 'selected';} else echo ''; echo '>Есть</option>';

                                                echo '<option value="Нет"';   if($value_row['option_value']=='Нет')   { echo 'selected';} else echo ''; echo '>Нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '237')///------------------------------------------- Материал верха ---------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="Полиэстер Chamois suede (тканная замша)"';   if($value_row['option_value']=='Полиэстер Chamois suede (тканная замша)')   { echo 'selected';} else echo ''; echo '>Полиэстер Chamois suede (тканная замша)</option>';

                                                echo '<option value="Polyester 75D Micro Brushed Lamminated TPU"';   if($value_row['option_value']=='Polyester 75D Micro Brushed Lamminated TPU')   { echo 'selected';} else echo ''; echo '>Polyester 75D Micro Brushed Lamminated TPU</option>';

                                                echo '<option value="Polyester 50D Stretch Lamminated TPU"';   if($value_row['option_value']=='Polyester 50D Stretch Lamminated TPU')   { echo 'selected';} else echo ''; echo '>Polyester 50D Stretch Lamminated TPU</option>';

                                                echo '<option value="Polyester 75D Velours Embossed Lamminated TPU"';   if($value_row['option_value']=='Polyester 75D Velours Embossed Lamminated TPU')   { echo 'selected';} else echo ''; echo '>Polyester 75D Velours Embossed Lamminated TPU</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '238')///------------------------------------------- Материал дна ---------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="Полиэстер SlidesLess 75D (точечное гелеобразное нанесение)"';   if($value_row['option_value']=='Полиэстер SlidesLess 75D (точечное гелеобразное нанесение)')   { echo 'selected';} else echo ''; echo '>Полиэстер SlidesLess 75D (точечное гелеобразное нанесение)</option>';

                                                echo '<option value="Polyester 75D Non-Slip Lamminated TPU"';   if($value_row['option_value']=='Polyester 75D Non-Slip Lamminated TPU')   { echo 'selected';} else echo ''; echo '>Polyester 75D Non-Slip Lamminated TPU</option>';
                                                echo '<option value="Терпаулинг"';   if($value_row['option_value']=='Терпаулинг')   { echo 'selected';} else echo ''; echo '>Терпаулинг</option>';


                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '7')///-------------------------------------------внешний тент ---------------------------------------------

                                            {

                                                //onchange="add_from_select(option_value_'.$option_row["optionID"].')"

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';



                                                echo '<option value="100% полиэстер 75D190T 7000 мм в. ст., проклеенные швы" ';

                                                if($value_row['option_value']=='100% полиэстер 75D190T 7000 мм в. ст., проклеенные швы')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% полиэстер 75D190T 7000 мм в. ст., проклеенные швы</option>';




                                                echo '<option value="100% Polyester RipStop, DryTech покрытие, 7000 мм, проклеенные швы" ';

                                                if($value_row['option_value']=='100% Polyester RipStop, DryTech покрытие, 7000 мм, проклеенные швы')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% Polyester RipStop, DryTech покрытие, 7000 мм, проклеенные швы</option>';



                                                echo '<option value="4000 мм H2O Polyester 190T PU"';

                                                if($value_row['option_value']=='4000 мм H2O Polyester 190T PU')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>4000 мм H2O Polyester 190T PU</option>';

                                                echo '<option value="40D/240Т нейлон Rip Stop 3000мм H2O силиконизированный огнеупорная пропитка швы проклеены" ';

                                                if($value_row['option_value']=='40D/240Т нейлон Rip Stop 3000мм H2O силиконизированный огнеупорная пропитка швы проклеены')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>40D/240Т нейлон Rip Stop 3000мм H2O силиконизированный огнеупорная пропитка швы проклеены</option>';

                                                echo '<option value="Polyester, покрытие PU на 3 000 мм Н2О, SL, проклеенные швы"';

                                                if($value_row['option_value']=='Polyester, покрытие PU на 3 000 мм Н2О, SL, проклеенные швы')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>Polyester, покрытие PU на 3 000 мм Н2О, SL, проклеенные швы</option>';

                                                echo '<option value="Polyester, покрытие PU на 4000 мм Н2О, силиконизирован"';

                                                if($value_row['option_value']=='Polyester, покрытие PU на 4000 мм Н2О, силиконизирован')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>Polyester, покрытие PU на 4000 мм Н2О, силиконизирован</option>';

                                                echo '<option value="Polyester, покрытие PU на 3000 мм Н2О"';

                                                if($value_row['option_value']=='Polyester, покрытие PU на 3000 мм Н2О')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>Polyester, покрытие PU на 3000 мм Н2О</option>';

                                                echo '<option value="Polyester, PU (внутренняя поверхность покрыта полиуретаном), 2500 мм H2O"';

                                                if($value_row['option_value']=='Polyester, PU (внутренняя поверхность покрыта полиуретаном), 2500 мм H2O')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>Polyester, PU (внутренняя поверхность покрыта полиуретаном), 2500 мм H2O</option>';

                                                echo '<option value="100% Polyester RipStop, DryTech покрытие, 2000 мм, проклеенные швы"';

                                                if($value_row['option_value']=='100% Polyester RipStop, DryTech покрытие, 2000 мм, проклеенные швы')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% Polyester RipStop, DryTech покрытие, 2000 мм, проклеенные швы</option>';

                                                echo '<option value="100% Polyester Diamond graphic, DryTech покрытие, 3000 мм, проклеенные швы"';

                                                if($value_row['option_value']=='100% Polyester Diamond graphic, DryTech покрытие, 3000 мм, проклеенные швы')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% Polyester Diamond graphic, DryTech покрытие, 3000 мм, проклеенные швы</option>';

                                                echo '<option value="100% Polyester Diamond graphic, DryTech покрытие, 1000 мм, проклеенные швы"';

                                                if($value_row['option_value']=='100% Polyester Diamond graphic, DryTech покрытие, 1000 мм, проклеенные швы')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% Polyester Diamond graphic, DryTech покрытие, 1000 мм, проклеенные швы</option>';

                                                echo '<option value="100% Polyester, DryTech покрытие, 3000 мм, проклеенные швы"';

                                                if($value_row['option_value']=='100% Polyester, DryTech покрытие, 3000 мм, проклеенные швы')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% Polyester, DryTech покрытие, 3000 мм, проклеенные швы</option>';

                                                echo '<option value="100% Polyester, DryTech покрытие, 2000 мм, проклеенные швы"';

                                                if($value_row['option_value']=='100% Polyester, DryTech покрытие, 2000 мм, проклеенные швы')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% Polyester, DryTech покрытие, 2000 мм, проклеенные швы</option>';

                                                echo '<option value="100% полиэстер Dry Tech покрытие,  2000 мм в. ст."';

                                                if($value_row['option_value']=='100% полиэстер Dry Tech покрытие,  2000 мм в. ст.')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% полиэстер Dry Tech покрытие,  2000 мм в. ст.</option>';

                                                echo '<option value="100% полиэстер 75D/190T WR PU 3000 мм в. ст."';

                                                if($value_row['option_value']=='100% полиэстер 75D/190T WR PU 3000 мм в. ст.')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% полиэстер 75D/190T WR PU 3000 мм в. ст.</option>';

                                                echo '<option value="100% полиэстер 75D/190T WR PU 2000 мм в. ст."';

                                                if($value_row['option_value']=='100% полиэстер 75D/190T WR PU 2000 мм в. ст.')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% полиэстер 75D/190T WR PU 2000 мм в. ст.</option>';

                                                echo '<option value="Polyester 400D PU 2000"';

                                                if($value_row['option_value']=='Polyester 400D PU 2000')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>Polyester 400D PU 2000</option>';

                                                echo '<option value="100% полиэстер 75D 1500 мм в. ст."';

                                                if($value_row['option_value']=='100% полиэстер 75D 1500 мм в. ст.')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% полиэстер 75D 1500 мм в. ст.</option>';

                                                echo '<option value="100% полиэстер 75D/190T WR PU 1000 мм в. ст."';

                                                if($value_row['option_value']=='100% полиэстер 75D/190T WR PU 1000 мм в. ст.')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% полиэстер 75D/190T WR PU 1000 мм в. ст.</option>';

                                                echo '<option value="100% полиэстер 3000 мм в. ст."';

                                                if($value_row['option_value']=='100% полиэстер 3000 мм в. ст.')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% полиэстер 3000 мм в. ст.</option>';

                                                echo '<option value="100% полиэстер 75D/190T RipStop PU 5000мм в. ст."';

                                                if($value_row['option_value']=='100% полиэстер 75D/190T RipStop PU 5000мм в. ст.')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% полиэстер 75D/190T RipStop PU 5000мм в. ст.</option>';

                                                echo '<option value="100% полиэстер 75D/190T Dry Tech  PU 4000мм в. ст."';

                                                if($value_row['option_value']=='100% полиэстер 75D/190T Dry Tech  PU 4000мм в. ст.')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% полиэстер 75D/190T Dry Tech  PU 4000мм в. ст.</option>';

                                                echo '<option value="100% полиэстер 75D/190T Diamond RipStop PU, 8000 мм в. ст."';

                                                if($value_row['option_value']=='100% полиэстер 75D/190T Diamond RipStop PU, 8000 мм в. ст.')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% полиэстер 75D/190T Diamond RipStop PU, 8000 мм в. ст.</option>';

                                                echo '<option value="100% полиэстер 75D/190T Diamond RipStop WR PU 4000 мм в. ст."';

                                                if($value_row['option_value']=='100% полиэстер 75D/190T Diamond RipStop WR PU 4000 мм в. ст.')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>100% полиэстер 75D/190T Diamond RipStop WR PU 4000 мм в. ст.</option>';

                                                echo '<option value="185T полиэстер Rip Stop 3000мм H2O швы проклеены"';

                                                if($value_row['option_value']=='185T полиэстер Rip Stop 3000мм H2O швы проклеены')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>185T полиэстер Rip Stop 3000мм H2O швы проклеены</option>';

                                                echo '<option value="185T полиэстер Rip Stop 3000мм H2O огнеупорная пропитка швы проклеены"';

                                                if($value_row['option_value']=='185T полиэстер Rip Stop 3000мм H2O огнеупорная пропитка швы проклеены')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>185T полиэстер Rip Stop 3000мм H2O огнеупорная пропитка швы проклеены</option>';

                                                echo '<option value="185T полиэстер Rip Stop 4000мм H2O огнеупорная пропитка швы проклеены"';

                                                if($value_row['option_value']=='185T полиэстер Rip Stop 4000мм H2O огнеупорная пропитка швы проклеены')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>185T полиэстер Rip Stop 4000мм H2O огнеупорная пропитка швы проклеены</option>';

                                                echo '<option value="185T полиэстер Rip Stop 6000мм H2O огнеупорная пропитка швы проклеены"';

                                                if($value_row['option_value']=='185T полиэстер Rip Stop 6000мм H2O огнеупорная пропитка швы проклеены')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>185T полиэстер Rip Stop 6000мм H2O огнеупорная пропитка швы проклеены</option>';

                                                echo '<option value="40D/240Т нейлон DOUBLE Rip Stop 6000мм H2O силиконизированный огнеупорная пропитка швы проклеены"';

                                                if($value_row['option_value']=='40D/240Т нейлон DOUBLE Rip Stop 6000мм H2O силиконизированный огнеупорная пропитка швы проклеены')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>40D/240Т нейлон DOUBLE Rip Stop 6000мм H2O силиконизированный огнеупорная пропитка швы проклеены</option>';

                                                echo '<option value="185T полиэстер 3000мм H2O огнеупорная пропитка швы проклеены"';

                                                if($value_row['option_value']=='185T полиэстер 3000мм H2O огнеупорная пропитка швы проклеены')

                                                { echo 'selected';}

                                                else echo '';

                                                echo '>185T полиэстер 3000мм H2O огнеупорная пропитка швы проклеены</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '14')///-------------------------------------------Цвет внешнего тента---------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="хаки, песочный"';   if($value_row['option_value']=='хаки, песочный')   { echo 'selected';} else echo ''; echo '>хаки, песочный</option>';

                                                echo '<option value="синий"';   if($value_row['option_value']=='синий')   { echo 'selected';} else echo ''; echo '>синий</option>';

                                                echo '<option value="красный"';   if($value_row['option_value']=='красный')   { echo 'selected';} else echo ''; echo '>красный</option>';

                                                echo '<option value="зеленый"';   if($value_row['option_value']=='зеленый')   { echo 'selected';} else echo ''; echo '>зеленый</option>';

                                                echo '<option value="зеленый, оранжевый"';   if($value_row['option_value']=='зеленый, оранжевый')   { echo 'selected';} else echo ''; echo '>зеленый, оранжевый</option>';

                                                echo '<option value="желто-черный"';   if($value_row['option_value']=='желто-черный')   { echo 'selected';} else echo ''; echo '>желто-черный</option>';

                                                echo '<option value="оранжевый"';   if($value_row['option_value']=='оранжевый')   { echo 'selected';} else echo ''; echo '>оранжевый</option>';

                                                echo '<option value="светло-зеленый"';   if($value_row['option_value']=='светло-зеленый')   { echo 'selected';} else echo ''; echo '>светло-зеленый</option>';

                                                echo '<option value="темно-зеленый"';   if($value_row['option_value']=='темно-зеленый')   { echo 'selected';} else echo ''; echo '>темно-зеленый</option>';

                                                echo '<option value="песочный"';   if($value_row['option_value']=='песочный')   { echo 'selected';} else echo ''; echo '>песочный</option>';

                                                echo '<option value="серый"';   if($value_row['option_value']=='серый')   { echo 'selected';} else echo ''; echo '>серый</option>';

                                                echo '<option value="камуфляжный"';   if($value_row['option_value']=='камуфляжный')   { echo 'selected';} else echo ''; echo '>камуфляжный</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '8')///-------------------------------------------Материал внутренней комнаты:---------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="100% Nylon RipStop – дышащий"';   if($value_row['option_value']=='100% Nylon RipStop – дышащий')   { echo 'selected';} else echo ''; echo '>100% Nylon RipStop – дышащий</option>';

                                                echo '<option value="20D/240T полиэстер Rip Stop (дышащий) водоотталкивающая пропитка"';   if($value_row['option_value']=='20D/240T полиэстер Rip Stop (дышащий) водоотталкивающая пропитка')   { echo 'selected';} else echo ''; echo '>20D/240T полиэстер Rip Stop (дышащий) водоотталкивающая пропитка</option>';

                                                echo '<option value="нейлон воздухопроницаемый Ripstop"';   if($value_row['option_value']=='нейлон воздухопроницаемый Ripstop')   { echo 'selected';} else echo ''; echo '>нейлон воздухопроницаемый Ripstop</option>';

                                                echo '<option value="нейлон воздухопроницаемый"';   if($value_row['option_value']=='нейлон воздухопроницаемый')   { echo 'selected';} else echo ''; echo '>нейлон воздухопроницаемый</option>';

                                                echo '<option value="100% дышащий полиэстер RipStop"';   if($value_row['option_value']=='100% дышащий полиэстер RipStop')   { echo 'selected';} else echo ''; echo '>100% дышащий полиэстер RipStop</option>';

                                                echo '<option value="100% дышащий Nylon"';   if($value_row['option_value']=='100% дышащий Nylon')   { echo 'selected';} else echo ''; echo '>100% дышащий Nylon</option>';

                                                echo '<option value="100% дышащий полиэстер 68D/68D 190T"';   if($value_row['option_value']=='100% дышащий полиэстер 68D/68D 190T')   { echo 'selected';} else echo ''; echo '>100% дышащий полиэстер 68D/68D 190T1</option>';

                                                echo '<option value="100% дышащий полиэстер 70D/190W/R"';   if($value_row['option_value']=='100% дышащий полиэстер 70D/190W/R')   { echo 'selected';} else echo ''; echo '>100% дышащий полиэстер 70D/190W/R</option>';

                                                echo '<option value="100% дышащий полиэстер"';   if($value_row['option_value']=='100% дышащий полиэстер')   { echo 'selected';} else echo ''; echo '>100% дышащий полиэстер</option>';

                                                echo '<option value="210T полиэстер Rip Stop (дышащий) водоотталкивающая пропитка"';   if($value_row['option_value']=='210T полиэстер Rip Stop (дышащий) водоотталкивающая пропитка')   { echo 'selected';} else echo ''; echo '>210T полиэстер Rip Stop (дышащий) водоотталкивающая пропитка</option>';

                                                echo '<option value="190T полиэстер дышащий водоотталкивающая пропитка"';   if($value_row['option_value']=='190T полиэстер дышащий водоотталкивающая пропитка')   { echo 'selected';} else echo ''; echo '>190T полиэстер дышащий водоотталкивающая пропитка</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '34')///-------------------------------------------Цвет внутренней палатки---------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="желтый"';   if($value_row['option_value']=='желтый')   { echo 'selected';} else echo ''; echo '>желтый</option>';

                                                echo '<option value="оранжевый"';   if($value_row['option_value']=='оранжевый')   { echo 'selected';} else echo ''; echo '>оранжевый</option>';

                                                echo '<option value="белый"';   if($value_row['option_value']=='белый')   { echo 'selected';} else echo ''; echo '>белый</option>';

                                                echo '<option value="серый"';   if($value_row['option_value']=='серый')   { echo 'selected';} else echo ''; echo '>серый</option>';

                                                echo '<option value="бежевый"';   if($value_row['option_value']=='бежевый')   { echo 'selected';} else echo ''; echo '>бежевый</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '9')///-------------------------------------------Пол: ---------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="150D Polyester Oxford 5000 мм"';   if($value_row['option_value']=='150D Polyester Oxford 5000 мм')   { echo 'selected';} else echo ''; echo '>150D Polyester Oxford 5000 мм</option>';

                                                echo '<option value="6000мм H2O Polyester 150D Oxford PU"';   if($value_row['option_value']=='6000мм H2O Polyester 150D Oxford PU')   { echo 'selected';} else echo ''; echo '>6000мм H2O Polyester 150D Oxford PU</option>';

                                                echo '<option value="4000мм H2O Polyethylene"';   if($value_row['option_value']=='4000мм H2O Polyethylene')   { echo 'selected';} else echo ''; echo '>4000мм H2O Polyethylene</option>';

                                                echo '<option value="100% нейлон Dry Tech, 5000 мм"';   if($value_row['option_value']=='100% нейлон Dry Tech, 5000 мм')   { echo 'selected';} else echo ''; echo '>100% нейлон Dry Tech, 5000 мм</option>';

                                                echo '<option value="100% Nylon, DryTech покрытие, 7000 мм, проклеенные швы"';   if($value_row['option_value']=='100% Nylon, DryTech покрытие, 7000 мм, проклеенные швы')   { echo 'selected';} else echo ''; echo '>100% Nylon, DryTech покрытие, 7000 мм, проклеенные швы</option>';

                                                echo '<option value="100% Polyester Oxford, 5000 мм"';   if($value_row['option_value']=='100% Polyester Oxford, 5000 мм')   { echo 'selected';} else echo ''; echo '>100% Polyester Oxford, 5000 мм</option>';

                                                echo '<option value="100% полиэстер 75D190T RipStop, PU 5000 мм в.ст."';   if($value_row['option_value']=='100% полиэстер 75D190T RipStop, PU 5000 мм в.ст.')   { echo 'selected';} else echo ''; echo '>100% полиэстер 75D190T RipStop, PU 5000 мм в.ст.</option>';

                                                echo '<option value="40D/240T нейлон Rip Stop 10000мм H2O"';   if($value_row['option_value']=='40D/240T нейлон Rip Stop 10000мм H2O')   { echo 'selected';} else echo ''; echo '>40D/240T нейлон Rip Stop 10000мм H2O</option>';

                                                echo '<option value="нейлон PU на 10 000 мм Н2О"';   if($value_row['option_value']=='нейлон PU на 10 000 мм Н2О')   { echo 'selected';} else echo ''; echo '>нейлон PU на 10 000 мм Н2О</option>';

                                                echo '<option value="нейлон PU на 10 000 мм Н2О, проклеенные швы"';   if($value_row['option_value']=='нейлон PU на 10 000 мм Н2О, проклеенные швы')   { echo 'selected';} else echo ''; echo '>нейлон PU на 10 000 мм Н2О, проклеенные швы</option>';

                                                echo '<option value="нейлон PU на 7 000 мм Н2О, проклеенные швы"';   if($value_row['option_value']=='нейлон PU на 7 000 мм Н2О, проклеенные швы')   { echo 'selected';} else echo ''; echo '>нейлон PU на 7 000 мм Н2О, проклеенные швы</option>';

                                                echo '<option value="100% полиэстер 75D/190T 10000 мм в. ст."';   if($value_row['option_value']=='100% полиэстер 75D/190T 10000 мм в. ст.')   { echo 'selected';} else echo ''; echo '>100% полиэстер 75D/190T 10000 мм в. ст.</option>';

                                                echo '<option value="100% полиэстр 75D/190T 7000 мм в. ст."';   if($value_row['option_value']=='100% полиэстр 75D/190T 7000 мм в. ст.')   { echo 'selected';} else echo ''; echo '>100% полиэстр 75D/190T 7000 мм в. ст.</option>';

                                                echo '<option value="100% полиэстр 75D/190T 7000 мм в. ст. + дополнительное дно 7000 мм в. ст."';   if($value_row['option_value']=='100% полиэстр 75D/190T 7000 мм в. ст. + дополнительное дно 7000 мм в. ст.')   { echo 'selected';} else echo ''; echo '>100% полиэстр 75D/190T 7000 мм в. ст. + дополнительное дно 7000 мм в. ст.</option>';

                                                echo '<option value="армированный полиэтилен (терпаулинг)"';   if($value_row['option_value']=='армированный полиэтилен (терпаулинг)')   { echo 'selected';} else echo ''; echo '>армированный полиэтилен (терпаулинг)</option>';

                                                echo '<option value="армированный полиэтилен (терпаулинг) 120г"';   if($value_row['option_value']=='армированный полиэтилен (терпаулинг) 120г')   { echo 'selected';} else echo ''; echo '>армированный полиэтилен (терпаулинг) 120г</option>';

                                                echo '<option value="100% полиэстер Diamond RipStop 75D/190T WR PU 6000мм в. ст."';   if($value_row['option_value']=='100% полиэстер Diamond RipStop 75D/190T WR PU 6000мм в. ст.')   { echo 'selected';} else echo ''; echo '>100% полиэстер Diamond RipStop 75D/190T WR PU 6000мм в. ст.</option>';

                                                echo '<option value="100% полиэстер 75D/190T 5000 мм в. ст."';   if($value_row['option_value']=='100% полиэстер 75D/190T 5000 мм в. ст.')   { echo 'selected';} else echo ''; echo '>100% полиэстер 75D/190T 5000 мм в. ст.</option>';

                                                echo '<option value="190T нейлон 5000мм H2O"';   if($value_row['option_value']=='190T нейлон 5000мм H2O')   { echo 'selected';} else echo ''; echo '>190T нейлон 5000мм H2O</option>';

                                                echo '<option value="190T нейлон 8000мм H2O"';   if($value_row['option_value']=='190T нейлон 8000мм H2O')   { echo 'selected';} else echo ''; echo '>190T нейлон 8000мм H2O</option>';

                                                echo '<option value="190T нейлон 10000мм H2O"';   if($value_row['option_value']=='190T нейлон 10000мм H2O')   { echo 'selected';} else echo ''; echo '>190T нейлон 10000мм H2O</option>';

                                                echo '<option value="многослойный полиэтилен"';   if($value_row['option_value']=='многослойный полиэтилен')   { echo 'selected';} else echo ''; echo '>многослойный полиэтилен</option>';

                                                echo '<option value="ламинированный полиэтилен"';   if($value_row['option_value']=='ламинированный полиэтилен')   { echo 'selected';} else echo ''; echo '>ламинированный полиэтилен</option>';

                                                echo '<option value="ламинированный полиэтилен или 150D Polyester OXFORD 5000mm H2O"';   if($value_row['option_value']=='ламинированный полиэтилен или 150D Polyester OXFORD 5000mm H2O')   { echo 'selected';} else echo ''; echo '>ламинированный полиэтилен или 150D Polyester OXFORD 5000mm H2O</option>';

                                                echo '<option value="150D полиэстер OXFORD, 5000мм H2O огнеупорная пропитка швы проклеены"';   if($value_row['option_value']=='150D полиэстер OXFORD, 5000мм H2O огнеупорная пропитка швы проклеены')   { echo 'selected';} else echo ''; echo '>150D полиэстер OXFORD, 5000мм H2O огнеупорная пропитка швы проклеены</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '19')///-------------------------------------------Каркас---------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="фибергласс"';   if($value_row['option_value']=='фибергласс')   { echo 'selected';} else echo ''; echo '>фибергласс</option>';

                                                echo '<option value="металл D20x0,8"';   if($value_row['option_value']=='металл D20x0,8')   { echo 'selected';} else echo ''; echo '>металл D20x0,8</option>';

                                                echo '<option value="дюралюминий 7,9 мм"';   if($value_row['option_value']=='дюралюминий 7,9 мм')   { echo 'selected';} else echo ''; echo '>дюралюминий 7,9 мм</option>';

                                                echo '<option value="durawrap  7,9 мм"';   if($value_row['option_value']=='durawrap  7,9 мм')   { echo 'selected';} else echo ''; echo '>durawrap  7,9 мм</option>';

                                                echo '<option value="durawrap 9,5/11 мм"';   if($value_row['option_value']=='durawrap 9,5/11 мм')   { echo 'selected';} else echo ''; echo '>durawrap 9,5/11 мм</option>';

                                                echo '<option value="durawrap 8,5/9,5 мм"';   if($value_row['option_value']=='durawrap 8,5/9,5 мм')   { echo 'selected';} else echo ''; echo '>durawrap 8,5/9,5 мм</option>';

                                                echo '<option value="durawrap  7,9/8,5 мм"';   if($value_row['option_value']=='durawrap  7,9/8,5 мм')   { echo 'selected';} else echo ''; echo '>durawrap  7,9/8,5 мм</option>';

                                                echo '<option value="durawrap  11 мм"';   if($value_row['option_value']=='durawrap  11 мм')   { echo 'selected';} else echo ''; echo '>durawrap  11 мм</option>';

                                                echo '<option value="дюралюминий 9,6 мм"';   if($value_row['option_value']=='дюралюминий 9,6 мм')   { echo 'selected';} else echo ''; echo '>дюралюминий 9,6 мм</option>';

                                                echo '<option value="фибергласс  7,9/8,5 мм"';   if($value_row['option_value']=='фибергласс  7,9/8,5 мм')   { echo 'selected';} else echo ''; echo '>фибергласс  7,9/8,5 мм</option>';

                                                echo '<option value="алюминий 8,5 мм"';   if($value_row['option_value']=='алюминий 8,5 мм')   { echo 'selected';} else echo ''; echo '>алюминий 8,5 мм</option>';

                                                echo '<option value="дюрапол 8.5 мм"';   if($value_row['option_value']=='дюрапол 8.5 мм')   { echo 'selected';} else echo ''; echo '>дюрапол 8.5 мм</option>';

                                                echo '<option value="дюрапол 8.5 мм/сталь 16мм"';   if($value_row['option_value']=='дюрапол 8.5 мм/сталь 16мм')   { echo 'selected';} else echo ''; echo '>дюрапол 8.5 мм/сталь 16мм</option>';

                                                echo '<option value="дюрапол 9.5 мм"';   if($value_row['option_value']=='дюрапол 9.5 мм')   { echo 'selected';} else echo ''; echo '>дюрапол 9.5 мм</option>';

                                                echo '<option value="дюрапол 9.5 мм/сталь 16мм"';   if($value_row['option_value']=='дюрапол 9.5 мм/сталь 16мм')   { echo 'selected';} else echo ''; echo '>дюрапол 9.5 мм/сталь 16мм</option>';

                                                echo '<option value="дюрапол 11 мм"';   if($value_row['option_value']=='дюрапол 11 мм')   { echo 'selected';} else echo ''; echo '>дюрапол 11 мм</option>';

                                                echo '<option value="дюрапол 13 мм"';   if($value_row['option_value']=='дюрапол 13 мм')   { echo 'selected';} else echo ''; echo '>дюрапол 13 мм</option>';

                                                echo '<option value="дюрапол 11мм + сталь 16 мм"';   if($value_row['option_value']=='дюрапол 11мм + сталь 16 мм')   { echo 'selected';} else echo ''; echo '>дюрапол 11мм + сталь 16 мм</option>';

                                                echo '<option value="алюминий 11мм + сталь 16 мм"';   if($value_row['option_value']=='алюминий 11мм + сталь 16 мм')   { echo 'selected';} else echo ''; echo '>алюминий 11мм + сталь 16 мм</option>';

                                                echo '<option value="фибергласс 9,5/11 + сталь 16 мм"';   if($value_row['option_value']=='фибергласс 9,5/11 + сталь 16 мм')   { echo 'selected';} else echo ''; echo '>фибергласс 9,5/11 + сталь 16 мм</option>';

                                                echo '<option value="фибергласс 9,5/11"';   if($value_row['option_value']=='фибергласс 9,5/11')   { echo 'selected';} else echo ''; echo '>фибергласс 9,5/11</option>';

                                                echo '<option value="дюрапол 9,5/11 + сталь 16 мм"';   if($value_row['option_value']=='дюрапол 9,5/11 + сталь 16 мм')   { echo 'selected';} else echo ''; echo '>дюрапол 9,5/11 + сталь 16 мм</option>';

                                                echo '<option value="фибергласс 7.9 мм"';   if($value_row['option_value']=='фибергласс 7.9 мм')   { echo 'selected';} else echo ''; echo '>фибергласс 7.9 мм</option>';

                                                echo '<option value="фибергласс 8.5 мм"';   if($value_row['option_value']=='фибергласс 8.5 мм')   { echo 'selected';} else echo ''; echo '>фибергласс 8.5 мм</option>';

                                                echo '<option value="Flash Touch System Fiberglass 8,5 мм"';   if($value_row['option_value']=='Flash Touch System Fiberglass 8,5 мм')   { echo 'selected';} else echo ''; echo '>Flash Touch System Fiberglass 8,5 мм</option>';

                                                echo '<option value="фибергласс 9.5 мм"';   if($value_row['option_value']=='фибергласс 9.5 мм')   { echo 'selected';} else echo ''; echo '>фибергласс 9.5 мм</option>';

                                                echo '<option value="фибергласс 7.9 и 8.5 мм"';   if($value_row['option_value']=='фибергласс 7.9 и 8.5 мм')   { echo 'selected';} else echo ''; echo '>фибергласс 7.9 и 8.5 мм</option>';

                                                echo '<option value="фибергласс 11 мм"';   if($value_row['option_value']=='фибергласс 11 мм')   { echo 'selected';} else echo ''; echo '>фибергласс 11 мм</option>';

                                                echo '<option value="ламинированный фиберглас 8,5мм"';   if($value_row['option_value']=='ламинированный фиберглас 8,5мм')   { echo 'selected';} else echo ''; echo '>ламинированный фиберглас 8,5мм</option>';

                                                echo '<option value="дюралюминий 8мм"';   if($value_row['option_value']=='дюралюминий 8мм')   { echo 'selected';} else echo ''; echo '>дюралюминий 8мм</option>';

                                                echo '<option value="дюралюминий 7001-T6 8,5мм"';   if($value_row['option_value']=='дюралюминий 7001-T6 8,5мм')   { echo 'selected';} else echo ''; echo '>дюралюминий 7001-T6 8,5мм</option>';

                                                echo '<option value="дюралюминий 7001-Т6 8,5/9,5 мм"';   if($value_row['option_value']=='дюралюминий 7001-Т6 8,5/9,5 мм')   { echo 'selected';} else echo ''; echo '>дюралюминий 7001-Т6 8,5/9,5 мм</option>';

                                                echo '<option value="дюралюминий 7001-Т6 9,5 мм"';   if($value_row['option_value']=='дюралюминий 7001-Т6 9,5 мм')   { echo 'selected';} else echo ''; echo '>дюралюминий 7001-Т6 9,5 мм</option>';

                                                echo '<option value="ламинированный фиберглас 8,5мм/9,5мм"';   if($value_row['option_value']=='ламинированный фиберглас 8,5мм/9,5мм')   { echo 'selected';} else echo ''; echo '>ламинированный фиберглас 8,5мм/9,5мм</option>';

                                                echo '<option value="ламинированный фиберглас 11,5мм"';   if($value_row['option_value']=='ламинированный фиберглас 11,5мм')   { echo 'selected';} else echo ''; echo '>ламинированный фиберглас 11,5мм</option>';

                                                echo '<option value="сталь 16мм + фибергласс 9.5мм"';   if($value_row['option_value']=='сталь 16мм + фибергласс 9.5мм')   { echo 'selected';} else echo ''; echo '>сталь 16мм + фибергласс 9.5мм</option>';

                                                echo '<option value="сталь 16мм"';   if($value_row['option_value']=='сталь 16мм')   { echo 'selected';} else echo ''; echo '>сталь 16мм</option>';

                                                echo '<option value="сталь 19мм"';   if($value_row['option_value']=='сталь 19мм')   { echo 'selected';} else echo ''; echo '>сталь 19мм</option>';

                                                echo '<option value="ламинированный фиберглас 11,0/12,5мм"';   if($value_row['option_value']=='ламинированный фиберглас 11,0/12,5мм')   { echo 'selected';} else echo ''; echo '>ламинированный фиберглас 11,0/12,5мм</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '2')///-------------------------------------------Количество входов---------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="1"';   if($value_row['option_value']=='1')   { echo 'selected';} else echo ''; echo '>1</option>';

                                                echo '<option value="2"';   if($value_row['option_value']=='2')   { echo 'selected';} else echo ''; echo '>2</option>';

                                                echo '<option value="3"';   if($value_row['option_value']=='3')   { echo 'selected';} else echo ''; echo '>3</option>';

                                                echo '<option value="4"';   if($value_row['option_value']=='4')   { echo 'selected';} else echo ''; echo '>4</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '5')///-------------------------------------------Количество дуг (штоков)--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="1"';   if($value_row['option_value']=='1')   { echo 'selected';} else echo ''; echo '>1</option>';

                                                echo '<option value="2"';   if($value_row['option_value']=='2')   { echo 'selected';} else echo ''; echo '>2</option>';

                                                echo '<option value="3"';   if($value_row['option_value']=='3')   { echo 'selected';} else echo ''; echo '>3</option>';

                                                echo '<option value="4"';   if($value_row['option_value']=='4')   { echo 'selected';} else echo ''; echo '>4</option>';

                                                echo '<option value="5"';   if($value_row['option_value']=='5')   { echo 'selected';} else echo ''; echo '>5</option>';

                                                echo '<option value="6"';   if($value_row['option_value']=='6')   { echo 'selected';} else echo ''; echo '>6</option>';

                                                echo '<option value="7"';   if($value_row['option_value']=='7')   { echo 'selected';} else echo ''; echo '>7</option>';

                                                echo '<option value="8"';   if($value_row['option_value']=='8')   { echo 'selected';} else echo ''; echo '>8</option>';

                                                echo '<option value="9"';   if($value_row['option_value']=='9')   { echo 'selected';} else echo ''; echo '>9</option>';

                                                echo '<option value="10"';   if($value_row['option_value']=='10')   { echo 'selected';} else echo ''; echo '>10</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '6')///-------------------------------------------Фурнитура--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="Nifco"';   if($value_row['option_value']=='Nifco')   { echo 'selected';} else echo ''; echo '>Nifco</option>';

                                                echo '<option value="YKK"';   if($value_row['option_value']=='YKK')   { echo 'selected';} else echo ''; echo '>YKK</option>';

                                                echo '<option value="Nexus"';   if($value_row['option_value']=='Nexus')   { echo 'selected';} else echo ''; echo '>Nexus</option>';

                                                echo '<option value="Nexus, Equip"';   if($value_row['option_value']=='Nexus, Equip')   { echo 'selected';} else echo ''; echo '>Nexus, Equip</option>';

                                                echo '<option value="DURAFLEX"';   if($value_row['option_value']=='DURAFLEX')   { echo 'selected';} else echo ''; echo '>DURAFLEX</option>';

                                                echo '<option value="молния YKK  с двумя двухсторонними замками"';   if($value_row['option_value']=='молния YKK  с двумя двухсторонними замками')   { echo 'selected';} else echo ''; echo '>молния YKK  с двумя двухсторонними замками</option>';

                                                echo '<option value="молния с двумя двухсторонними замками"';   if($value_row['option_value']=='молния с двумя двухсторонними замками')   { echo 'selected';} else echo ''; echo '>молния с двумя двухсторонними замками</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '37')///-------------------------------------------Защитная юбка--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            /*elseif ($option_row["optionID"] == '19')///-------------------------------------------Каркас палатки---------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                            $str_checked = "checked";

                                                else

                                                            $str_checked = "";

                                                echo '<tr>

                                                            <td>&nbsp;</td>

                                                            <td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

                                                                        onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

                                                            '.$str_checked.'> </td>

                                                            <td>

                                                                <select name=option_value_'.$option_row["optionID"].'>';

                                                                    echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="металл"';   if($value_row['option_value']=='металл')   { echo 'selected';} else echo ''; echo '>металл</option>';

                                                echo '<option value="пластик"';   if($value_row['option_value']=='пластик')   { echo 'selected';} else echo ''; echo '>пластик</option>';

                                                echo '			</select>

                                                            </td>

                                                      </tr>';

                                            }*/

                                            elseif ($option_row["optionID"] == '20')///-------------------------------------------Класс рюкзака---------------------------------------------

                                            {

                                                $class_arr = GetProductClassRukzak();

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name=option_value_'.$option_row["optionID"].'>';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                for($i=0; $i<count($class_arr);$i++)

                                                {

                                                    echo '<option value="'.$class_arr[$i]['name'].'"'; if($value_row['option_value']==$class_arr[$i]['name'])

                                                { echo 'selected';} else echo ''; echo '>'.$class_arr[$i]['name'].'</option>';

                                                }

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '22')///-------------------------------------------Каркас палатки---------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name=option_value_'.$option_row["optionID"].'>';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="спина"';   if($value_row['option_value']=='спина')   { echo 'selected';} else echo ''; echo '>спина</option>';

                                                echo '<option value="рама"';   if($value_row['option_value']=='рама')   { echo 'selected';} else echo ''; echo '>рама</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '46')///-------------------------------------------Защитная юбка--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '47')///-------------------------------------------Наполнитель--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="PolyPlus Light"';   if($value_row['option_value']=='PolyPlus Light')   { echo 'selected';} else echo ''; echo '>PolyPlus Light</option>';

                                                echo '<option value="Thermolite Extra"';   if($value_row['option_value']=='Thermolite Extra')   { echo 'selected';} else echo ''; echo '>Thermolite Extra</option>';

                                                echo '<option value="Primaloft Sport"';   if($value_row['option_value']=='Primaloft Sport')   { echo 'selected';} else echo ''; echo '>Primaloft Sport</option>';
                                                echo '<option value="Hollowfiber"';   if($value_row['option_value']=='Hollowfiber')   { echo 'selected';} else echo ''; echo '>Hollowfiber</option>';

                                                echo '<option value="пенополиуретан"';   if($value_row['option_value']=='пенополиуретан')   { echo 'selected';} else echo ''; echo '>пенополиуретан</option>';

                                                echo '<option value="вспененный полиуретан 16кг/м3"';   if($value_row['option_value']=='вспененный полиуретан 16кг/м3')   { echo 'selected';} else echo ''; echo '>вспененный полиуретан 16кг/м3</option>';

                                                echo '<option value="полиэстеровые волокна, HollowCore"';   if($value_row['option_value']=='полиэстеровые волокна, HollowCore')   { echo 'selected';} else echo ''; echo '>полиэстеровые волокна, HollowCore</option>';

                                                echo '<option value="полиэстеровые волокна, HollowCore 7"';   if($value_row['option_value']=='полиэстеровые волокна, HollowCore 7')   { echo 'selected';} else echo ''; echo '>полиэстеровые волокна, HollowCore 7</option>';

                                                echo '<option value="полиэстеровые микроволокна, HollowCore-micro"';   if($value_row['option_value']=='полиэстеровые микроволокна, HollowCore-micro')   { echo 'selected';} else echo ''; echo '>полиэстеровые микроволокна, HollowCore-micro</option>';

                                                echo '<option value="утиный пух, Grey Duck Down 80/20, Fill Power 550"';   if($value_row['option_value']=='утиный пух, Grey Duck Down 80/20, Fill Power 550')   { echo 'selected';} else echo ''; echo '>утиный пух, Grey Duck Down 80/20, Fill Power 550</option>';

                                                echo '<option value="полиэстеровые волокна, ProLite Extrafil Q7"';   if($value_row['option_value']=='полиэстеровые волокна, ProLite Extrafil Q7')   { echo 'selected';} else echo ''; echo '>полиэстеровые волокна, ProLite Extrafil Q7</option>';

                                                echo '<option value="силиконизированный HiTech Holofiber"';   if($value_row['option_value']=='силиконизированный HiTech Holofiber')   { echo 'selected';} else echo ''; echo '>силиконизированный HiTech Holofiber</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '53')///-------------------------------------------Внешний материал--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="Nylon Durafolio Rip"';   if($value_row['option_value']=='Nylon Durafolio Rip')   { echo 'selected';} else echo ''; echo '>Nylon Durafolio Rip</option>';

                                                echo '<option value="Nylon 66 TACTEL Rip Stop, W/R, cire"';   if($value_row['option_value']=='Nylon 66 TACTEL Rip Stop, W/R, cire')   { echo 'selected';} else echo ''; echo '>Nylon 66 TACTEL Rip Stop, W/R, cire</option>';

                                                echo '<option value="40D Nylon Diamond Rip Stop, D/P"';   if($value_row['option_value']=='40D Nylon Diamond Rip Stop, D/P')   { echo 'selected';} else echo ''; echo '>40D Nylon Diamond Rip Stop, D/P</option>';

                                                echo '<option value="210T Polyester Rip Stop, CIRE"';   if($value_row['option_value']=='210T Polyester Rip Stop, CIRE')   { echo 'selected';} else echo ''; echo '>210T Polyester Rip Stop, CIRE</option>';

                                                echo '<option value="210T Polyester Rip Stop, W/R, CIRE"';   if($value_row['option_value']=='210T Polyester Rip Stop, W/R, CIRE')   { echo 'selected';} else echo ''; echo '>210T Polyester Rip Stop, W/R, CIRE</option>';

                                                echo '<option value="190T Polyester, W/R, CIRE"';   if($value_row['option_value']=='190T Polyester, W/R, CIRE')   { echo 'selected';} else echo ''; echo '>190T Polyester, W/R, CIRE</option>';

                                                echo '<option value="polycotton 96*72 T/C"';   if($value_row['option_value']=='Polycotton 96*72 T/C')   { echo 'selected';} else echo ''; echo '>Polycotton 96*72 T/C</option>';

                                                echo '<option value="нейлон 300T/40D Diamond RipStop"';   if($value_row['option_value']=='нейлон 300T/40D Diamond RipStop')   { echo 'selected';} else echo ''; echo '>нейлон 300T/40D Diamond RipStop</option>';

                                                echo '<option value="210T Polyester Rip Stop"';   if($value_row['option_value']=='210T Polyester Rip Stop')   { echo 'selected';} else echo ''; echo '>210T Polyester Rip Stop</option>';

                                                echo '<option value="нейлон 210T RipStop"';   if($value_row['option_value']=='нейлон 21T RipStop')   { echo 'selected';} else echo ''; echo '>нейлон 21T RipStop</option>';
                                                echo '<option value="полиэстер 170T"';   if($value_row['option_value']=='полиэстер 170T')   { echo 'selected';} else echo ''; echo '>полиэстер 170T</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '54')///-------------------------------------------Внутренний материал--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="Nylon Taffeta 210T"';   if($value_row['option_value']=='Nylon Taffeta 210T')   { echo 'selected';} else echo ''; echo '>Nylon Taffeta 210T</option>';

                                                echo '<option value="100% Nylon RipStop – дышащий"';   if($value_row['option_value']=='100% Nylon RipStop – дышащий')   { echo 'selected';} else echo ''; echo '>100% Nylon RipStop – дышащий</option>';

                                                echo '<option value="290T Micro Polyester"';   if($value_row['option_value']=='290T Micro Polyester')   { echo 'selected';} else echo ''; echo '>290T Micro Polyester</option>';

                                                echo '<option value="280T Micro Polyester"';   if($value_row['option_value']=='280T Micro Polyester')   { echo 'selected';} else echo ''; echo '>280T Micro Polyester</option>';

                                                echo '<option value="T/C Fleece"';   if($value_row['option_value']=='T/C Fleece')   { echo 'selected';} else echo ''; echo '>T/C Fleece</option>';

                                                echo '<option value="Flannel 100% cotton"';   if($value_row['option_value']=='Flannel 100% cotton')   { echo 'selected';} else echo ''; echo '>Flannel 100% cotton</option>';

                                                echo '<option value="хлопок"';   if($value_row['option_value']=='хлопок')   { echo 'selected';} else echo ''; echo '>хлопок</option>';

                                                echo '<option value="210T нейлон Taffeta"';   if($value_row['option_value']=='210T нейлон Taffeta')   { echo 'selected';} else echo ''; echo '>210T нейлон Taffeta</option>';

                                                echo '<option value="фланель"';   if($value_row['option_value']=='фланель')   { echo 'selected';} else echo ''; echo '>фланель</option>';
                                                echo '<option value="полиэстер 170Т cire"';   if($value_row['option_value']=='полиэстер 170Т cire')   { echo 'selected';} else echo ''; echo '>полиэстер 170Т cire</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '55')///-------------------------------------------Компрессионная упаковка--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '57')///-------------------------------------------Количество слоев--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="1"';   if($value_row['option_value']=='1')   { echo 'selected';} else echo ''; echo '>1</option>';

                                                echo '<option value="2"';   if($value_row['option_value']=='2')   { echo 'selected';} else echo ''; echo '>2</option>';

                                                echo '<option value="3"';   if($value_row['option_value']=='3')   { echo 'selected';} else echo ''; echo '>3</option>';

                                                echo '<option value="4"';   if($value_row['option_value']=='4')   { echo 'selected';} else echo ''; echo '>4</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '66')///-------------------------------------------Материал--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="Polyester 600D Rip Stop / 1680T FTX TM PD, WR, PU"';   if($value_row['option_value']=='Polyester 600D Rip Stop / 1680T FTX TM PD, WR, PU')   { echo 'selected';} else echo ''; echo '>Polyester 600D Rip Stop / 1680T FTX TM PD, WR, PU</option>';

                                                echo '<option value="Polyester 185T PU 4000 mm H2O"';   if($value_row['option_value']=='Polyester 185T PU 4000 mm H2O')   { echo 'selected';} else echo ''; echo '>Polyester 185T PU 4000 mm H2O</option>';

                                                echo '<option value="Sio-Line 630"';   if($value_row['option_value']=='Sio-Line 630')   { echo 'selected';} else echo ''; echo '>Sio-Line 630</option>';

                                                echo '<option value="100% поліестер 500 D, PU coating, Rip-Stop"';   if($value_row['option_value']=='100% поліестер 500 D, PU coating, Rip-Stop')   { echo 'selected';} else echo ''; echo '>100% поліестер 500 D, PU coating, Rip-Stop</option>';

                                                echo '<option value="100% поліестер 420 D, PU coating, Dobby texture"';   if($value_row['option_value']=='100% поліестер 420 D, PU coating, Dobby texture')   { echo 'selected';} else echo ''; echo '>100% поліестер 420 D, PU coating, Dobby texture</option>';

                                                echo '<option value="100% поліестер 500 D, PU coating, Rip-stop texture"';   if($value_row['option_value']=='100% поліестер 500 D, PU coating, Rip-stop texture')   { echo 'selected';} else echo ''; echo '>100% поліестер 500 D, PU coating, Rip-stop texture</option>';

                                                echo '<option value="Cordura"';   if($value_row['option_value']=='Cordura')   { echo 'selected';} else echo ''; echo '>Cordura</option>';

                                                echo '<option value="Cordura 1000D"';   if($value_row['option_value']=='Cordura 1000D')   { echo 'selected';} else echo ''; echo '>Cordura 1000D</option>';

                                                echo '<option value="600d Nylon"';   if($value_row['option_value']=='600d Nylon')   { echo 'selected';} else echo ''; echo '>600d Nylon</option>';

                                                echo '<option value="600d Nylon R/S"';   if($value_row['option_value']=='600d Nylon R/S')   { echo 'selected';} else echo ''; echo '>600d Nylon R/S</option>';

                                                echo '<option value="Textreme 6.6 / Cross Nylon 420 HD"';   if($value_row['option_value']=='Textreme 6.6 / Cross Nylon 420 HD')   { echo 'selected';} else echo ''; echo '>Textreme 6.6 / Cross Nylon 420 HD</option>';

                                                echo '<option value="450 HD Polyoxford, 420 HD Nylon"';   if($value_row['option_value']=='450 HD Polyoxford, 420 HD Nylon')   { echo 'selected';} else echo ''; echo '>450 HD Polyoxford, 420 HD Nylon</option>';

                                                echo '<option value="Textreme, 420 HD Nylon"';   if($value_row['option_value']=='Textreme, 420 HD Nylon')   { echo 'selected';} else echo ''; echo '>Textreme, 420 HD Nylon</option>';

                                                echo '<option value="Texamid 5.5, Rugg Tex 11.1, Texamid 11.1"';   if($value_row['option_value']=='Texamid 5.5, Rugg Tex 11.1, Texamid 11.1')   { echo 'selected';} else echo ''; echo '>Texamid 5.5, Rugg Tex 11.1, Texamid 11.1</option>';

                                                echo '<option value="Texamid 5.5, Rugg Tex 11.1"';   if($value_row['option_value']=='Texamid 5.5, Rugg Tex 11.1')   { echo 'selected';} else echo ''; echo '>Texamid 5.5, Rugg Tex 11.1</option>';

                                                echo '<option value="Textreme 6.6"';   if($value_row['option_value']=='Textreme 6.6')   { echo 'selected';} else echo ''; echo '>Textreme 6.6</option>';

                                                echo '<option value="Polartec® Classic 200"';   if($value_row['option_value']=='Polartec® Classic 200')   { echo 'selected';} else echo ''; echo '>Polartec® Classic 200</option>';

                                                echo '<option value="Polartec® Thermal Pro®"';   if($value_row['option_value']=='Polartec® Thermal Pro®')   { echo 'selected';} else echo ''; echo '>Polartec® Thermal Pro®</option>';

                                                echo '<option value="Polartec® Windbloc®"';   if($value_row['option_value']=='Polartec® Windbloc®')   { echo 'selected';} else echo ''; echo '>Polartec® Windbloc®</option>';

                                                echo '<option value="Polartec® Wind Pro®"';   if($value_row['option_value']=='Polartec® Wind Pro®')   { echo 'selected';} else echo ''; echo '>Polartec® Wind Pro®</option>';

                                                echo '<option value="Polyester 600D Rip Stop / 1680D FTX TM PD, WR, PU"';   if($value_row['option_value']=='Polyester 600D Rip Stop / 1680D FTX TM PD, WR, PU')   { echo 'selected';} else echo ''; echo '>Polyester 600D Rip Stop / 1680D FTX TM PD, WR, PU</option>';

                                                echo '<option value="Polyester 450D Rip Stop / 600D DIAMOND Rip Stop, WR, PU"';   if($value_row['option_value']=='Polyester 450D Rip Stop / 600D DIAMOND Rip Stop, WR, PU')   { echo 'selected';} else echo ''; echo '>Polyester 450D Rip Stop / 600D DIAMOND Rip Stop, WR, PU</option>';

                                                echo '<option value="Polyester 600D DIAMOND / 1680D, WR, PU"';   if($value_row['option_value']=='Polyester 600D DIAMOND / 1680D, WR, PU')   { echo 'selected';} else echo ''; echo '>Polyester 600D DIAMOND / 1680D, WR, PU</option>';

                                                echo '<option value="NYLON CORDURA 500D, Rip Stop PU, W/R, cire, 1680D FTX TM PD, WR"';   if($value_row['option_value']=='NYLON CORDURA 500D, Rip Stop PU, W/R, cire, 1680D FTX TM PD, WR')   { echo 'selected';} else echo ''; echo '>NYLON CORDURA 500D, Rip Stop PU, W/R, cire, 1680D FTX TM PD, WR</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '67')///-------------------------------------------Подвесная система--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="AAS 1"';   if($value_row['option_value']=='AAS 1')   { echo 'selected';} else echo ''; echo '>AAS 1</option>';

                                                echo '<option value="V-VAR TORSO"';   if($value_row['option_value']=='V-VAR TORSO')   { echo 'selected';} else echo ''; echo '>V-VAR TORSO</option>';

                                                echo '<option value="SV System"';   if($value_row['option_value']=='SV System')   { echo 'selected';} else echo ''; echo '>SV System</option>';

                                                echo '<option value="SX System"';   if($value_row['option_value']=='SX System')   { echo 'selected';} else echo ''; echo '>SX System</option>';

                                                echo '<option value="YL System"';   if($value_row['option_value']=='YL System')   { echo 'selected';} else echo ''; echo '>YL System</option>';

                                                echo '<option value="V2 System"';   if($value_row['option_value']=='V2 System')   { echo 'selected';} else echo ''; echo '>V2 System</option>';

                                                echo '<option value="X Vent Vario System"';   if($value_row['option_value']=='X Vent Vario System')   { echo 'selected';} else echo ''; echo '>X Vent Vario System</option>';

                                                echo '<option value="X Lite Vario System"';   if($value_row['option_value']=='X Lite Vario System')   { echo 'selected';} else echo ''; echo '>X Lite Vario System</option>';

                                                echo '<option value="V1 Eco System"';   if($value_row['option_value']=='V1 Eco System')   { echo 'selected';} else echo ''; echo '>V1 Eco System</option>';

                                                echo '<option value="X1 System"';   if($value_row['option_value']=='X1 System')   { echo 'selected';} else echo ''; echo '>X1 System</option>';

                                                echo '<option value="TCS TORSO"';   if($value_row['option_value']=='TCS TORSO')   { echo 'selected';} else echo ''; echo '>TCS TORSO</option>';

                                                echo '<option value="VAR TORSO"';   if($value_row['option_value']=='VAR TORSO')   { echo 'selected';} else echo ''; echo '>VAR TORSO</option>';

                                                echo '<option value="VAR"';   if($value_row['option_value']=='VAR')   { echo 'selected';} else echo ''; echo '>VAR</option>';

                                                echo '<option value="FRAME VAR"';   if($value_row['option_value']=='FRAME VAR')   { echo 'selected';} else echo ''; echo '>FRAME VAR</option>';

                                                echo '<option value="CR"';   if($value_row['option_value']=='CR')   { echo 'selected';} else echo ''; echo '>CR</option>';

                                                echo '<option value="3D EVS"';   if($value_row['option_value']=='3D EVS')   { echo 'selected';} else echo ''; echo '>3D EVS</option>';

                                                echo '<option value="VERTI COOL"';   if($value_row['option_value']=='VERTI COOL')   { echo 'selected';} else echo ''; echo '>VERTI COOL</option>';

                                                echo '<option value="AVS"';   if($value_row['option_value']=='AVS')   { echo 'selected';} else echo ''; echo '>AVS</option>';

                                                echo '<option value="PERFORATE"';   if($value_row['option_value']=='PERFORATE')   { echo 'selected';} else echo ''; echo '>PERFORATE</option>';

                                                echo '<option value="AIR DIRECTOR"';   if($value_row['option_value']=='AIR DIRECTOR')   { echo 'selected';} else echo ''; echo '>AIR DIRECTOR</option>';

                                                echo '<option value="X-VAR TORSO"';   if($value_row['option_value']=='X-VAR TORSO')   { echo 'selected';} else echo ''; echo '>X-VAR TORSO</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '68')///-------------------------------------------Совместимость H2O--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '69')///-------------------------------------------набедреный пояс--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '70')///-------------------------------------------нижний вход с перегородкой--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '71')///-------------------------------------------регулировка клапана по высоте--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '72')///-------------------------------------------грудная стяжка--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '73')///-------------------------------------------боковые карманы--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '74')///-------------------------------------------фронтальный карман--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '75')///-------------------------------------------накидка от дождя--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '77')///-------------------------------------------Лямки для переноски--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '78')///------------------------------------------Светоотражатели--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '76')///-------------------------------------------гарантия--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="6 месяцев"';   if($value_row['option_value']=='6 месяцев')   { echo 'selected';} else echo ''; echo '>6 месяцев</option>';

                                                echo '<option value="12 месяцев"';   if($value_row['option_value']=='12 месяцев')   { echo 'selected';} else echo ''; echo '>12 месяцев</option>';

                                                echo '<option value="24 месяцев"';   if($value_row['option_value']=='24 месяцев')   { echo 'selected';} else echo ''; echo '>24 месяцев</option>';

                                                echo '<option value="36 месяцев"';   if($value_row['option_value']=='36 месяцев')   { echo 'selected';} else echo ''; echo '>36 месяцев</option>';

                                                echo '<option value="пожизненная"';   if($value_row['option_value']=='пожизненная')   { echo 'selected';} else echo ''; echo '>пожизненная</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '131')///-------------------------------------------Оттяжки--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            elseif ($option_row["optionID"] == '132')///-------------------------------------------Колышки--------------------------------------------

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                echo '<tr>

								<td>&nbsp;</td>

								<td><input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

											onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );"

								'.$str_checked.'> </td>

								<td>

									<select name="option_value_'.$option_row["optionID"].'">';

                                                echo '<option value="0">'.ADMIN_CHOICE.'</option>';

                                                echo '<option value="есть"';   if($value_row['option_value']=='есть')   { echo 'selected';} else echo ''; echo '>есть</option>';

                                                echo '<option value="нет"';   if($value_row['option_value']=='нет')   { echo 'selected';} else echo ''; echo '>нет</option>';

                                                echo '<option value="ввертыши сталь 6 шт"';   if($value_row['option_value']=='ввертыши сталь 6 шт')   { echo 'selected';} else echo ''; echo '>ввертыши сталь 6 шт</option>';

                                                echo '			</select>

								</td>

						  </tr>';

                                            }

                                            ///-------------------------------------------Стандартное поле ввода---------------------------------------------

                                            else

                                            {

                                                if ( $value_row["option_type"]==0 && strlen($value_row["option_value"]) > 0 )

                                                    $str_checked = "checked";

                                                else

                                                    $str_checked = "";

                                                $value_str = str_replace("\"","&quot;",$value_row["option_value"]);

                                                echo '<tr>

					<td>&nbsp;</td>

					<td valign="top">

					<input name="option_radio_type_'.$option_row["optionID"].'" type="radio" value="ANY_VALUE"

						onclick="JavaScript:SetEnabledStateTextValueField('.$option_row['optionID'].', \'ANY_VALUE\' );" '.$str_checked.'>

					</td><td>

						'.ADMIN_ANY_VALUE.'';

                                                echo '<input type=text  size="100" name="option_value_'.$option_row["optionID"].'"	value="'.$value_str.'"  ></td></tr>';

                                            }

                                            ?>

                                            <tr>

                                                <td>&nbsp;</td>

                                                <td valign='top'>

                                                    <input name='option_radio_type_<?php echo $option_row["optionID"]?>'

                                                           type='radio' value="N_VALUES"

                                                           onclick="JavaScript:SetEnabledStateTextValueField(<?php echo $option_row['optionID']?>, 'N_VALUES' );"

                                                        <?php

                                                        if ( $value_row["option_type"]==1 )

                                                            echo "checked";

                                                        ?>

                                                    >

                                                </td>

                                                <td>

                                                    <table cellpadding='0' id='OptionTable_<?php echo $option_row["optionID"]?>'>

                                                        <tr>

                                                            <td>

                                                                <?php echo ADMIN_SELECTING_FROM_VALUES;?> (<?php echo $ValueCount?> <?php echo ADMIN_VARIANTS;?>)

                                                            </td>

                                                        </tr>

                                                        <tr>

                                                            <td>

                                                                <a name="option_value_configurator_<?php echo $option_row['optionID']?>"

                                                                    <?php

                                                                    if ( $_GET["productID"] != 0 )

                                                                    {

                                                                        ?>

                                                                        href="JavaScript:open_window('option_value_configurator.php?optionID=<?php echo $option_row["optionID"]?>&productID=<?php echo $_GET["productID"]?>',400,400);"

                                                                        <?php

                                                                    } else

                                                                    {

                                                                        ?>

                                                                        href="JavaScript:AddProductAndOpen_option_value_configurator(<?php echo $option_row["optionID"]?>)"

                                                                        <?php

                                                                    }

                                                                    ?>

                                                                >

                                                                    <?php echo ADMIN_SELECT_SETTING;?>...

                                                                </a>

                                                            </td>

                                                        </tr>

                                                    </table>

                                                </td>

                                            </tr>

                                            <tr>

                                                <td colspan=3>

                                                    <hr width="100%" color=black></hr>

                                                </td>

                                            </tr>

                                        </table>

                                        <?php

                                    }

                                    ?>

                                </td></tr>

                        </table>

                        <script language='JavaScript'>

                            <?php

                            if ( $showConfiguratorTable == 0 )

                            {

                            ?>

                            ConfiguratorTable.style.display = 'none';

                            <?php

                            }

                            ?>

                        </script>

                        <input type="submit" name="AddProductAndOpenConfigurator"

                               value="" width=5>

                        <input type="hidden" name="optionID" value="">

                        <script language='JavaScript'>

                            document.MainForm.AddProductAndOpenConfigurator.style.display = 'none';

                            function AddProductAndOpen_option_value_configurator(optionID)

                            {

                                document.MainForm.optionID.value = optionID;

                                document.MainForm.AddProductAndOpenConfigurator.click();

                            }

                        </script>

                    </td>

                </tr>

                <tr align=right><td>&nbsp;</td></tr>

                <tr>

                    <td colspan=2 align=center>

                        <center>

                            <a href="JavaScript:PhotoHideTable();">

                                <?php echo ADMIN_PHOTOS;?>

                            </a>

                            <input type=hidden name='PhotoHideTable_hidden'

                                   value='<?php echo $showPhotoTable;?>'>

                        </center>

                        <script language='javascript'>

                            function PhotoHideTable()

                            {

                                if ( PhotoTable.style.display == 'none' )

                                {

                                    PhotoTable.style.display = 'block';

                                    document.MainForm.PhotoHideTable_hidden.value='1';

                                }

                                else

                                {

                                    PhotoTable.style.display = 'none';

                                    document.MainForm.PhotoHideTable_hidden.value='0';

                                }

                            }

                        </script>

                        <table id='PhotoTable'><tr><td>

                                    <table border=0 cellpadding=5 cellspacing=1 bgcolor=#C3BD7C>

                                        <tr>

                                            <td colspan=5 align=center>

                                                <b><?php echo ADMIN_PHOTOS;?></b>

                                            </td>

                                        </tr>

                                        <tr bgcolor=#F5F5C5>

                                            <td><?php echo ADMIN_DEFAULT_PHOTO;?></td>

                                            <td><?php echo ADMIN_PRODUCT_PICTURE;?></td>

                                            <td><?php echo ADMIN_PRODUCT_THUMBNAIL;?></td>

                                            <td><?php echo ADMIN_PRODUCT_BIGPICTURE;?></td>

                                            <td width=1%>&nbsp;</td>

                                        </tr>

                                        <?php

                                        foreach( $picturies as $picture )

                                        {

                                            echo("<tr bgcolor=#FFFFE2>");

                                            // default picture radio button

                                            if ( $picture["default_picture"] == 1 )

                                            {

                                                $default_picture_exists = true;

                                                echo("<td><input type=radio name=default_picture value='".$picture["photoID"].

                                                    "' checked></input></td>");

                                            }

                                            else

                                                echo("<td><input type=radio name=default_picture value='".$picture["photoID"].

                                                    "'></input></td>");

                                            // conventional picture ( filename field )

                                            echo("<td>");

                                            echo("		<input type=text name=filename_".$picture["photoID"].

                                                " value='".$picture["filename"]."'><br>" );

                                            if ( file_exists("./products_pictures/".$picture["filename"])

                                                && trim($picture["filename"]) != "" )

                                                echo("		<a class=small href='javascript:open_window(\"products_pictures/".$picture["filename"]."\",".GetPictureSize($picture["filename"]).")'>".ADMIN_PHOTO_PREVIEW."</a>");

                                            else

                                                echo(ADMIN_PICTURE_NOT_UPLOADED);

                                            echo("</td>");

                                            // small picture ( thumbnail field )

                                            echo("<td>");

                                            echo("		<input type=text name=thumbnail_".$picture["photoID"].

                                                " value='".$picture["thumbnail"]."'><br>" );

                                            if ( file_exists("./products_pictures/".$picture["thumbnail"])

                                                && trim($picture["thumbnail"]) != "" )

                                            {

                                                echo("		<a class=small href='javascript:open_window(\"products_pictures/".$picture["thumbnail"]."\",".GetPictureSize($picture["thumbnail"]).")'>".ADMIN_PHOTO_PREVIEW."</a>");

                                                echo("		<a class=small href=\"javascript:confirmDelete('".QUESTION_DELETE_PICTURE."', 'products.php?delete_one_picture=1&thumbnail=".$picture["photoID"]."&productID=".$_GET["productID"]."')\">".DELETE_BUTTON."</a>" );

                                            }

                                            else

                                                echo(ADMIN_PICTURE_NOT_UPLOADED);

                                            echo("</td>");

                                            // large picture ( enlarged field )

                                            echo("<td>");

                                            echo("		<input type=text name=enlarged_".$picture["photoID"].

                                                " value='".$picture["enlarged"]."'><br>" );

                                            if ( file_exists("./products_pictures/".$picture["enlarged"])

                                                && trim($picture["enlarged"]) != "" )

                                            {

                                                echo("		<a class=small href='javascript:open_window(\"products_pictures/".$picture["enlarged"]."\",".GetPictureSize($picture["enlarged"]).")'>".ADMIN_PHOTO_PREVIEW."</a>");

                                                echo("		<a class=small href=\"javascript:confirmDelete('".QUESTION_DELETE_PICTURE."', 'products.php?delete_one_picture=1&enlarged=".$picture["photoID"]."&productID=".$_GET["productID"]."')\">".DELETE_BUTTON."</a>" );

                                            }

                                            else

                                                echo( ADMIN_PICTURE_NOT_UPLOADED );

                                            echo("</td>");

                                            // delete button

                                            echo("<td>");

                                            ?>

                                            <a href=

                                               "javascript:confirmDelete('<?php echo QUESTION_DELETE_PICTURE?>','products.php?productID=<?php echo $_GET["productID"]?>&photoID=<?php echo $picture["photoID"]?>&delete_pictures=1');">

                                                <img src="images/remove.jpg" border=0 alt="<?php echo DELETE_BUTTON?>">

                                            </a>

                                            <?php

                                            echo("</td>");

                                            echo("</tr>");

                                        }

                                        ?>

                                        <tr>

                                            <td colspan=5 align=center>

                                                <?php echo ADD_BUTTON?>:

                                            </td>

                                        </tr>

                                        <tr bgcolor=#FFFFE2>

                                            <td width=1%>

                                                <input type=radio name=default_picture

                                                    <?php

                                                    if ( !isset($default_picture_exists) )

                                                    {

                                                        ?>

                                                        checked

                                                        <?php

                                                    }

                                                    ?>

                                                       value=-1

                                                >

                                                </input>

                                            </td>

                                            <td>

                                                <input type="file" name="new_filename" width=10></td>

                                            <td><input type="file" name="new_thumbnail"></td>

                                            <td><input type="file" name="new_enlarged"></td>

                                            <td width=1%>&nbsp;</td>

                                        </tr>

                                    </table>

                                    <br>

                                    <center>

                                        <input type=submit name="save_pictures" value="<?php echo ADMIN_SAVE_PHOTOS?>">

                                    </center>

                                </td></td></table>

                        <script language='JavaScript'>

                            <?php

                            if ( $showPhotoTable == 0 )

                            {

                            ?>

                            PhotoTable.style.display = 'none';

                            <?php

                            }

                            ?>

                        </script>

                    </td>

                </tr>

                <tr>

                    <td colspan=2 align=center>

                    </td>

                </tr>

                <?php

                // }

                ?>

                <?php

                ?>

                <tr>

                    <td colspan=2 align=center>

                        <table id='FileNameTable'>

                            <tr>

                                <td colspan=3>

                                    <input type=checkbox name='ProductIsProgram'

                                           value='1'

                                           onclick='JavaScript:ProductIsProgramHandler();'

                                        <?php

                                        if ( trim($product["eproduct_filename"]) != "" )

                                        {

                                            ?>

                                            checked

                                            <?php

                                        }

                                        ?>

                                    >

                                    <?php echo ADMIN_PRODUCT_IS_PROGRAM;?>

                                </td>

                            </tr>

                            <script language='JavaScript'>

                                function ProductIsProgramHandler()

                                {

                                    document.MainForm.eproduct_filename.disabled =

                                        !document.MainForm.ProductIsProgram.checked;

                                    document.MainForm.eproduct_available_days.disabled =

                                        !document.MainForm.ProductIsProgram.checked;

                                    document.MainForm.eproduct_download_times.disabled =

                                        !document.MainForm.ProductIsProgram.checked;

                                }

                            </script>

                            <tr>

                                <td>

                                    <?php echo ADMIN_EPRODUCT_FILENAME;?>

                                </td>

                                <td>

                                    <?php echo ADMIN_EPRODUCT_AVAILABLE_DAYS;?>

                                </td>

                                <td>

                                    <?php echo ADMIN_EPRODUCT_DOWNLOAD_TIMES;?>

                                </td>

                            </tr>

                            <tr>

                                <td>

                                    <input type='file' name='eproduct_filename'

                                           value='<?php echo $product["eproduct_filename"];?>' >

                                    <br>

                                    <?php

                                    if ( file_exists("./products_files/".$product["eproduct_filename"])  &&

                                        $product["eproduct_filename"]!=null )

                                    {

                                        ?>

                                        (<?php echo $product["eproduct_filename"];?>)

                                        <?php

                                    }

                                    else

                                    {

                                        ?>

                                        <?php echo ADMIN_FILE_NOT_UPLOADED;?>

                                        <?php

                                    }

                                    ?>

                                </td>

                                <td>

                                    <?php

                                    $valueArray[] = 1;

                                    $valueArray[] = 2;

                                    $valueArray[] = 3;

                                    $valueArray[] = 4;

                                    $valueArray[] = 5;

                                    $valueArray[] = 7;

                                    $valueArray[] = 14;

                                    $valueArray[] = 30;

                                    $valueArray[] = 180;

                                    $valueArray[] = 365;

                                    ?>

                                    <select name='eproduct_available_days'>

                                        <?php

                                        foreach($valueArray as $value)

                                        {

                                            ?>

                                            <option value='<?php echo $value;?>'

                                                <?php

                                                if ( $product["eproduct_available_days"] == $value )

                                                {

                                                    ?>

                                                    selected

                                                    <?php

                                                }

                                                ?>

                                            > <?php echo $value;?> </option>

                                            <?php

                                        }

                                        ?>

                                    </select>

                                </td>

                                <td>

                                    <input type=text name='eproduct_download_times'

                                           value='<?php echo $product["eproduct_download_times"];?>' >

                                </td>

                            </tr>

                        </table>

                        <script language='JavaScript'>

                            ProductIsProgramHandler();

                        </script>

                    </td>

                </tr>

                <?php

                ?>

                <tr>

                    <td align="left" colspan="2" style="font-weight:bold;"><?php echo ADMIN_PRODUCT_BRIEF_DESC;?> (HTML):</td>

                </tr>

                <tr>

                    <!--<td><textarea name="brief_description" rows=7 cols=40><?php //echo str_replace("<","&lt;",$product["brief_description"]); ?></textarea></td>-->

                    <td colspan="2">

                        <?php
                        //echo $product["brief_description"];

                        $oFCKeditor = new FCKeditor('brief_description') ;

                        $oFCKeditor->BasePath = '/js/fckeditor/' ;

                        $oFCKeditor->Value = $product["brief_description"];

                        $oFCKeditor->Create();

                        ?>

                </tr>

                <tr>

                    <td align="left" colspan="2" style="font-weight:bold;"><?php echo ADMIN_PRODUCT_DESC;?> (HTML):</td>

                </tr>

                <tr>

                    <!--<td><textarea name="description" rows=15 cols=40><?php //echo str_replace("<","&lt;",$product["description"]); ?></textarea></td>-->

                    <td colspan="2">

                        <?php

                        $oFCKeditor = new FCKeditor('description') ;

                        $oFCKeditor->BasePath = '/js/fckeditor/' ;

                        $oFCKeditor->Value = $product["description"];

                        $oFCKeditor->Create();

                        ?>

                    </td>

                </tr>









                <tr>

                    <td align="left" colspan="2" style="font-weight:bold;"><?php echo ADMIN_PRODUCT_BRIEF_DESC2;?> (HTML):</td>

                </tr>

                <tr>

                    <!--<td><textarea name="brief_description" rows=7 cols=40><?php //echo str_replace("<","&lt;",$product["brief_description"]); ?></textarea></td>-->

                    <td colspan="2">

                        <?php

                        $oFCKeditor = new FCKeditor('brief_description2') ;

                        $oFCKeditor->BasePath = '/js/fckeditor/' ;

                        $oFCKeditor->Value = $product["brief_description2"];

                        $oFCKeditor->Create();

                        ?>

                </tr>

                <tr>

                    <td align="left" colspan="2" style="font-weight:bold;"><?php echo ADMIN_PRODUCT_DESC2;?> (HTML):</td>

                </tr>

                <tr>

                    <!--<td><textarea name="description" rows=15 cols=40><?php //echo str_replace("<","&lt;",$product["description"]); ?></textarea></td>-->

                    <td colspan="2">

                        <?php

                        $oFCKeditor = new FCKeditor('description2') ;

                        $oFCKeditor->BasePath = '/js/fckeditor/' ;

                        $oFCKeditor->Value = $product["description2"];

                        $oFCKeditor->Create();

                        ?>

                    </td>

                </tr>



                <tr>

                    <td align="center" colspan="2">

                        <?php echo ADMIN_META_DESCRIPTION;?><br>

                        <textarea name='meta_description' rows=10 cols=90><?php echo $product["meta_description"];?></textarea>

                    </td>

                </tr>

                <tr>

                    <td align="center" colspan="2">

                        <?php echo ADMIN_META_KEYWORDS;?><br>

                        <textarea name='meta_keywords' 	rows=10 cols=90><?php echo $product["meta_keywords"];?></textarea>

                    </td>

                </tr>

            </table>

            <?php if ($_GET["productID"]) { ?>

                <hr size=1 width=90%>

                <center>

                    <font><b><?php echo STRING_RELATED_ITEMS;?></b></font>

                    <?php

                    $q = db_query("SELECT count(*) FROM ".RELATED_PRODUCTS_TABLE." WHERE Owner='".$_GET["productID"]."'") or die (db_error());

                    $cnt = db_fetch_row($q);

                    if ($cnt[0] == 0) echo "<p><font>< ".STRING_EMPTY_CATEGORY." ></font></p>";

                    else {

                        $q = db_query("SELECT productID FROM ".RELATED_PRODUCTS_TABLE." WHERE Owner='".$_GET["productID"]."'") or die (db_error());

                        echo "<table>";

                        while ($r = db_fetch_row($q))

                        {

                            $p = db_query("SELECT productID, name FROM ".PRODUCTS_TABLE." WHERE productID=$r[0]") or die (db_error());

                            if ($r1 = db_fetch_row($p))

                            {

                                echo "<tr>";

                                echo "<td width=100%>$r1[1]</td>";

                                echo "</tr>";

                            }

                        }

                        echo "</table>";

                    }

                    ?>

                    [ <a href="javascript:open_window('wishlist.php?owner=<?php echo $_GET["productID"]; ?>',400,600);"><?php echo EDIT_BUTTON; ?></a> ]

                </center>

                <hr size=1 width=90%>

            <?php } ?>

    <p><center>

        <input type="submit" name="save_product" value="<?php echo SAVE_BUTTON;?>" width=5>

        <input type="button" value="<?php echo CANCEL_BUTTON;?>" onClick="window.close();">

        <?php	if ($_GET["productID"]) echo "<input type=button value=\"".DELETE_BUTTON."\" onClick=\"confirmDelete('".QUESTION_DELETE_CONFIRMATION."','products.php?productID=".$_GET["productID"]."&delete=1');\">"; ?>

        &nbsp;<?php    if ($_GET["productID"]) echo "<input type=submit name=dbl_product value=\"".DBL_BUTTON."\" onClick=\"confirmDelete('".QUESTION_DBL_CONFIRMATION."','products.php?productID=".$_GET["productID"]."&dbl=1');\">"; ?>

    </center></p>

    <input type=hidden name='save_product_without_closing' value='0'>

    </form>

</center>

</body>

</html>

