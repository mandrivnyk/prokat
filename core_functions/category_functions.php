<?php

// *****************************************************************************

// Purpose	insert predefined root category into CATEGORIES_TABLE

// Inputs

// Remarks	this function is called in CreateTablesStructureXML, ID of root category equals to 1

// Returns	nothing

function catInstall()

{

	db_query("insert into ".CATEGORIES_TABLE."( name, parent, categoryID )".

			"values( 'ROOT', NULL, 1 )");

}



	//frequently used category tree functions



function processCategories($level, $path, $sel)

{

	//returns an array of categories, that will be presented by the category_navigation.tpl template



	//$categories[] - categories array

	//$level - current level: 0 for main categories, 1 for it's subcategories, etc.

	//$path - path from root to the selected category (calculated by calculatePath())

	//$sel -- categoryID of a selected category



	//returns an array of (categoryID, name, level)



	//category tree is being rolled out "by the path", not fully



	$out = array();

	$cnt = 0;



	$parent = $path[$level]["parent"];

	if ( $parent == "" || $parent == null )

		$parent = "NULL";

	$q = db_query("select categoryID, name from ".CATEGORIES_TABLE.

		" where parent=".$path[$level]["parent"]." order by sort_order, name")

			or die (db_error());

	while ($row = db_fetch_row($q))

	{

		$out[$cnt][0] = $row["categoryID"];

		$out[$cnt][1] = $row["name"];

		$out[$cnt][2] = $level;

		$cnt++;



		//process subcategories?

		if ($level+1<count($path) && $row["categoryID"] == $path[$level+1])

		{

			$sub_out = processCategories($level+1,$path,$sel);

			//add $sub_out to the end of $out

			for ($j=0; $j<count($sub_out); $j++)

			{

				$out[] = $sub_out[$j];

				$cnt++;

			}

		}

	}

	return $out;

} //processCategories



function fillTheCList($parent,$level) //completely expand category tree

{



	$q = db_query("SELECT categoryID, name, products_count, products_count_admin, parent FROM ".

		CATEGORIES_TABLE." WHERE categoryID<>0 and parent=$parent ORDER BY sort_order, name") or die (db_error());

	$a = array(); //parents

	while ($row = db_fetch_row($q))

	{

		$row[5] = $level;

		$a[] = $row;

		//process subcategories

		$b = fillTheCList($row[0],$level+1);

		//add $b[] to the end of $a[]

		for ($j=0; $j<count($b); $j++)

		{

			$a[] = $b[$j];

		}

	}

	return $a;



} //fillTheCList



function _recursiveGetCategoryCompactCList( $path, $level )

{

	$q = db_query( "select categoryID, parent, url_name, name, enable, picture from ".CATEGORIES_TABLE.

				" where parent=".$path[$level-1]["categoryID"]." AND enable=1 order by sort_order, name " );

	$res = array();

	$selectedCategoryID = null;

	while( $row=db_fetch_row($q) )

	{



		$row["level"] = $level;

		$res[] = $row;

		if ( $level <= count($path)-1 )

		{

			if ( (int)$row["categoryID"] == (int)$path[$level]["categoryID"] )

			{

				$selectedCategoryID = $row["categoryID"];

				$array = _recursiveGetCategoryCompactCList( $path, $level+1 );

				foreach( $array as $val )

					$res[] = $val;

			}

		}

	}



 	return $res;

}





function catExpandCategory( $categoryID, $sessionArrayName )

{

	$existFlag = false;

	foreach( $_SESSION[$sessionArrayName] as $key => $value )

		if ( $value == $categoryID )

		{

			$existFlag = true;

			break;

		}

	if ( !$existFlag )

		$_SESSION[$sessionArrayName][] = $categoryID;



}



function catShrinkCategory( $categoryID, $sessionArrayName )

{

	foreach( $_SESSION[$sessionArrayName] as $key => $value )

	{

		if ( $value == $categoryID )

			unset( $_SESSION[$sessionArrayName][$key] );

	}

}





function catGetCategoryCompactCList( $selectedCategoryID )

