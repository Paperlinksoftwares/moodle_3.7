<?php
if(isset($_POST))
{
	require_once('../config.php');
	global $DB;

	$dir_main = "C:/xampp/moodledata/filedir/"; 

	for($i=0;$i<count($_POST['assid']); $i++)
	{
		
		$sql_itemid = "SELECT * FROM `mdl_assign_grades` WHERE `assignment` = '".$_POST['assid'][$i]."' AND `grader` = '".$_POST['graderid']."' AND `userid` = '".$_POST['userid'][$i]."'";
		$list_itemid = $DB->get_record_sql($sql_itemid);


		$sql_file = "SELECT * FROM {files} WHERE `itemid` = '".$list_itemid->id."' AND `userid` = '".$_POST['graderid']."' AND `filesize`!='0' AND `filearea` = 'feedback_files'";
		$list_all_files = $DB->get_records_sql($sql_file);

		foreach($list_all_files as $list_all_files)
		{
			$str1 = substr($list_all_files->contenthash,0,2);
			$str2 = substr($list_all_files->contenthash,2,2);

			$file = $dir_main.$str1."/".$str2."/".$list_all_files->contenthash; 
			$new_file = $list_all_files->id."_".$list_all_files->itemid."_".$_POST['graderid']."_".$list_all_files->filename;

			if(file_exists($file))
			{
				copy($file,"C:/xampp/htdocs/bulkdownload/source/".$new_file);
			}
			unset($new_file);
		}
	
	}
	header("Location: download2.php?term=".$_POST['term']."&year=".$_POST['year']."&gradername=".$_POST['gradername']);
	exit();
}