<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	//customer survey module

	if (!strcmp($sub, "survey"))
	{

		if (isset($_GET["save_successful"])) //show successful save confirmation message
		{
			$smarty->assign("configuration_saved", 1);
		}


		if (isset($_POST["save_voting"]) && isset($_POST["question"]) && isset($_POST["answers"])) // save new survey
		{
			if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
			{
				Redirect( "admin.php?dpt=modules&sub=survey&safemode=yes" );
			}
			$_POST = xStripSlashesGPC($_POST);

			$f = fopen("./cfg/survey.inc.php","w");
			fputs($f,"<?php\n");
			//record question and answer options
			fputs($f,"\t\$survey_question = '".str_replace(array('\\',"'"),array('\\\\',"\'"),$_POST["question"])."';\n\n");
			fputs($f,"\t\$survey_answers = array();\n");
			$answers = explode("\n",$_POST["answers"]);
			for ($i=0; $i<count($answers); $i++)
				fputs($f,"\t\$survey_answers[] = '".str_replace(array('\\',"'"),array('\\\\',"\'"),$answers[$i])."';\n");

			//reset results to 0
			fputs($f,"\n\t\$survey_results = array();\n");
			for ($i=0; $i<count($answers); $i++)
				fputs($f,"\t\$survey_results[] = 0;\n");

			fputs($f,"?>");
			fclose($f);

			Redirect("admin.php?dpt=modules&sub=survey&save_successful=yes");
		}

		if (isset($_GET["start_new_poll"])) //show new customer survey form
		{
			$smarty->assign("start_new_poll", "yes");
		}
		else //show existing survey results
		{
			include("./cfg/survey.inc.php");
			$smarty->hassign("survey_question", $survey_question );
			$smarty->hassign("survey_answers", $survey_answers);
			$smarty->hassign("survey_results", $survey_results);
			//get total voters count
			$voters_count = 0;
			for ($i=0; $i<count($survey_results); $i++)	$voters_count += $survey_results[$i];
			$smarty->assign("voters_count", $voters_count);
		}


		//set sub-department template
		$smarty->assign("admin_sub_dpt", "modules_survey.tpl.html");
	}

?>