{

	$path = catCalculatePathToCategory( $selectedCategoryID );

	$res = array();

	$res[] = array( "categoryID" => 1, "parent" => null,

					"name" => ADMIN_CATEGORY_ROOT, "level" => 0 );

	$q = db_query( "select categoryID,  parent,url_name,  name, picture from ".CATEGORIES_TABLE.

				" where parent=1 AND enable =1 ".

				" order by sort_order, name " );



	while( $row = db_fetch_row($q) )

	{

		$row["level"] = 1;

		$res[] = $row;

		if ( count($path) > 1 )

		{

			if ( $row["categoryID"] == $path[1]["categoryID"] )

			{

				$array = _recursiveGetCategoryCompactCList( $path, 2 );

				foreach( $array as $val )

					$res[] = $val;

			}

		}

	}

	return $res;

}







// *****************************************************************************

// Purpose	gets category tree to render it on HTML page

// Inputs

//			$parent - must be 0

//			$level	- must be 0

//			$expandedCategoryID_Array - array of category ID that expanded

// Remarks

//			array of item

//				for each item

//					"products_count"			-		count product in category including

//															subcategories excluding enabled product

//					"products_count_admin"		-		count product in category

//															without count product subcategory

//					"products_count_category"	-

// Returns	nothing

function _recursiveGetCategoryCList( $parent, $level, $expandedCategoryID_Array, $_indexType = 'NUM', $_countEnabledProducts = false )

{

	$q = db_query("SELECT categoryID, name, products_count, ".

			"products_count_admin, parent, enable FROM ".

			CATEGORIES_TABLE.

			" WHERE parent=$parent ORDER BY sort_order, name") or die (db_error());

	$result = array(); //parents

	while ($row = db_fetch_row($q))

	{

		$row["level"] = $level;

		$row["ExpandedCategory"] = false;

		if ( $expandedCategoryID_Array != null )

		{

			foreach( $expandedCategoryID_Array as $categoryID )

			{

				if ( (int)$categoryID == (int)$row["categoryID"] )

				{

					$row["ExpandedCategory"] = true;

					break;

				}

			}

		}

		else

			$row["ExpandedCategory"] = true;



		$row["products_count_category"] = catGetCategoryProductCount( $row["categoryID"], $_countEnabledProducts );



		$count = db_query("select count(categoryID) from ".CATEGORIES_TABLE.

				" where categoryID<>0 AND parent=".$row["categoryID"] );

		$count = db_fetch_row($count);

		$count = $count[0];



		$row["ExistSubCategories"] = ( $count != 0 );



		if($_indexType=='NUM')

			$result[] = $row;

		elseif ($_indexType=='ASSOC')

			$result[$row['categoryID']] = $row;





		if ( $row["ExpandedCategory"] )

		{

			//process subcategories

			$subcategories = _recursiveGetCategoryCList( $row["categoryID"],

				$level+1, $expandedCategoryID_Array, $_indexType, $_countEnabledProducts  );



			if($_indexType=='NUM'){



				//add $subcategories[] to the end of $result[]

				for ($j=0; $j<count($subcategories); $j++)

					$result[] = $subcategories[$j];

			}

			elseif ($_indexType=='ASSOC'){



				//add $subcategories[] to the end of $result[]

				foreach ($subcategories as $_sub){



					$result[$_sub['categoryID']] = $_sub;

				}

			}



		}

	}

	return $result;

}





// *****************************************************************************

// Purpose	gets category tree to render it on HTML page

// Inputs

// Remarks

// Returns	nothing

function catGetCategoryCList( $expandedCategoryID_Array = null, $_indexType='NUM', $_countEnabledProducts = false )

{

	return _recursiveGetCategoryCList( 1, 0, $expandedCategoryID_Array, $_indexType, $_countEnabledProducts );

}







// *****************************************************************************

// Purpose	gets product count in category

// Inputs

// Remarks  this function does not keep in mind subcategories

// Returns	nothing

function catGetCategoryProductCount( $categoryID, $_countEnabledProducts = false )

