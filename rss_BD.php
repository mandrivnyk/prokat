<?


 
  @ $db=mysql_connect('db1.ho.ua','prokat','1248421684');
if(!$db)
{
  echo 'Ошибка: не удалось установить соединение с базой данных.
        Пожалуйста, повторите попытку позже.';
  exit;
}
else 
//echo 'ok';
 mysql_select_db( 'prokat');
 //SQL = "SELECT * FROM RSS";
 $query1  = "SELECT * FROM rss";
                                $result1 = mysql_query($query1);
	                	if (!$result1)
                        		echo 'Oshibka $result1';
                               
	                 	//if (!$num_result)
    		                    //    echo 'Oshibka $num_result = mysql_num_rows($result)';
	                        $row1 = mysql_fetch_array($result1);
	                    echo "<pre>";    print_r($row1); echo "</pre>";
	                    $row1['data'] = date('d-m-Y', $row1['data']);
	                    echo "<pre>";    print_r($row1); echo "</pre>";
//$time = time();
?>