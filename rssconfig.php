<?php
header('Content-type: text/xml; charset=windows-1251');

  @ $db=mysql_connect('db1.ho.ua','prokat','1248421684');
if(!$db)
{
  echo 'Ошибка: не удалось установить соединение с базой данных.
        Пожалуйста, повторите попытку позже.';
  exit;
}
else 

 mysql_select_db( 'prokat');

 $query1  = "SELECT * FROM rss";
                                $result1 = mysql_query($query1);
	                	if (!$result1)
                        		echo 'Oshibka $result1';

	                        $num_result = mysql_num_rows($result1);
           

print "<?xml version='1.0'?>
			<rss version='2.0'>
			<channel>
    		<title>Прокат и продажа снаряги</title>
    		<link>http://prokat.ho.com.ua</link>
    		<description>Распрадажа палаток</description>
    		<language>en-us</language>
    		<pubDate> Thu, 17 Jul 2008 18:00:00 GMT</pubDate>
		    <lastBuildDate>Tue, 10 Jun 2003 09:41:01 GMT</lastBuildDate>
    		<docs>http://prokat.ho.com.ua/rss</docs>
    		<generator>BY EASIER</generator>
    		<managingEditor>easier@ukr.net</managingEditor>
    		<webMaster>easier@ukr.net</webMaster>";



   
for ($i=0; $i<$num_result; $i++)
		{
  			//obrabotka resultata
  			$row1 = mysql_fetch_array($result1);
	//echo "<pre>";    print_r($row1); echo "</pre>";
	                    $row1['data'] = date('d-m-Y', $row1['data']);
	                   
   						$title = $row1['title'];
	                    $rss_link = $row1['rss_link'];
	                    $rss_desc = $row1['rss_desc'];
	                    $data = $row1['data'];
	                    
print   "	<item>
      		<title>{$title}</title>
      		<link>http://{$rss_link}</link>
      		<description>{$rss_desc} </description>
      		<pubDate>{$data}</pubDate>
      		<guid>http://{$rss_link}</guid>
    		</item>";
		
		
		}

print "
			</channel>
			</rss>";
?>
