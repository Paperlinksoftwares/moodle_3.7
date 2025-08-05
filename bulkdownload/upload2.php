<?php
require_once('../config.php');
global $DB;
$path = $_GET['targetdir']."/source";

$fileList = scandir($path,1);
$k=0;
$filename_arr = array();
//echo '<pre>';
//print_r($fileList); die;
foreach($fileList as $key=>$filename)
{
	if($k<4)
	{
		if($filename!='.' && $filename!='..')
		{ 
			$filename_arr=explode("_",$filename);
			$sql_file = "SELECT * FROM {files} WHERE `id` = '".$filename_arr[0]."' AND `itemid` = '".$filename_arr[1]."' AND `userid` = '".$filename_arr[2]."' AND `filesize`!='0' AND `filearea` = 'feedback_files'";
			$list_all_files = $DB->get_record_sql($sql_file);
			
			$str1 = substr($list_all_files->contenthash,0,2);
		    $str2 = substr($list_all_files->contenthash,2,2);
		   
		    $source_file = $_GET['targetdir']."/source/".$filename;
		
		    $destination = 'C:/xampp/moodledata/filedir/' . $str1.'/'.$str2.'/'.$filename;
			copy($source_file,$destination);
			unlink('C:/xampp/moodledata/filedir/' . $str1.'/'.$str2.'/'.$list_all_files->contenthash);
		    rename('C:/xampp/moodledata/filedir/' . $str1.'/'.$str2.'/'.$filename,'C:/xampp/moodledata/filedir/' . $str1.'/'.$str2.'/'.$list_all_files->contenthash);

		}   
	}
	$k++;
}
header("Location: upload3.php?original=".$_GET['targetdir']."&subdir=source");
exit();