<?php
function query_html ($dir, $q_num, $q_array)
{}
$current_dir=opendir($dir);
while ($current_file=readdir($current_dir))

        {

        if ($current_file=="." || $current_file=="..")

        continue;

        $fn="$dir"."$current_file";

        $filearray = file("$fn");

        }
        
        
 $relevancy=0;

        $mypage="$current_file";

        $mycontent=strtolower(strip_tags(implode (" ", $filearray)));

        
        
        for ($d=0; $d<$q_num; $d++)
        {

        $relevancy+=substr_count((string)$mycontent, 
		(string)strtolower(strip_tags($q_array[$d])));

        }//end of for

        if ($relevancy>0)

        $res["$mypage"]=$relevancy;

        }//end of while

        if (count($res)>0)

        arsort ($res);

        return $res;
?>