{

	$categoryID = (int)$categoryID;

	if (!$categoryID) return 0;



	$res = 0;

	$sql = "

		SELECT count(*) FROM ".PRODUCTS_TABLE."

		WHERE categoryID=$categoryID".($_countEnabledProducts?" AND enabled<>0":"")."

	";

	$q = db_query($sql);

	$t = db_fetch_row($q);

	$res += $t[0];

	if($_countEnabledProducts)

		$sql = "

			SELECT COUNT(*) FROM ".PRODUCTS_TABLE." AS prot

			LEFT JOIN ".CATEGORIY_PRODUCT_TABLE." AS catprot

			ON prot.productID=catprot.productID

			WHERE catprot.categoryID='{$categoryID}' AND prot.enabled<>0

		";

	else

		$sql = "

			select count(*) from ".CATEGORIY_PRODUCT_TABLE.

			" where categoryID=$categoryID

		";

	$q1 = db_query($sql);

	$row = db_fetch_row($q1);

	$res += $row[0];

	return $res;

}





function update_products_Count_Value_For_Categories($parent)

{



	$q = db_query("SELECT categoryID FROM ".CATEGORIES_TABLE.

		" WHERE parent=$parent AND categoryID<>1") or die (db_error());

	$cnt = array();

	$cnt["admin_count"] = 0;

	$cnt["customer_count"] = 0;



	// process subcategories

	while( $row=db_fetch_row($q) )

	{

		$t = update_products_Count_Value_For_Categories( $row["categoryID"] );

		$cnt["admin_count"]		+= $t["admin_count"];

		$cnt["customer_count"]  += $t["customer_count"];

	}



	// to administrator

	$q = db_query("SELECT count(*) FROM ".PRODUCTS_TABLE.

			" WHERE categoryID=$parent");

	$t = db_fetch_row($q);

	$cnt["admin_count"] += $t[0];



	// to customer

	$q = db_query("SELECT count(*) FROM ".PRODUCTS_TABLE.

			" WHERE categoryID=$parent AND enabled=1");

	$t = db_fetch_row($q);

	$cnt["customer_count"] += $t[0];

	$q1 = db_query("select productID, categoryID from ".CATEGORIY_PRODUCT_TABLE.

			" where categoryID=$parent");



	$admin_plus = 0;

	while( $row = db_fetch_row($q1) )

	{



		$q2 = db_query("select productID, categoryID from ".PRODUCTS_TABLE.

				" where productID=".$row["productID"]." AND enabled=1 " );

		$res = db_fetch_row($q2);



		if(!$res){



			if ($res['categoryID'] == $parent)$admin_plus++;

			continue;

		}



		if ($res['categoryID'] == $parent){



			$cnt["admin_count"]++;

			$cnt["customer_count"] ++;

		}



	}



	$cnt["admin_count"] += $admin_plus;



	$sql = "UPDATE ".CATEGORIES_TABLE.

			" SET products_count=".$cnt["customer_count"].", products_count_admin=".

				$cnt["admin_count"]." ".

			" WHERE categoryID=".$parent;

	db_query($sql) or die (db_error());

	catCountProductDuplicates($parent);



	return $cnt;

}





function update_parent_products_skidka($_CategoryID, $skidka_old, $skidka)
{
	//--------------��������� ���� "������" ��� �������-----------------------
	$sql = "UPDATE ".PRODUCTS_TABLE."
			SET skidka=".$skidka."
			WHERE in_stock>0 AND categoryID=".intval($_CategoryID)." ";
	db_query($sql);


	if(($skidka >0) AND ($skidka_old>0))
	{
		//------������� ���� �� ������ ������-----------------
		$sql = "UPDATE ".PRODUCTS_TABLE."
				SET Price=list_price, list_price =0
				WHERE list_price<>0 AND categoryID=".intval($_CategoryID)." ";
		db_query($sql);
	}
	

	if($skidka >0)
	{
		//--------------��������� �������������� ���� ������ � ���� list_price ��� ��� ������� ������� ���� ��� ������
		$sql = "UPDATE ".PRODUCTS_TABLE."
				SET list_price=Price
				WHERE in_stock>0 AND list_price=0 AND  categoryID=".intval($_CategoryID)." AND producer = 'Craft' ";
				//WHERE in_stock>0 AND list_price=0 AND producer='Craft' AND  categoryID=".intval($_CategoryID)." ";
		db_query($sql);
		//--------------������������ ���� ������ � ������ ������ � ������ ��� � ���� !! ���������� ���� �� �������  ����� ������� �����!!!
		$sql = "UPDATE ".PRODUCTS_TABLE."
				SET Price=CEILING((Price*(100-skidka))/100)
				WHERE in_stock>0 AND categoryID=".intval($_CategoryID)." AND producer = 'Craft'";
		db_query($sql);
			/*WHERE in_stock>0 AND categoryID=".intval($_CategoryID)." AND producer <> 'Craft' ";
		db_query($sql);
		$sql = "UPDATE ".PRODUCTS_TABLE."
				SET Price=CEILING((Price*95)/100)
				WHERE in_stock>0 AND categoryID=".intval($_CategoryID)." AND producer = 'Craft' ";
		db_query($sql);*/
	}
	elseif ($skidka == 0) 
	{
		$sql = "UPDATE ".PRODUCTS_TABLE."
				SET Price=list_price, list_price =0
				WHERE list_price<>0 AND categoryID=".intval($_CategoryID)." ";
		db_query($sql);
	}
	//  {math equation="ceil((x*(100-y))/100)" x=$product_info.PriceWithOutUnit  y=$product_info.skidka} ���
	//echo $sql;

	//die();	
}



