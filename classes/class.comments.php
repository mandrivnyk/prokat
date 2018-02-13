<?php
class comments
{
    private $commDir;
    private $dir;
    private $email;
    private $post;
    private $data;


    public  function __construct(){

        $this->_setDir($_SERVER['DOCUMENT_ROOT'].'/comments/');
        $this->_setEmail('easier@ukr.net');

        //return true;

    }

    public function SaveComment()
    {
       if(isset($_POST)){
          $this->data =  $this->_getData();
        }

        if (!is_dir($this->_getDir()))  // ---- директория -
    	{
    	    //echo '1';
    		//---создать директорию
    		if (mkdir($this->_getDir(), 0755))
    		{//echo '2';
       			$this->_saveCommentToFile($this->data['name'], $this->data['text'], $this->data['productID']);
    		}
    	  //else die('Не удалось создать директории...');
    	}
    	else
    	{//echo '3';
    	       $this->_saveCommentToFile($this->data['name'], $this->data['text'], $this->data['productID']);
    	}

    }

    public function GetComment($productID, $last)
    {

       if($productID >0)
        $this->_getCommentsFromFile($productID);

        if($last >0)
        $this->_getCommentsLast();


     /* header('Content-type: application/json');
      print json_encode($result = array(

     'mark' => true,
     'name' => 'You will go to space today',
     'minuses' => $_POST['name'] . ' ' . rand(1,5),
     'pluses' => $_POST['value'],
     'text' => $_POST['value']

     ));       */
    }

    public function _getCommentsLast()
    {
         header('Content-Type: text/html; charset=windows-1251');
         $files = scandir($this->_getDir());
         for($i=0; $i<count($files); $i++)
         {
          if(($files[$i] == '.') || ($files[$i] == '..')) continue;
           $comFiles[$i]['name'] =  $files[$i];
           $comFiles[$i]['lastmod'] =  filemtime($this->_getDir().$files[$i]);
         }

         usort($comFiles, array('comments','cmp'));
         $com_str = '';
         for($y=0;$y<3; $y++)
         {
           $productID = str_replace(".json","",$comFiles[$y]['name']);
           $com_str .=$this->_getCommentsNum($productID);
         }

        echo $com_str;


         //print_r($comFiles);
    }


  private static   function cmp($a, $b)
    {
       return ($a['lastmod'] >= $b['lastmod']) ? -1 : 1;
    }


    /* Выбрать заданное количество комментариев из файла    */
    public function _getCommentsNum($productID, $num = 2)
    {
        if(is_file($this->_getDir().$productID.".json"))
        {
            $fp = fopen($this->_getDir().$productID.".json", "r"); // Открываем файл в режиме чтения
            if ($fp)
            {

                $i=0;
                while (!feof($fp))
                {
                     $comments[$i++]  = fgets($fp, 999);
                }

               //  print_r($comments);
                  //$json_com = str_replace("[","",$json_com);
                $comments =   implode($comments);
                $comments = json_decode($comments, true) ;
              // header('Content-Type: text/html; charset=windows-1251');
               // echo "<script>alert('test'); </script>";

              //echo ' count($comments) = '.count($comments).' ';
                $com_str = '';
                for($i=0;$i<count($comments); $i++)
                {
                    $comment =   $this->_convertCommentsTo1251($comments[$i]);
                    if($i==2) break; // вытягиваем только 2 первых коммента , они же самые свежие

                    $com_str .= "<div class='comment-box-main' onclick=\"location.href='/index.php?productID=".$productID."'\"><div class='comment-head'><div class='comment-name'> ".$comment['name']."</div>";
                   $mark = '';
                    for($y=1; $y<=5; $y++)
                    {

                        if($y<=$comment['mark'])
                            $mark = $mark."<div   class='reviews-add-stars-i-sm active'></div>";
                        else
                            $mark = $mark."<div   class='reviews-add-stars-i-sm '></div>";
                    }
                    //val.text = decodeURIComponentX(val.text);
                  //  val.text = decode_utf8(val.text);
                    $com_str = $com_str.$mark."<div class='comment-date'> ".$comment['date']."</div></div><div class='comment-plus'>";
                    $com_str = $com_str."<span class='comment-plus-head'>Плюсы:</span> ".$comment['pluses']."</div><div class='comment-minus'><span class='comment-minus-head'>Минусы:</span> ".$comment['minuses']."</div>";
                    $com_str = $com_str."<div class='comment-text'><span class='comment-text-head'>Отзыв/Вопрос:</span> ".$comment['text']."</div>";
                    $com_str = $com_str."<div class='comment-text'><span class='comment-text-ref'>Перейти на страницу товара >></span></div></div>";

                }
               // echo $com_str;
                return $com_str;


            }
            else
                echo 'Can not open file';
        }
        else
            echo 'Нет отзывов, возможно ваш будет первым';

    }

