 <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>

<head>

<script type="text/javascript" src="./js/prototype.js"></script>

<script>

  var gUrl = './cache-update.php';  

    function togglevis_img_in_div(DIV)

  {

   // alert(DIV);

   

    var div_elem = document.getElementById(DIV);

    div_elem.innerHTML ='<span style="padding-left: 0px;"><img width="16" height="16"  src="/images/loading_ajax.gif" border="0" /></span>';

  }

function enable(FROM, SHAG) // ----------

  {

	//alert(FROM);

  	//alert(SHAG);

		 //alert(par_two);

		 //alert(par_three);

	//alert(ID_CAT);

	//alert(status);

	//exit;

  	// var div_elem = document.getElementById('img_cache');

//if((div_elem !== null) or (div_elem !== undefined))

	//if(div_elem !== null)

//	{	

	//alert(div_elem.src);

	//alert(httphost+'/images/plus.jpg');

	     		//div_elem.src = '/images/minus.jpg';

	     		//changeDisplayState_tree('podmenu_'+ID_CAT);

		  		togglevis_img_in_div('el_cache');

	//exit;

			  	//document.getElementById('podmenu_'+ID_CAT).innerHTML ='';	

			   // var com = ID_CAT;

			   //alert(STATUS);

				       var today = new Date();

				       var time  =   today.getTime();

			    var myAjax = new Ajax.Updater('el_cache', gUrl, {method: 'get', asynchronous: true, parameters: {from: FROM, times:time, shag:SHAG}, evalScripts: true});     	

	//}

  }



-->

</script>

<title>Обновление кеша сайта</title>

</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="padding:50;">



To update cache please press button: <br>
<!-- enable(1,5) - тут 1- с 1 файла начать, 5 - это шаг  -->
<span id="el_cache"><img border="0" onclick="enable(8500,5)" title="" src="/images/backend/enable-no.jpg" id="img_cache"></span>

<div id="cache_info"> INFO:</div>

</body>





</html>