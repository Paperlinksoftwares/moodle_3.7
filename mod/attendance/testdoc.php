<?php
header("Content-Type: application/vnd.ms-word");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("content-disposition: attachment;filename=Report.doc");
$html = file_get_contents('https://training.gov.au/Training/Details/BSBMKG550');
$tags = "<br>";
$test = strip_tags($page,$html);
$breaks = array("<br />","<br>","<br/>");  
$text = str_ireplace($breaks, "\r\n", $test);  
$text = iconv('UTF-8', 'ASCII//TRANSLIT',$text);
$handle = fopen("newdoc.doc", "w+");
fwrite($handle, $text);
fclose($handle);