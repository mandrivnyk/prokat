<?php
//frequently used functions

if(!isset($_SERVER['REQUEST_URI'])){
    $req = $_SERVER['PHP_SELF'];
    if ( isset($_SERVER['QUERY_STRING']) && (strlen($_SERVER['QUERY_STRING']) > 0))
    $req .= '?'.$_SERVER['QUERY_STRING'];
    $_SERVER['REQUEST_URI'] = $GLOBALS['REQUEST_URI'] = $req;
}

/**
 * ss_mail() is used all around the software for sending emails
 */
function ss_mail($email, $subject, $text, $headers = ""){

    return @mail($email, $subject, $text, $headers);

	//IMPORTANT:
	//If you hosting provider restricts usage of PHP mail() functions, delete the 'return mail()' line above
	//and uncomment the code below.
	//make sure to specify valid SMTP server configuration in the $params array below
	//(please request this information from your hosting provider)

/*
    include_once './classes/class.smtp.php';

    $params['host'] = 'smtp.server.com';                // The smtp server host/ip
    $params['port'] = 25;                        // The smtp server port
    $params['helo'] = 'server.com';            // What to use when sending the helo command. Typically, your domain/hostname
    $params['auth'] = TRUE;                        // Whether to use basic authentication or not
    $params['user'] = 'user+server.com';                // Username for authentication
    $params['pass'] = '1234567890';                // Password for authentication

    $send_params['recipients']    = explode(',',$email);
    $send_params['headers']        = explode("\n", "To:".str_replace("\n","",$email)."\nSubject: ".str_replace("\n","",$subject)."\n".$headers);
    $send_params['body']        = $text;

    if(is_object($smtp = smtp::connect($params)) AND $smtp->send($send_params)){

        return true;
    }else{

        return false;
        //echo 'Error sending mail'."\r\n\r\n";

        // The reason for failure should be in the errors variable
        //print_r($smtp->errors);
    }
*/
}

function MagicQuotesRuntimeSetting()
{
	ini_set("magic_quotes_runtime",0);
}


function myfile_get_contents( $fileName )
{
	return implode( "", file($fileName) );
}


function correct_URL( $url, $mode = "http" ) //converts
{
	$URLprefix = trim( $url );
	$URLprefix = str_replace("http://",  "", $URLprefix);
	$URLprefix = str_replace("https://", "", $URLprefix);
	$URLprefix = str_replace("index.php", "", $URLprefix);
	if ($URLprefix[ strlen($URLprefix)-1 ] == '/')
	{
		$URLprefix = substr( $URLprefix, 0, strlen($URLprefix)-1 );
	}
	return( $mode."://".$URLprefix."/" );
}

// *****************************************************************************
// Purpose	sets access rights to files which uploaded with help move_uploaded_file
//			function
// Inputs   	$file_name - file name
// Remarks
// Returns	nothing
function SetRightsToUploadedFile( $file_name )
{
	@chmod( $file_name, 0777);
	
}



// *****************************************************************************
// Purpose	this function works without errors ( as is_writable PHP functoin )
// Inputs   	$url
// Remarks
// Returns	nothing
function IsWriteable( $fileName )
{
	$f = @fopen($fileName, "a");
	return !is_bool( $f );
}


// *****************************************************************************
// Purpose	redirects to other PHP page specified URL ( $url )
// Inputs   	$url
// Remarks	this function uses header
// Returns	nothing
function Redirect( $url )
{
	@header("Location: ".$url);
	exit;
}


// *****************************************************************************
// Purpose	redirects to other PHP page specified URL ( $url )
// Inputs
// Remarks	if CONF_PROTECTED_CONNECTION == '1' this function uses protected ( https:// ) connection
//			else it uses unsecure http://
//			$url is relative URL, NOT an absolute one, e.g. index.php, index.php?productID=x, but not http://www.example.com/
// Returns	nothing
function RedirectProtected( $url )
{
	if ( CONF_PROTECTED_CONNECTION == '1' )
	{
		Redirect( correct_URL(CONF_FULL_SHOP_URL,"https").$url ); //redirect to HTTPS part of the website
	}
	else
		Redirect( $url ); //relative URL
}


// *****************************************************************************
// Purpose	redirects to other PHP page specified URL ( $url )
// Inputs   	$url
// Remarks	this function uses JavaScript client script
// Returns	nothing
function RedirectJavaScript( $url )
{
	die( "<script language='JavaScript'> window.location = '".$url."'; </script>" );
}


// *****************************************************************************
// Purpose	round float value to 0.01 precision
// Inputs   	$float_value - value to float
// Remarks
// Returns	rounded value
function RoundFloatValue( $float_value )
{
	return round (100*$float_value)/100;
}