    public function _convertCommentsTo1251($comments)
    {
       // print_r($comments);
                  $comments['name'] = @iconv("UTF-8","windows-1251",$comments['name']);
                  $comments['pluses'] = @iconv("UTF-8","windows-1251",$comments['pluses']);
                  $comments['minuses'] = @iconv("UTF-8","windows-1251",$comments['minuses']);
                  $comments['text'] = @iconv("UTF-8","windows-1251",$comments['text']);
                  return $comments;
    }

    public function utf8_fopen_read($fileName) {
//        $fc = iconv( 'utf-8', 'windows-1251//IGNORE', file_get_contents($fileName));
        $fc = file_get_contents($fileName);
        $handle=fopen("php://memory", "rw");
        fwrite($handle, $fc);
        fseek($handle, 0);
        return $handle;
    }


    public function _getCommentsFromFile($productID)
    {

        header('Content-Type: text/html; charset=windows-1251');
        // echo 'tyt1 = '."comments/".$productID.".json";
        //$fp = fopen($this->_getDir()."/1761.json", "r"); // Открываем файл в режиме чтения
        if(is_file($this->_getDir().$productID.".json"))
        {
           // $fp = fopen($this->_getDir().$productID.".json", "r"); // Открываем файл в режиме чтения
            $fp = $this->utf8_fopen_read($this->_getDir().$productID.".json");
            if ($fp)
            {

                 $i=0;
                while (!feof($fp))
                {
                     $comments[$i++]  = fgets($fp, 999);
                }
                  //$json_com = str_replace("[","",$json_com);
                //print_r($comments) ;
                $comments =   implode($comments);
                //print_r("----------------------------".$comments) ;
                //echo $comments;
               //$comments = utf8_encode($comments);
                  $comments = json_decode($comments, true) ;

//               print_r("=========1=====================");
//                print_r(json_last_error());
//               print_r($comments) ;
               //phpinfo();
              // header('Content-Type: text/html; charset=windows-1251');
               // echo "<script>alert('test'); </script>";

                $com_str = '';
                for($i=0;$i<count($comments); $i++)
                {
                    $comment =   $this->_convertCommentsTo1251($comments[$i]);



                    $com_str .= "<div class='comment-box'><div class='comment-head'><div class='comment-name'> ".$comment['name']."</div>";
                   $mark = '';
                    for($y=1; $y<=5; $y++)
                    {
                        if($y<=$comment['mark'])
                            $mark = $mark."<div   class='reviews-add-stars-i-sm active'></div>";
                        else
                            $mark = $mark."<div   class='reviews-add-stars-i-sm '></div>";
                    }
                    //val.text = decodeURIComponentX(val.text);
                  //  val.text = decode_utf8(val.text);
                    $com_str = $com_str.$mark."<div class='comment-date'> ".$comment['date']."</div></div><div class='comment-plus'>";
                    $com_str = $com_str."<span class='comment-plus-head'>Плюсы:</span> ".$comment['pluses']."</div><div class='comment-minus'><span class='comment-minus-head'>Минусы:</span> ".$comment['minuses']."</div>";
                    $com_str = $com_str."<div class='comment-text'><span class='comment-text-head'>Отзыв/Вопрос:</span> ".$comment['text']."</div></div>";

                }
                echo $com_str;


            }
            else
                echo 'Can not open file';
        }
        else
            echo 'Нет отзывов, возможно ваш будет первым';
    }


     public function CleanText()
    {
       // strip_tags
    }

    public function _sendComment()
    {
        $subject = 'Комментарий/отзыв';
        //<a href="mailto:someone@example.com?Subject=Hello%20again" target="_top">Send Mail</a>
        $text = "email: ".$this->data['email']." \r\n mark = ".$this->data['mark']."\r\n name= ".$this->data['name']."\r\n minuses = ".$this->data['minuses']." \r\n pluses = ".$this->data['pluses']."\r\n text = ".$this->data['text']."\r\n productID = ".$this->data['productID']."\r\n productUrl = http://www.prokat.ho.com.ua/".$this->data['productUrl'];
        //$headers  = 'MIME-Version: 1.0' . "\r\n";
      //  $headers .= 'Content-type: text/html;' . "\r\n";
      $headers = '';
        if(mail($this->email, $subject, $text, $headers))
            echo 'LETTER SENT SUCCESSFUL';
    }

    function writeFile($filename,$content) {
        $f=fopen($filename,"w");
        # Now UTF-8 - Add byte order mark
        //fwrite($f, pack("CCC",0xef,0xbb,0xbf));
        fwrite($f,$content);
        fclose($f);
    }

