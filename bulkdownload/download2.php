<?php
require_once('../config.php');
global $DB;
$dir_main = "C:/xampp/moodledata/filedir/"; 
$term = $_GET['term'];
$year = $_GET['year'];
$gradername = $_GET['gradername'];

$gradername = preg_replace('/\s+/', '_', $gradername);
$fileList = glob('source/*');
foreach($fileList as $filename)
{
    if(is_file($filename))
	{ 
		$filename_new = preg_replace('/\s+/', '_', $filename);
		rename($filename, $filename_new);
		$files_to_zip[] = $filename_new;
		unset($filename_new);
		
    }   
}

/* creates a compressed zip file */
function create_zip($files = array(),$destination = '',$overwrite = false) {
	//if the zip file already exists and overwrite is false, return false
	if(file_exists($destination) && !$overwrite) { return false; }
	//vars
	$valid_files = array();
	//if files were passed in...
	if(is_array($files)) {
		//cycle through each file
		foreach($files as $file) {
			//make sure the file exists
			if(file_exists($file)) {
				$valid_files[] = $file;
			}
		}
	}
	//if we have good files...
	if(count($valid_files)) {
		//create the archive
		$zip = new ZipArchive();
		if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
			return false;
		}
		//add the files
		foreach($valid_files as $file) {
			$zip->addFile($file,$file);
		}
		//debug
		//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
		
		//close the zip -- done!
		$zip->close();
		
		//check to make sure the file exists
		return file_exists($destination);
	}
	else
	{
		return false;
	}
}
/*$files_to_zip = array(
	'1.docx',
	'2.docx'
);*/
//if true, good; if false, zip creation failed
if(file_exists($gradername."_".$year."_T".$term.".zip"))
{
	unlink($gradername."_".$year."_T".$term.".zip");
}
$result = create_zip($files_to_zip,$gradername."_".$year."_T".$term.".zip");

if($result)
{
	foreach($files_to_zip as $key=>$val)
	{
		unlink($val);
	}
	header("Location: download3.php?term=".$term."&year=".$year."&gradername=".$gradername);
	exit();
}