// *****************************************************************************
// Purpose	round float value to 0.01 precision
// Inputs   $float_value - value to float
// Remarks	this function returns string value.
//				Two digits locate after decimal point always.
// Returns	rounded value
function RoundFloatValueStr( $float_value )
{
	$str = RoundFloatValue( $float_value );
	$index = strpos($str,".");
	if ( $index === false )
		return $str.".00";
	else
	{
		if ( strlen($str)-1-$index == 1 )
			return $str."0";
		else
			return $str;
	}
}

function _testExtension( $filename, $extension )
{
	if ( $extension == null || trim($extension) == "" )
		return true;
	$i=strlen($filename)-1;
	for( ; $i >= 0; $i-- )
	{
		if ( $filename[$i] == '.' )
			break;
	}

	if ( $filename[$i] != '.' )
		return false;
	else
	{
		$ext = substr( $filename, $i+1 );
		return ( strtolower($extension) == strtolower($ext) );
	}
}


// *****************************************************************************
// Purpose	gets all files in specified directory
// Inputs   $dir - full path directory
// Remarks
// Returns
function GetFilesInDirectory( $dir, $extension = "" )
{
	$dh  = opendir($dir);
	$files = array();
	while (false !== ($filename = readdir($dh)))
	{
		if ( !is_dir($dir.'/'.$filename) && $filename != "." && $filename != ".." )
		{
			if ( _testExtension($filename,$extension) )
				$files[] = $dir."/".$filename;
		}
	}
	return $files;
}


// *****************************************************************************
// Purpose	gets class name in file
// Inputs   $fileName - full file name
// Remarks	this file must contains only one class syntax valid declaration
// Returns	class name
function GetClassName( $fileName )
{
	$strContent = myfile_get_contents( $fileName );
	$_match = array();
	$strContent = substr($strContent, strpos($strContent, '@connect_module_class_name'), 100);
	if(preg_match("|\@connect_module_class_name[\t ]+([0-9a-z_]*)|mi", $strContent, $_match)){

		return $_match[1];
	}else {

		return false;
	}
}

function InstallModule( $module )
{
	db_query("insert into ".MODULES_TABLE.
		" ( module_name ) ".
		" values( '".$module->title."' ) ");
}

function GetModuleId( $module )
{
	$q = db_query("select module_id from ".MODULES_TABLE.
		" where module_name='".$module->title."' ");
	$row = db_fetch_row($q);
	return (int)$row["module_id"];
}


function TransformStringToDataBase( $str )
{
	if (is_array($str))
	{
		foreach ($str as $key => $val)
		{
			$str[$key] = stripslashes($val);
		}
		$str = str_replace("\\","\\\\",$str);
	}
	else
	{
		$str = str_replace("\\","\\\\",stripslashes($str));
	}
	return str_replace( "'", "''", $str );
}

function TransformDataBaseStringToHTML_Text( $str )
{
	return $str;
}

function TransformDataBaseStringToText( $str )
{
	$str = str_replace( "&", "&#38;", $str );
	$str = str_replace( "'", "&#39;", $str );
	$str = str_replace( "<", "&lt;",  $str );
	$str = str_replace( ">", "&gt;",  $str );
	return $str;
}

function TransformDataToCopyFromPostToPage( $str )
{
	return TransformDataBaseStringToText( $str );
}



function TransformToSafeForm( & $data )
{
	$data = str_replace( "<", "&lt;",  $data );
	$data = str_replace( ">", "&gt;",  $data );
	$data = str_replace( "'", "&#39;", $data );
	return $data;
}

function PrepareHTML_Code( $data )
{
	$data = str_replace( "&lt;", "<", $data );
	$data = str_replace( "&gt;", ">", $data );
	return $data;
}



function _formatPrice($price)
{
	$price = (string)$price;

	if ( !strstr($price,".") )
		$price .= ".00";

	$oldPrice = $price;
	$res = "";

	$i = 0;
	for( $i=strlen($price)-1; $i>=0; $i-- )
	{
		if ( $price[$i] == "." )
			break;
		else
			$res = $price[$i].$res;
	}

	$res = ".".$res;

	$i--;
	$digitCounter = 0;
	for( ; $i>=0; $i-- )
	{
		$digitCounter++;
		$res = $price[$i].$res;
		if ( $digitCounter == 3 && $i != 0 )
		{
			$res = ",".$res;
			$digitCounter = 0;
		}
	}

	return $res;
}


