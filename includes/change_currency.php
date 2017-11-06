<?php
	// currency selection form

	if (  isset($_POST["current_currency"]) )
	{
		currSetCurrentCurrency( $_POST["current_currency"] );
		
		$url = "index.php";
		$paramGetVars = "";
		foreach( $_GET as $key => $value )
		{
			if ( $paramGetVars == "" )
				$paramGetVars .= "?".$key."=".$value;
			else 
				$paramGetVars .= "&".$key."=".$value;
		}

		Redirect( $url.$paramGetVars );
	}

?>