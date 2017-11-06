
<head>

<script type="text/javascript" src="./js/prototype.js"></script>

<script>
<?php
$num_rows= 0;
$fp = fopen('./images/pars/caft_akc.txt', "r"); // Открываем файл в режиме чтения

		if ($fp)
		{   
			while (!feof($fp))
			{
				$url = trim(fgets($fp, 4096));
				//echo $url.'\n';
				$num_rows++;
			}
		}
		else echo "Ошибка при открытии файла";
		fclose($fp);
		
		echo 'var num_rows = '.$num_rows.';'; 

?>
//alert(num_rows);
  var gUrl = './pars.php';  

    function togglevis_img_in_div(DIV)

  {

   // alert(DIV);

    var div_elem = document.getElementById(DIV);

    div_elem.innerHTML ='<span style="padding-left: 0px;"><img width="16" height="16"  src="/images/loading_ajax.gif" border="0" /></span>';

  }

function pars(CURRENT, ALL) // ----------

  {
		 togglevis_img_in_div('el_pars');
		 var today = new Date();
	     var time  =   today.getTime();
		 var myAjax = new Ajax.Updater('el_pars', gUrl, {method: 'get', asynchronous: true, parameters: {times:time, current:CURRENT, all:ALL}, evalScripts: true});     	


  }

-->

</script>

<title>Парсинг картинок из сайтов по списку Гугла и сохранение в директорию</title>

</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="padding:50;">

To pars please press button: <br>

<!-- enable(1,5) - тут 1- с 1 файла начать, 5 - это шаг  -->

<span id="el_pars"><img border="0" onclick="pars(1, num_rows)" title="" src="/images/backend/enable-no.jpg" id="img_pars"></span>

<div id="pars_info"> INFO:</div>

</body>

</html>

