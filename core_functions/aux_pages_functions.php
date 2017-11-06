<?php

?><?php
// *****************************************************************************
// Purpose
// Inputs
// Remarks
// Returns
function auxpgGetAllPageAttributes()
{
 	$q = db_query("select aux_page_ID, url_name, aux_page_name, ".
		" aux_page_text_type, meta_keywords, meta_description from ".AUX_PAGES_TABLE);
	$data = array();
	while( $row = db_fetch_row( $q ) )
	{
		$row["url_name"] = TransformDataBaseStringToText( $row["url_name"] );
		$row["aux_page_name"] = TransformDataBaseStringToText( $row["aux_page_name"] );
		$row["meta_keywords"]		= TransformDataBaseStringToText( $row["meta_keywords"] );
		$row["meta_description"]	= TransformDataBaseStringToText( $row["meta_description"] );
		$data[] = $row;
	}
	return $data;
}


// *****************************************************************************
// Purpose
// Inputs
// Remarks
// Returns
function auxpgGetAuxPage( $aux_page_ID )
{
	$aux_page_ID = (int) $aux_page_ID;
  	$q = db_query("select aux_page_ID, url_name,aux_page_name, aux_page_text, aux_page_text_type, ".
		 " meta_keywords, meta_description from ".AUX_PAGES_TABLE." where aux_page_ID=$aux_page_ID" );
	if  ( $row=db_fetch_row($q) )
	{
		$row["url_name"] = TransformDataBaseStringToText( $row["url_name"] );
		$row["aux_page_name"] = TransformDataBaseStringToText( $row["aux_page_name"] );
		if ( $row["aux_page_text_type"] == 1 )
			$row["aux_page_text"] = TransformDataBaseStringToHTML_Text( $row["aux_page_text"] );
		else
			$row["aux_page_text"] = TransformDataBaseStringToText( $row["aux_page_text"] );
		$row["meta_keywords"]		= TransformDataBaseStringToText( $row["meta_keywords"] );
		$row["meta_description"]	= TransformDataBaseStringToText( $row["meta_description"] );
	}
	return $row;
}


// *****************************************************************************
// Purpose
// Inputs
// Remarks
// Returns
function auxpgUpdateAuxPage( 	$aux_page_ID, $url_name, $aux_page_name,
				$aux_page_text, $aux_page_text_type,
				$meta_keywords, $meta_description  )
{
	$aux_page_ID = (int) $aux_page_ID;
	$url_name		= TransformStringToDataBase( $url_name );
	$aux_page_name		= TransformStringToDataBase( $aux_page_name );
	$meta_keywords		= TransformStringToDataBase( $meta_keywords );
	$meta_description	= TransformStringToDataBase( $meta_description );
	$aux_page_text		= TransformStringToDataBase( $aux_page_text );
	db_query("update ".AUX_PAGES_TABLE.
		 " set 	url_name='$url_name', ".
		 " 	aux_page_name='$aux_page_name', ".
		 " 	aux_page_text='$aux_page_text', ".
		 " 	aux_page_text_type=$aux_page_text_type, ".
		 " 	meta_keywords='$meta_keywords', ".
		 " 	meta_description='$meta_description' ".
		 " where aux_page_ID = $aux_page_ID");
}

// *****************************************************************************
// Purpose
// Inputs
// Remarks
// Returns
function auxpgAddAuxPage( 	$aux_page_name,$url_name,
				$aux_page_text, $aux_page_text_type,
				$meta_keywords, $meta_description  )
{
	$url_name		= TransformStringToDataBase( $url_name );
	$aux_page_name		= TransformStringToDataBase( $aux_page_name );
	$meta_keywords		= TransformStringToDataBase( $meta_keywords );
	$meta_description	= TransformStringToDataBase( $meta_description );
	$aux_page_text		= TransformStringToDataBase( $aux_page_text );
	db_query( "insert into ".AUX_PAGES_TABLE.
		" ( url_name, aux_page_name, aux_page_text, aux_page_text_type, meta_keywords, meta_description )  ".
		" values( '$url_name','$aux_page_name', '$aux_page_text', $aux_page_text_type, ".
		" '$meta_keywords', '$meta_description' ) " );
}


// *****************************************************************************
// Purpose
// Inputs
// Remarks
// Returns
function auxpgDeleteAuxPage( $aux_page_ID )
{
	$aux_page_ID = (int) $aux_page_ID;
	db_query("delete from ".AUX_PAGES_TABLE.
		" where aux_page_ID=$aux_page_ID");
}


?>