function catCountProductDuplicates ($_CategoryID){



	$SubCategories = catGetSubCategories($_CategoryID);

	$SubCategories[] = $_CategoryID;

	$sql = "

		SELECT prod.enabled, count(distinct prod.productID) FROM ".CATEGORIY_PRODUCT_TABLE." as catprod

		LEFT JOIN ".PRODUCTS_TABLE." as prod ON catprod.productID = prod.productID

		WHERE catprod.categoryID IN (".implode(", ",$SubCategories).") AND prod.categoryID NOT IN (".implode(", ",$SubCategories).")

		GROUP BY prod.enabled

	";

	$Result = db_query($sql);



	$cntA = 0;

	$cntU = 0;



	while ($Row = db_fetch_row($Result)){



		if(intval($Row[0])>0)

			$cntU = $Row[1];

		else

			$cntA = $Row[1];

	}

	$cntA += $cntU;



	if($cntA || $cntU){



		$sql = "

			UPDATE ".CATEGORIES_TABLE."

			SET products_count=products_count+{$cntU}, products_count_admin=products_count_admin+{$cntA}

			WHERE categoryID=".intval($_CategoryID)."

		";

		db_query($sql);

	}

}



/**

 * update products_count and products_count_admin if necessary

 *

 * @param integer $_ProductID

 * @param integer $_ProdOrCat

 * @param integer $_ProdAddCat

 */

function catUpdateProductCount($_ProductID, $_ProdAddCat, $_State = 1, $_SourceCategoryID = 0){



	$Product = GetProduct ($_ProductID);

	$subCategories = catGetSubCategories ($_ProdAddCat);

	$subCategories[] = 1;

	if($_SourceCategoryID)

		$subCategories[] = $_ProdAddCat;

	$_State = intval($_State);



	$sql = "

		SELECT 1 FROM ".CATEGORIY_PRODUCT_TABLE."

		WHERE productID='{$_ProductID}' AND categoryID IN (".implode(", ", $subCategories).") AND categoryID<>".intval($_SourceCategoryID)."

	";

	if(!db_fetch_row(db_query($sql)) && !in_array($Product['categoryID'], $subCategories)){



		$sql = "

			UPDATE ".CATEGORIES_TABLE."

			SET products_count=products_count".($Product['enabled']?"+{$_State}":"").", products_count_admin=products_count_admin+{$_State}

			WHERE categoryID='".intval($_ProdAddCat)."'

		";

		db_query($sql);



		$Category = catGetCategoryById($_ProdAddCat);

		if($_SourceCategoryID == 0)

			$_SourceCategoryID = $_ProdAddCat;

		catUpdateProductCount($_ProductID, $Category['parent'], $_State, $_SourceCategoryID);

	}

}



// *****************************************************************************

// Purpose	get subcategories by category id

// Inputs   $categoryID

//				parent category ID

// Remarks  get current category's subcategories IDs (of all levels!)

// Returns	array of category ID

function catGetSubCategories( $categoryID )

{

	$q = db_query("select categoryID from ".CATEGORIES_TABLE.

		" where categoryID<>0 and parent='$categoryID'") or die (db_error());

	$r = array();

	while ($row = db_fetch_row($q))

	{

		$a = catGetSubCategories($row[0]);

		for ($i=0;$i<count($a);$i++) $r[] = $a[$i];

		$r[] = $row[0];

	}

	return $r;

}