function show_price($price, $custom_currency = 0) //show a number and selected currency sign
		//$price is in universal currency

		//if $custom_currency != 0 show price this currency with ID = $custom_currency
{
	global $selected_currency_details;

	if ($custom_currency == 0)
	{
		if (!isset($selected_currency_details) || !$selected_currency_details) //no currency found
		{
			return $price;
		}
	}
	else //show price in custom currency
	{
		$custom_currency = (int) $custom_currency;

		$q = db_query("select code, currency_value, where2show, currency_iso_3, Name from ".CURRENCY_TYPES_TABLE." where CID=$custom_currency") or die (db_error());
		if ($row = db_fetch_row($q))
		{
			$selected_currency_details = $row; //for show_price() function
		}
		else //no currency found. In this case check is there any currency type in the database
		{
			$q = db_query("select code, currency_value, where2show from ".CURRENCY_TYPES_TABLE) or die (db_error());
			if ($row = db_fetch_row($q))
			{
				$selected_currency_details = $row; //for show_price() function
			}
		}

	}

	//is exchange rate negative or 0?
	if ($selected_currency_details[1] == 0) return "";

	//now show price
	$price = round(100*$price*$selected_currency_details[1])/100;
	if (round($price*10) == $price*10 && round($price)!=$price)
		$price = "$price"."0"; //to avoid prices like 17.5 - write 17.50 instead

	$price = _formatPrice( $price );
	return $selected_currency_details[2] ?
		$price.$selected_currency_details[0] :
		$selected_currency_details[0].$price;
}





// *****************************************************************************
// Purpose
// Inputs
// Remarks
// Returns
function ShowPriceInTheUnit( $price, $currencyID )
{
	$q_currency = db_query( "select currency_value, where2show, code from ".
		CURRENCY_TYPES_TABLE." where CID=$currencyID" );
	$currency = db_fetch_row( $q_currency );
	$price = round( 100*$price*$currency["currency_value"] )/100;
	if (round($price*10) == $price*10 && round($price)!=$price)
		$price = "$price"."0"; //to avoid prices like 17.5 - write 17.50 instead
	return $currency["where2show"] ?  $price.$currency["code"] : $currency["code"].$price;
}



function addUnitToPrice( $price )
{
	global $selected_currency_details;

	return $selected_currency_details[2] ?
		$price.$selected_currency_details[0] :
		$selected_currency_details[0].$price;
}

function ConvertPriceToUniversalUnit($priceWithOutUnit)
{
	global $selected_currency_details;
	return (float)$priceWithOutUnit / (float)$selected_currency_details[1];
}

function show_priceWithOutUnit($price)
{
	global $selected_currency_details;

	if (!isset($selected_currency_details) || !$selected_currency_details)
		//no currency found
	{
		return $price;
	}

	//is exchange rate negative or 0?
	if ($selected_currency_details[1] == 0) return "";

	//now show price
	$price = round(100*$price*$selected_currency_details[1])/100;
	if (round($price*10) == $price*10 && round($price)!=$price)
		$price = "$price"."0"; //to avoid prices like 17.5 - write 17.50 instead
	return (float)$price;
}

function getPriceUnit()
{
	global $selected_currency_details;

	if (!isset($selected_currency_details) || !$selected_currency_details)
		//no currency found
	{
		return "";
	}
	return $selected_currency_details[0];
}

function getLocationPriceUnit()
{
	global $selected_currency_details;

	if (!isset($selected_currency_details) || !$selected_currency_details)
		//no currency found
	{
		return true;
	}
	return $selected_currency_details[2];
}	


/*
function get_current_time() //get current date and time as a string
//required to do INSERT queries of DATETIME/TIMESTAMP in different DBMSes
{
	$timestamp = time();
	if (DBMS == 'mssql')
		// $s = strftime("%H:%M:%S %d/%m/%Y", $timestamp);
		$s = strftime("%m.%d.%Y %H:%M:%S", $timestamp);
	else // MYSQL or IB
		$s = strftime("%Y-%m-%d %H:%M:%S", $timestamp);

	return $s;
}
*/


