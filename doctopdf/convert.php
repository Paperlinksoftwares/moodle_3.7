<?php
$originFilePath = 'C:\xampp\htdocs\doctopdf\test123.docx';
$outputDirPath  = 'C:\xampp\htdocs\doctopdf\test123.pdf';

$dir    = dirname($originFilePath);
$pdf    = $dir . DIRECTORY_SEPARATOR . basename($originFilePath, '.docx').'.pdf';
$ret = shell_exec("/Applications/LibreOffice.app/Contents/MacOS/soffice --headless --convert-to pdf --outdir . test123.docx");
// $ret will contain any errors
if (!file_exists($pdf)) {
    die("Conversion error: " . htmlentities($ret));
}
rename($pdf, $outputDirPath);

header("Content-type:application/pdf");
header("Content-Disposition:attachment;filename=newpdf.pdf");
readfile($outputDirPath);