    public function _saveCommentToFile()
    {
        echo 'teste';
       // echo $this->_getDir().$this->data['productID'].'.html';
//        $this->data['minuses'] = mb_convert_encoding($this->data['minuses'], 'UTF-8', 'OLD-ENCODING');
//        $this->data['pluses'] = mb_convert_encoding($this->data['pluses'], 'UTF-8', 'OLD-ENCODING');
//        $this->data['text'] = mb_convert_encoding($this->data['text'], 'UTF-8', 'OLD-ENCODING');
        if( !file_exists($this->_getDir().$this->data['productID'].'.json'))
                {
                    $str = "[{\"date\":\"".date('d-m-Y  H:m:s')."\",\"mark\":".$this->data['mark'].",\"name\":\"".$this->data['name']."\",\"minuses\":\"".$this->data['minuses']."\",\"pluses\":\"".$this->data['pluses']."\",\"text\":\"".$this->data['text']."\",\"email\":\"".$this->data['email']."\"}]";

                    $this->writeFile($this->_getDir().$this->data['productID'].'.json',$str);
                }
                else {
                  //echo ' 5';
                    $fp = fopen($this->_getDir().$this->data['productID'].'.json', "r"); // ("r" - считывать "w" - создавать "a" - добавлять к тексту), мы создаем файл
                     if ($fp)
                        {   $i=0;
                            while (!feof($fp))
                            {
                                $comment[$i++] = fgets($fp, 999);

                            }
                        }
                   fclose ($fp);
                    unlink($this->_getDir().$this->data['productID'].'.json');
                    $fp = fopen($this->_getDir().$this->data['productID'].'.json', "w"); // ("r" - считывать "w" - создавать "a" - добавлять к тексту), мы создаем файл
                    $str = "[{\"date\":\"".date('d-m-Y  H:m:s')."\",\"mark\":".$this->data['mark'].",\"name\":\"".$this->data['name']."\",\"minuses\":\"".$this->data['minuses']."\",\"pluses\":\"".$this->data['pluses']."\",\"text\":\"".$this->data['text']."\",\"email\":\"".$this->data['email']."\"},"."\r\n";
                    fwrite($fp, $str);
                    //$this->writeFile($this->_getDir().$this->data['productID'].'.json',$str);
                    $comment[0] = str_replace("[","",$comment[0]);
                    for($i=0; $i< count($comment); $i++)
                    {


                       $str =$comment[$i];
                       $res = fwrite($fp, $str);

                    }
                }



    				/*$str = '<div class="comment-box"><div class="comment-head"><div class="comment-name">'.$this->data['name'].'</div>';
    				if(isset($this->data['mark'])){
    				    for($i=1;$i<=5;$i++){
        				    if($this->data['mark']>=$i)
        				        $active = ' active';
        				    else
        				        $active = '';
        				    $str .='<div   class="reviews-add-stars-i-sm'.$active.'"></div>';
    				    }
    				}
    				$str .= '<div class="comment-date">'.date('d-m-Y  H:m:s').'</div>';
    				$str .= '</div>';

    				if(isset($this->data['pluses']) && ($this->data['pluses']!==''))
    				    $str .='<div class="comment-plus"><span class="comment-plus-head">Плюсы:</span> '.$this->data['pluses'].'</div>';
    				if(isset($this->data['minuses']) && ($this->data['minuses']!==''))
    				    $str .='<div class="comment-minus"><span class="comment-minus-head">Минусы:</span> '.$this->data['minuses'].'</div>';
    				$str .='<div class="comment-text"><span class="comment-text-head">Отзыв/Вопрос:</span> '.$this->data['text'].'</div>';

    				$str .= '</div>';
    				$str = $str."\r\n";*/

    				$this->_sendComment();

    }

    public function _getData(){
        foreach ($_POST  as $key => $value )
        {
            $data[$key] = iconv("UTF-8","windows-1251",strip_tags($_POST[$key]));
            $data[$key] = strip_tags($_POST[$key]);
            $data[$key] = preg_replace("/s(w+s)1/i", "$1", $data[$key]);     //удаление повторяющихся слов
            $data[$key] =  preg_replace("#( |\n|^)(http://)?[0-9a-z_.-/]+?[^@][0-9a-z_.-/]+\.[a-z]{2,4}#is", "", $data[$key]);    //удаление повторяющихся запятых
        }
        return $data;
    }
    public function _setDir($dir)
    {
        $this->dir = $dir;
    }

    public function _setEmail($email)
    {
        $this->email = $email;
    }

    public function _getDir()
    {
        return $this->dir;
    }
}


?>