<?
					$url_new = '';
					$Url_arr = getUrl();
					$Url_base_str = getHtaccess_base();
					$fp = fopen('./.htaccess', 'w');
					fwrite($fp, $Url_base_str);
					for($i=0;$i<count($Url_arr);$i++)
					{
						$url_new .= $Url_arr[$i]['url_rewrite'].chr(10).chr(13);
					}
					$res = fwrite($fp, $url_new);
					fclose($fp);
?>