// *****************************************************************************

// Purpose	get subcategories by category id

// Inputs   	$categoryID

//				parent category ID

// Remarks  	get current category's subcategories IDs (of all levels!)

// Returns	array of category ID

function catGetSubCategoriesSingleLayer( $categoryID )

{

	$q = db_query("SELECT categoryID, name, products_count FROM ".

			CATEGORIES_TABLE." WHERE parent='$categoryID' order by sort_order, name");

	$result = array();

	while ($row = db_fetch_row($q))

		$result[] = $row;

	return $result;

}







// *****************************************************************************

// Purpose	get category by id

// Inputs   $categoryID

//				- category ID

// Remarks

// Returns

function catGetCategoryById($categoryID)

{

	$categoryID = (int)$categoryID;

	$q = db_query("select categoryID, url_name, title_one, title_two, name, head_text,seo_text, parent, products_count, description, picture, ".

		" products_count_admin, sort_order, skidka, viewed_times".

		", allow_products_comparison".

		", allow_products_search".

		", show_subcategories_products, meta_description, meta_keywords ".

		"  from ".CATEGORIES_TABLE.

		" where categoryID=$categoryID");

	return db_fetch_row($q);

}



// *****************************************************************************

// Purpose	gets category META information in HTML form

// Inputs   $categoryID

//				- category ID

// Remarks

// Returns

function catGetMetaTags($categoryID)

{

	$categoryID = (int) $categoryID;



	$q = db_query( "select meta_description, meta_keywords from ".

		CATEGORIES_TABLE." where categoryID=".$categoryID );

	$row = db_fetch_row($q);

	$meta_description	= TransformDataBaseStringToText( trim($row["meta_description"]) );

	$meta_keywords		= TransformDataBaseStringToText( trim($row["meta_keywords"]) );



	$res = "";

	

if(isset($_GET['offset']))

	$offset = ' offset'.$_GET['offset'];

elseif (isset($_GET['show_all'])) 	

{

	//exit();

	$offset = ' show all';

}

elseif(isset($_GET['sort']))

{

	$offset = ' sort '.$_GET['direction'];

	

}

else 

	$offset = '';



	if  ( $meta_description != "" )

		$res .= "<meta name=\"Description\" content=\"".str_replace("\"","&quot;",$meta_description). $offset." \">\n";

	if  ( $meta_keywords != "" )

		$res .= "<meta name=\"KeyWords\" content=\"".str_replace("\"","&quot;",$meta_keywords). $offset."\" >\n";



	return $res;

}



// *****************************************************************************

// Purpose	adds product to appended category

// Inputs

// Remarks      this function uses CATEGORIY_PRODUCT_TABLE table in data base instead of

//			PRODUCTS_TABLE.categoryID. In CATEGORIY_PRODUCT_TABLE saves appended

//			categories

// Returns	array of item

//			"categoryID"

//			"category_name"

function catGetAppendedCategoriesToProduct( $productID )

{

 	$q = db_query( "select ".CATEGORIES_TABLE.".categoryID as categoryID, name as category_name ".

		" from ".CATEGORIY_PRODUCT_TABLE.", ".CATEGORIES_TABLE." ".

		" where ".CATEGORIY_PRODUCT_TABLE.".categoryID = ".CATEGORIES_TABLE.".categoryID ".

		" AND productID = $productID"  );

	$data = array();

	while( $row = db_fetch_row( $q ) )

		$data[] = $row;

	return $data;

}



// *****************************************************************************

// Purpose	adds product to appended category

// Inputs

// Remarks      this function uses CATEGORIY_PRODUCT_TABLE table in data base instead of

//			PRODUCTS_TABLE.categoryID. In CATEGORIY_PRODUCT_TABLE saves appended

//			categories

// Returns	true if success, false otherwise

function catAddProductIntoAppendedCategory($productID, $categoryID)

