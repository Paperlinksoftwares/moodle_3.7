<?php
session_start();
error_reporting(0);
include("config.php");

if(isset($_GET['sql_param']) && $_GET['sql_param']!='')
{
$sql = base64_decode($_GET['sql_param']);

$res_total = mysqli_query($connection,$sql);
$total = mysqli_num_rows($res_total);
$sql_result = mysqli_query ($connection ,$sql ) or die ('request "Could not execute SQL query" '.$sql);
header("Content-type: application/octet-stream");  
header("Content-Disposition: attachment; filename=popalertusers.xls");  
header("Pragma: no-cache");  
header("Expires: 0"); 

$columnHeader ='<table width="100%" border="1" cellspacing="0" cellpadding="4">
 <tr>
    <td bgcolor="#CCCCCC"><strong>SL No.</strong></td>
    <td bgcolor="#CCCCCC"><strong>Name | Email</strong></td>
   
    
    <td bgcolor="#CCCCCC"><strong>Date</strong></td>
  </tr>';
  
$rowsval='';
if (mysqli_num_rows($sql_result)>0) {
	while ($row = mysqli_fetch_assoc($sql_result)) {
	  


$rows = '<tr>
      <td>'.$row["id"].'</td>
    <td>'.$row["firstname"].' '.$row["firstname"].' | '.$row["from_email"].'</td>
    
    <td>'.@date("l jS \of F Y h:i A", strtotime($row["date"])).'</td>
   
  </tr>';
  $rowsval = $rowsval.$rows;

unset($result_count);
unset($row_count);
unset($count);
	}
} else {

$rowsval = '<tr><td colspan="6">Sorry! No results found.</td></tr>';
	
}
echo $columnHeader . "\n" . $rowsval . "\n";  
}
else
	
{
	header("Location: search.php");
	exit();
}
?>