function ShowNavigator($a, $offset, $q, $path, &$out)
{
		global $url_name;
		//shows navigator [prev] 1 2 3 4 � [next]
		//$a - count of elements in the array, which is being navigated
		//$offset - current offset in array (showing elements [$offset ... $offset+$q])
		//$q - quantity of items per page
		//$path - link to the page (f.e: "index.php?categoryID=1&")
 
		if ($a > $q) //if all elements couldn't be placed on the page
		{
			
			//[prev]
			
			if ($offset>0) 
			{
				$sum0 = $offset-$q;
				if($sum0 == 0)
				{
					//echo $url_name;
					if(isset($_GET['brend']) && ($_GET['brend'] !== ''))
						$out .= "<a class=no_underline href=\"".$path."/offset/".($offset-$q)."\">&lt;&lt; ".STRING_PREVIOUS."</a> &nbsp;&nbsp;";
					else
						$out .= "<a class=no_underline href=\"/".$url_name."\">&lt;&lt; ".STRING_PREVIOUS."</a> &nbsp;&nbsp;";
				}
				else 
					$out .= "<a class=no_underline href=\"".$path."/offset/".($offset-$q)."\">&lt;&lt; ".STRING_PREVIOUS."</a> &nbsp;&nbsp;";
			}

			//digital links
			$k = $offset / $q;

			//not more than 4 links to the left
			$min = $k - 5;
			if ($min < 0) { $min = 0; }
			else {
				if ($min >= 1)
				{ //link on the 1st page
					if($_SERVER['SCRIPT_NAME'] == '/admin.php')
						$out .= "<a class=no_underline href=\"".$path."&offset=0\">1</a> &nbsp;&nbsp;";
					else
						$out .= "<a class=no_underline href=\"".$path."/offset/0\">1</a> &nbsp;&nbsp;";

					if ($min != 1) { $out .= "... &nbsp;"; };
				}
			}

			for ($i = $min; $i<$k; $i++)
			{
				$m = $i*$q + $q;
				if ($m > $a) $m = $a;
				if($_SERVER['SCRIPT_NAME'] == '/admin.php')
					$out .= "<a class=no_underline href=\"".$path."&offset=".($i*$q)."\">".($i+1)."</a> &nbsp;&nbsp;";
				else
				{
					$sum =$i+1;
					
					if($sum == 1)
						$out .= "<a class=no_underline href=\"/".$url_name."\">".($i+1)."</a> &nbsp;&nbsp;";
					else 
						$out .= "<a class=no_underline href=\"".$path."/offset/".($i*$q)."\">".($i+1)."</a> &nbsp;&nbsp;";
				}

			}

			//# of current page
			if (strcmp($offset, "show_all"))
			{
				$min = $offset+$q;
				if ($min > $a) $min = $a;
				$out .= "<font class=faq><b>".($k+1)."</b></font> &nbsp;&nbsp;";
			}
			else
			{
				$min = $q;
				if ($min > $a) $min = $a;
				if($_SERVER['SCRIPT_NAME'] == '/admin.php')
					$out .= "<a class=no_underline href=\"".$path."&offset=0\">1</a> &nbsp;&nbsp;";
				else
					$out .= "<a class=no_underline href=\"".$path."/offset/0\">1</a> &nbsp;&nbsp;";
			}

			//not more than 5 links to the right
			$min = $k + 6;
			if ($min > $a/$q) { $min = $a/$q; };
			for ($i = $k+1; $i<$min; $i++)
			{
				$m = $i*$q+$q;
				if ($m > $a) $m = $a;
				if($_SERVER['SCRIPT_NAME'] == '/admin.php')
					$out .= "<a class=no_underline href=\"".$path."&offset=".($i*$q)."\">".($i+1)."</a> &nbsp;&nbsp;";
				else
					$out .= "<a class=no_underline href=\"".$path."/offset/".($i*$q)."\">".($i+1)."</a> &nbsp;&nbsp;";
			}

			if ($min*$q < $a) { //the last link
				if ($min*$q < $a-$q) $out .= " ... &nbsp;&nbsp;";
				if($_SERVER['SCRIPT_NAME'] == '/admin.php')
					$out .= "<a class=no_underline href=\"".$path."&offset=".($a-$a%$q)."\">".(floor($a/$q)+1)."</a> &nbsp;&nbsp;";
				else
					$out .= "<a class=no_underline href=\"".$path."/offset/".($a-$a%$q)."\">".(floor($a/$q)+1)."</a> &nbsp;&nbsp;";
			}

			//[next]
			if (strcmp($offset, "show_all"))
				if ($offset<$a-$q)
				{
					if($_SERVER['SCRIPT_NAME'] == '/admin.php')
						$out .= "<a class=no_underline href=\"".$path."&offset=".($offset+$q)."\">".STRING_NEXT." &gt;&gt;</a> ";
					else
						$out .= "<a class=no_underline href=\"".$path."/offset/".($offset+$q)."\">".STRING_NEXT." &gt;&gt;</a> ";
				}
//echo $path;  
//[show all]
			if (strcmp($offset, "show_all"))
			{
				
				if($_SERVER['SCRIPT_NAME'] == '/admin.php')
					$out .= " |&nbsp; <a class=no_underline href=\"".$path."&show_all=yes\">".STRING_SHOWALL."</a>";
				else
					$out .= " |&nbsp; <a class=no_underline href=\"".$path."/show_all/yes\">".STRING_SHOWALL."</a>";
			}
			else
				$out .= " |&nbsp; <span class='nafigator-active'>".STRING_SHOWALL."</span>";
			/*echo '<pre>';
				print_r($_SERVER);
			echo '</pre>';*/
			//echo ($_SERVER['SCRIPT_NAME']);

		}
}


