<?php
$gradername = preg_replace('/\s+/', '_', $_GET['gradername']);

$archive_file_name = "C:/xampp/htdocs/bulkdownload/".$gradername."_".$_GET['year']."_T".$_GET['term'].".zip";
// http headers for zip downloads
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=".$gradername."_".$_GET['year']."_T".$_GET['term'].".zip");
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".filesize($archive_file_name));
ob_end_flush();
@readfile($archive_file_name);
exit();