{

	$q = db_query("select count(*) from ".CATEGORIY_PRODUCT_TABLE.

		" where productID=$productID AND categoryID=$categoryID");

	$row = db_fetch_row( $q );



 	$q1 = db_query( "select categoryID from ".PRODUCTS_TABLE.

			" where productID=$productID");

	$row1 = db_fetch_row( $q1 );

	$basic_categoryID = $row1["categoryID"];



	if ( !$row[0] && $basic_categoryID != $categoryID )

	{

		db_query("insert into ".CATEGORIY_PRODUCT_TABLE.

			"( productID, categoryID ) ".

			"values( $productID, $categoryID )" );
			
echo "insert into ".CATEGORIY_PRODUCT_TABLE."( productID, categoryID ) "."values( $productID, $categoryID )";

	return true;

	}

	else

		return false;

}



// *****************************************************************************

// Purpose	removes product to appended category

// Inputs

// Remarks      this function uses CATEGORIY_PRODUCT_TABLE table in data base instead of

//			PRODUCTS_TABLE.categoryID. In CATEGORIY_PRODUCT_TABLE saves appended

//			categories

// Returns	nothing

function catRemoveProductFromAppendedCategory($productID, $categoryID)

{

	$productID = (int) $productID;

	$categoryID = (int) $categoryID;

	db_query("delete from ".CATEGORIY_PRODUCT_TABLE.

		" where productID = $productID AND categoryID = $categoryID");



}



// *****************************************************************************

// Purpose	calculate a path to the category ( $categoryID )

// Inputs

// Remarks

// Returns	path to category

function catCalculatePathToCategory( $categoryID )

{

	$categoryID = (int)$categoryID;

	if (!$categoryID) return NULL;



	$path = array();



	$q = db_query("select count(*) from ".CATEGORIES_TABLE.

			" where categoryID=$categoryID ");

 	$row = db_fetch_row($q);

	if ( $row[0] == 0 )

		return $path;



	$curr = $categoryID;

	do

	{

		$q = db_query("SELECT categoryID, parent, url_name, name FROM ".

			CATEGORIES_TABLE.

			" WHERE categoryID='$curr'") or die (db_error());

		$row = db_fetch_row($q);

		$path[] = $row;



	 	if ( $curr == 1 )

			break;



		$curr = $row["parent"];

	}

	while ( 1 );

	//now reverse $path

	$path = array_reverse($path);

	/*echo '<pre>';

		print_r($path);

	echo '</pre>';*/

	return $path;

}







function _deleteSubCategories( $parent )

{

	// move all product of this category to the root category

	db_query("UPDATE ".PRODUCTS_TABLE.

			" SET categoryID=1 WHERE categoryID=$parent") or die (db_error());



	$q = db_query("SELECT picture FROM ".CATEGORIES_TABLE.

			" WHERE categoryID='".$parent."' and categoryID<>0");

	$r = db_fetch_row($q);

	if ($r["picture"] && file_exists("./products_pictures/".$r["picture"]))

		unlink("./products_pictures/".$r["picture"]);





	$q = db_query("SELECT categoryID FROM ".CATEGORIES_TABLE.

				" WHERE parent=$parent and categoryID<>0") or die (db_error());

	while ($row = db_fetch_row($q))

		_deleteSubCategories( $row["categoryID"] );

	db_query("DELETE FROM ".CATEGORIES_TABLE.

		" WHERE parent=$parent and categoryID<>0") or die (db_error());



}





// *****************************************************************************

// Purpose	deletes category

// Inputs

//		 $categoryID - ID of category to be deleted

// Remarks      delete also all subcategories, all prodoctes remove into root

// Returns	nothing

function catDeleteCategory( $categoryID )

{

	_deleteSubCategories( $categoryID );



	db_query("UPDATE ".PRODUCTS_TABLE.

			" SET categoryID=1 WHERE categoryID=$categoryID");

	db_query("DELETE FROM ".CATEGORIES_TABLE.

		" WHERE parent=$categoryID and categoryID<>0") or die (db_error());

	$q = db_query("SELECT picture FROM ".CATEGORIES_TABLE.

			" WHERE categoryID='".$categoryID."' and categoryID<>0");

	$r = db_fetch_row($q);

	if ($r["picture"] && file_exists("./products_pictures/".$r["picture"]))

		unlink("./products_pictures/".$r["picture"]);



	db_query("DELETE FROM ".CATEGORIES_TABLE.

		" WHERE categoryID=$categoryID and categoryID<>0") or die (db_error());

}



function catBuildProductTree(){



	$sql = "

		SELECT

	";

}

?>