function GetCurrentURL( $file, $exceptKeys)
{
	$res = $file;
	foreach( $_GET as $key => $val )
	{
		$exceptFlag = false;
		foreach( $exceptKeys as $exceptKey  )
		 	if ( $exceptKey == $key )
			{
				$exceptFlag = true;
				break;
			}

		if ( !$exceptFlag )
		{
			if ( $res == $file )
				$res .= "?".$key."=".$val;
			else
				$res .= "&".$key."=".$val;
		}
	}
	return $res;
}


function GetNavigatorHtml(	$url, $countRowOnPage = CONF_PRODUCTS_PER_PAGE,
				$callBackFunction, $callBackParam, &$tableContent,
				&$offset, &$count )
{
	/*echo '<pre>';
		print_r($callBackParam);
	echo '</pre>';*/
	
	if ( isset($_GET["offset"]) )
		$offset = (int)$_GET["offset"];
	else
		$offset = 0;
	$offset -= $offset % $countRowOnPage;//CONF_PRODUCTS_PER_PAGE;
	if ( $offset < 0 ) $offset = 0;
	$count = 0;

	if ( !isset($_GET["show_all"]) ) //show 'CONF_PRODUCTS_PER_PAGE' products on this page
	{
		$tableContent = $callBackFunction( $callBackParam, $count,
					array(
						"offset" => $offset,
						"CountRowOnPage" => $countRowOnPage
					     )
				);
	}
	else //show all products
	{
		$tableContent = $callBackFunction( $callBackParam, $count, null );
		$offset = "show_all";
	}

	ShowNavigator( $count, $offset, $countRowOnPage,$url, $out);
		//echo $url;
//echo '<br>'.$out;
	return $out;
}




function moveCartFromSession2DB() //all products in shopping cart, which are in session vars, move to the database
{
	if (  isset($_SESSION["gids"]) && isset($_SESSION["log"])  )
	{

		$customerID = regGetIdByLogin( $_SESSION["log"] );
		$q = db_query( "select itemID from ".SHOPPING_CARTS_TABLE." where customerID=".$customerID );
		$items = array();
		while ( $item = db_fetch_row($q) )
			$items[] = $item["itemID"];

		//$i=0;
		foreach( $_SESSION["gids"] as $key => $productID )
		{
			if ( $productID == 0 )
				continue;

			// search product in current user's shopping cart content
			$itemID = null;
			for( $j=0; $j<count($items); $j++ )
			{
				$q = db_query( "select count(*) from ".SHOPPING_CART_ITEMS_TABLE." where productID=".$productID." AND ".
								" itemID=".$items[$j] );
				$count = db_fetch_row($q);
				$count = $count[0];
				if ( $count != 0 )
				{
					// compare configuration
					$configurationFromSession = $_SESSION["configurations"][$key];
					$configurationFromDB = GetConfigurationByItemId( $items[$j] );
					if ( CompareConfiguration($configurationFromSession, $configurationFromDB) )
					{
						$itemID = $items[$j];
						break;
					}
						$itemID = $items[$j];

				}
			}


			if ( $itemID == null )
			{
				// create new item
				db_query( "insert into ".SHOPPING_CART_ITEMS_TABLE.
					" (productID) values('".$productID."')\n" ) or die (db_error());
				$itemID = db_insert_id();

				// set content item
				foreach( $_SESSION["configurations"][$key] as $var )
				{
					db_query("insert into ".
						SHOPPING_CART_ITEMS_CONTENT_TABLE." ( itemID, variantID ) ".
						" values( '".$itemID."', '".$var."' )\n" ) or die (db_error());
				}

				// insert item into cart
				db_query("insert ".SHOPPING_CARTS_TABLE.
					"(customerID, itemID, Quantity)".
					"values( '".$customerID."', '".$itemID."', '".$_SESSION["counts"][$key].
						"' )\n" ) or die (db_error());
			}
			else
			{
				db_query( "update ".SHOPPING_CARTS_TABLE.
					" set Quantity=Quantity + ".$_SESSION["counts"][$key]." ".
					" where customerID=".$customerID." and itemID=".$itemID."\n" ) or die (db_error());
			}

		}

 		unset($_SESSION["gids"]);
		unset($_SESSION["counts"]);
		unset($_SESSION["configurations"]);
		session_unregister("gids"); //calling session_unregister() is required since unset() may not work on some systems
		session_unregister("counts");
		session_unregister("configurations");
	}
} // moveCartFromSession2DB

function validate_search_string($s) //validates $s - is it good as a search query
{
	//exclude special SQL symbols
	$s = str_replace("%","",$s);
	$s = str_replace("_","",$s);
	//",',\
	$s = stripslashes($s);
	$s = str_replace("'","\'",$s);
	return $s;

} //validate_search_string

