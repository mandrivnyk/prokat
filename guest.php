<?
$email = $_POST['email'];
$name = $_POST['name'];
$msg = $_POST['msg'];


$files = "guest.txt";
$qq=50;

if ($email == "") { $email = "нет"; }
$msg=substr($msg,0,999);
$email=substr($email,0,39);
$name=substr($name,0,39);

if ($msg!= "" && $name!= "") {
$time = Date("h:i:M:d");
$soo = "  \n<b>   $time $name  <a href=mailto: $email  >  $email   </a>  </b><br>  $msg  <hr>  ";
$fp = fopen($files, "a+");
$fw = fwrite($fp, $soo);
fclose($fp); }

$lines = file($files);
$a = count($lines);
$u = $a - $qq;
for($i = $a; $i >= 0;$i--)
 { 
 	echo $lines[$i];
 }
?>