function string_encode($s) // encodes a string with a simple algorythm
{
	$result = base64_encode($s);
	return $result;
}

function string_decode($s) // decodes a string encoded with string_encode()
{
	$result = base64_decode($s);
	return $result;
}





// *****************************************************************************
// Purpose	this function creates array it containes value POST variables
// Inputs     		name array
// Remarks		if <name> is contained in $varnames, then for POST variable
//				<name>_<id> in result array $data (see body) item is added
//				with key <id> and POST variable <name>_<id> value
// Returns		array $data ( see Remarks )
function ScanPostVariableWithId( $varnames )
{
        $data=array();
	foreach( $varnames as $name )
	{
		foreach( $_POST as $key => $value )
		{
			if ( strstr( $key, $name."_" ) )
			{
				$key = str_replace($name."_","",$key);
				$data[$key][ $name ] = $value;
			}
		}
	}
	return $data;
}


// *****************************************************************************
// Purpose	this functin does also as ScanPostVariableWithId
//			but it uses GET variables
// Inputs     	see ScanPostVariableWithId
// Remarks	see ScanPostVariableWithId
// Returns	see ScanPostVariableWithId
function ScanGetVariableWithId( $varnames )
{
        $data=array();
	foreach( $varnames as $name )
	{
		foreach( $_GET as $key => $value )
		{
			if ( strstr( $key, $name."_" ) )
			{
				$key = str_replace($name."_","",$key);
				$data[$key][ $name ] = $value;
			}
		}
	}
	return $data;
}


function value( $variable )
{
	if ( !isset($variable) )
		return "undefined";

	$res="";
	if ( is_null($variable) )
	{
		$res.="NULL";
	}
	else if ( is_array($variable) )
	{
		$res.="<b>array</b>";
		$res.="<ul>";
		foreach( $variable as $key => $value )
		{
			$res.="<li>";
			$res.="[ ".value($key)." ]=".value($value);
			$res.="</li>";
		}
		$res.="</ul>";
	}
	else if ( is_int($variable) )
	{
		$res.="<b>integer</b>\n";
		$res.=(string)$variable;
	}
	else if ( is_bool($variable) )
	{
		$res.="<b>bool</b>\n";
		if ( $variable )
			$res.="<i>True</i>";
		else
			$res.="<i>False</i>";
	}
	else if ( is_string($variable)  )
	{
		$res.= "<b>string</b>\n";
		$res.= "'".(string)$variable."'";
	}
	else if ( is_float($variable)  )
	{
		$res.= "<b>float</b>\n";
		$res.= (string)$variable;
	}

	return $res;
}





function debug( $variable )
{
	if ( !isset($variable) )
		echo("undefined");
	else
		echo( value($variable)."<br>" );
}

function set_query($_vars, $_request = '', $_store = false){

	if(!$_request){

		global $_SERVER;
		$_request = $_SERVER['REQUEST_URI'];
	}

	$_anchor = '';
	@list($_request, $_anchor) = explode('#', $_request);

	if(strpos($_vars, '#')!==false){

		@list($_vars, $_anchor) = explode('#', $_vars);
	}

	if(!$_vars && !$_anchor)
		return preg_replace('|\?.*$|','', $_request).($_anchor?'#'.$_anchor:'');
	elseif (!$_vars && $_anchor)
		return $_request.'#'.$_anchor;

	$_rvars = array();
	$tr_vars = explode('&', strpos($_request, '?')!==false?preg_replace('|.*\?|','',$_request):'');
	foreach ($tr_vars as $_var){

		$_t = explode('=', $_var);
		if($_t[0])$_rvars[$_t[0]] = $_t[1];
	}
	$tr_vars = explode('&', preg_replace(array('|^\&|','|^\?|'), '', $_vars));
	foreach ($tr_vars as $_var){

		$_t = explode('=', $_var);
		if(!$_t[1])unset($_rvars[$_t[0]]);
		else $_rvars[$_t[0]] = $_t[1];
	}
	$tr_vars = array();
	foreach ($_rvars as $_var=>$_val)
		$tr_vars[] = "$_var=$_val";

	if ($_store){

		global $_SERVER;
		$_request = $_SERVER['REQUEST_URI'];
		$_SERVER['REQUEST_URI'] = preg_replace('|\?.*$|','', $_request).(count($tr_vars)?'?'.implode('&', $tr_vars):'').($_anchor?'#'.$_anchor:'');
		return $_SERVER['REQUEST_URI'];
	}else
		return preg_replace('|\?.*$|','', $_request).(count($tr_vars)?'?'.implode('&', $tr_vars):'').($_anchor?'#'.$_anchor:'');
}

function getListerRange($_pagenumber, $_totalpages, $_lister_num = 20){

	if($_pagenumber<=0) return array('start'=>1, 'end'=>1);
	$lister_start=$_pagenumber-floor($_lister_num/2);
	$lister_start=($lister_start+$_lister_num<=$_totalpages?$lister_start:$_totalpages-$_lister_num+1);
	$lister_start=($lister_start>0?$lister_start:1);
	$lister_end=$lister_start+$_lister_num-1;
	$lister_end=($lister_end<=$_totalpages?$lister_end:$_totalpages);
	return array('start'=>$lister_start, 'end'=>$lister_end);
}

function html_spchars($_data){

	if(is_array($_data)){

		foreach ($_data as $_ind=>$_val){

			$_data[$_ind] = html_spchars($_val);
		}
		return $_data;
	}else
		return htmlspecialchars( $_data, ENT_QUOTES,'cp1251' );
		//return htmlspecialchars($_data, ENT_QUOTES);
}

/**
*Strip slashes if magic_quotes_gpc is On
*
*
*/
function xStripSlashesGPC($_data){

	if(!get_magic_quotes_gpc())return $_data;
	if(is_array($_data)){

		foreach ($_data as $_ind => $_val){

			$_data[$_ind] = xStripSlashesGPC($_val);
		}
		return $_data;
	}
	return stripslashes($_data);
}

/**
 * Transform date from template format to DATETIME format
 *
 * @param string $_date
 * @param string $_template template for transform
 * @return string
 */
function TransformTemplateToDATE($_date, $_template = ''){

	if(!$_template)$_template = CONF_DATE_FORMAT;
	$day 	= substr($_date, strpos($_template, 'DD'),2);
	$month 	= substr($_date, strpos($_template, 'MM'),2);
	$year 	= substr($_date, strpos($_template, 'YYYY'),4);
	return "{$year}-{$month}-{$day} ";
}

/**
 * Transform DATE to template format
 *
 * @param string $_date
 * @param string $_template template for transform
 * @return string
 */
function TransformDATEToTemplate($_date, $_template = ''){

	if(!$_template)$_template = CONF_DATE_FORMAT;
	preg_match('|(\d{4})-(\d{2})-(\d{2})|', $_date, $mathes);
	unset($mathes[0]);
	return str_replace(
		array('YYYY', 'MM', 'DD'),
		$mathes,
		$_template
		);
}

/**
 * Check date in template format
 *
 * @param string $_date
 * @param string $_template template for check
 * @return bool
 */
function isTemplateDate($_date, $_template = ''){

	if(!$_template)$_template = CONF_DATE_FORMAT;

	$ok = (strlen($_date)==strlen($_template) && (preg_replace('|\d{2}|', '', $_date) == str_replace(array('MM','DD','YYYY'), '', $_template)));
	$ok = ($ok && substr($_date, strpos($_template, 'DD'), 2)<32 && substr($_date, strpos($_template, 'MM'), 2)<13);
	return $ok;
}

/**
 * mail txt message from template
 * @param string email
 * @param string email subject
 * @param string template name
 */
function xMailTxt($_Email, $_Subject, $_TemplateName, $_AssignArray = array()){

	if(!$_Email)return 0;
	$mailSmarty = new Smarty();
	foreach ($_AssignArray as $_var=>$_val){

		$mailSmarty->assign($_var, $_val);
	}
	$_t = $mailSmarty->fetch('email/'.$_TemplateName);
	ss_mail($_Email, $_Subject, $_t, "From: \"".
				CONF_SHOP_NAME."\"<".
				CONF_GENERAL_EMAIL.">\n".stripslashes(EMAIL_MESSAGE_PARAMETERS).
				"\nReturn-path: <".CONF_GENERAL_EMAIL.">");
}

/**
 * replace newline symbols to &lt;br /&gt;
 * @param mixed data for action
 * @param array which elements test
 * @return mixed
 */
function xNl2Br($_Data, $_Key = array()){


	if (!is_array($_Data)){

		return nl2br($_Data);
	}

	if (!is_array($_Key))$_Key = array($_Key);
	foreach ($_Data as $__Key=>$__Data){

		if (count($_Key)&&!is_array($__Data)){

			if (in_array($__Key, $_Key)){

				$_Data[$__Key] = xNl2Br($__Data, $_Key);
			}
		}else $_Data[$__Key] = xNl2Br($__Data, $_Key);

	}
	return $_Data;
}

function xStripSlashesHTMLspecialChars($_Data){

	return html_spchars(xStripSlashesGPC($_Data));
}

function xStrReplace($_Search, $_Replace, $_Data, $_Key=array()){

	if (!is_array($_Data)){

		return str_replace($_Search, $_Replace, $_Data);
	}

	if (!is_array($_Key))$_Key = array($_Key);
	foreach ($_Data as $__Key=>$__Data){

		if (count($_Key)&&!is_array($__Data)){

			if (in_array($__Key, $_Key)){

				$_Data[$__Key] = xStrReplace($_Search, $_Replace, $__Data, $_Key);
			}
		}else $_Data[$__Key] = xStrReplace($_Search, $_Replace, $__Data, $_Key);

	}
	return $_Data;
}

function xHtmlSpecialChars($_Data, $_Params = array(), $_Key = array()){


	if (!is_array($_Data)){
//echo '3';
		return htmlspecialchars($_Data, ENT_QUOTES, 'cp1251');
	}

	if (!is_array($_Key))$_Key = array($_Key);
	foreach ($_Data as $__Key=>$__Data){

		if (count($_Key)&&!is_array($__Data)){

			if (in_array($__Key, $_Key)){

				$_Data[$__Key] = xHtmlSpecialChars( $__Data, $_Params, $_Key);
			}
		}else $_Data[$__Key] = xHtmlSpecialChars( $__Data, $_Params, $_Key);

	}
	return $_Data;
}

function xEscapeSQLstring ( $_Data, $_Params = array(), $_Key = array() ){

	if (!is_array($_Data)){

		return mysql_real_escape_string ($_Data);
	}

	if (!is_array($_Key))$_Key = array($_Key);
	foreach ($_Data as $__Key=>$__Data){

		if (count($_Key)&&!is_array($__Data)){

			if (in_array($__Key, $_Key)){

				$_Data[$__Key] = xEscapeSQLstring( $__Data, $_Params, $_Key);
			}
		}else $_Data[$__Key] = xEscapeSQLstring( $__Data, $_Params, $_Key);

	}
	return $_Data;
}

function xSaveData($_ID, $_Data, $_TimeControl = 0){

	if(!session_is_registered('_xSAVE_DATA')){

		session_register('_xSAVE_DATA');
		$_SESSION['_xSAVE_DATA'] = array();
	}

	if(intval($_TimeControl)){

		$_SESSION['_xSAVE_DATA'][$_ID] = array(
			$_ID.'_DATA' => $_Data,
			$_ID.'_TIME_CTRL' => array(
				'timetag' => time(),
				'timelimit' => $_TimeControl,
				),
			);
	}else{
		$_SESSION['_xSAVE_DATA'][$_ID] = $_Data;
	}
}

function xPopData($_ID){

	if(!isset($_SESSION['_xSAVE_DATA'][$_ID])){
		return null;
	}

	if(is_array($_SESSION['_xSAVE_DATA'][$_ID])){

		if(isset($_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL'])){

			if( ($_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL']['timetag']+$_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL']['timelimit']) < time() ){
				return null;
			}else{

				$Return = $_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_DATA'];
				unset($_SESSION['_xSAVE_DATA'][$_ID]);
				return $Return;
			}
		}
	}

	$Return = $_SESSION['_xSAVE_DATA'][$_ID];
	unset($_SESSION['_xSAVE_DATA'][$_ID]);
	return $Return;
}

function xDataExists($_ID){

	if(!isset($_SESSION['_xSAVE_DATA'][$_ID]))return 0;

	if(is_array($_SESSION['_xSAVE_DATA'][$_ID])){

		if(isset($_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL'])){

			if( ($_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL']['timetag']+$_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL']['timelimit']) >= time() ){
				return 1;
			}else{
				return 0;
			}
		}else{
			return 1;
		}
	}else{
		return 1;
	}
}


function xGetData($_ID){

	if(!isset($_SESSION['_xSAVE_DATA'][$_ID])){
		return null;
	}

	if(is_array($_SESSION['_xSAVE_DATA'][$_ID])){

		if(isset($_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL'])){

			if( ($_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL']['timetag']+$_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_TIME_CTRL']['timelimit']) < time() ){
				return null;
			}else{

				$Return = $_SESSION['_xSAVE_DATA'][$_ID][$_ID.'_DATA'];
				return $Return;
			}
		}
	}

	$Return = $_SESSION['_xSAVE_DATA'][$_ID];
	return $Return;
}

function isWindows(){

	if(isset($_SERVER["WINDIR"]) || isset($_SERVER["windir"]))
		return true;
	else
		return false;
}

function generateRndCode($_RndLength, $_RndCodes = 'qwertyuiopasdfghjklzxcvbnm0123456789'){

	$l_name='';
	$top = strlen($_RndCodes)-1;
	srand((double) microtime()*1000000);
	for($j=0; $j<$_RndLength; $j++)$l_name .= $_RndCodes{rand(0,$top)};
	return $l_name;
}

define('PATH_DELIMITER', isWindows()?';':':');
?>