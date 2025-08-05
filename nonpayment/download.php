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
header("Content-Disposition: attachment; filename=students_restricted.xls");  
header("Pragma: no-cache");  
header("Expires: 0"); 

$columnHeader ='<table width="100%" border="1" cellspacing="0" cellpadding="4">
 <tr>
    <td bgcolor="#CCCCCC"><strong>SL No.</strong></td>
    <td bgcolor="#CCCCCC"><strong>Name | Email</strong></td>
    <td bgcolor="#CCCCCC"><strong>Type of Restriction</strong></td>  
    <td bgcolor="#CCCCCC"><strong>Date when Restricted</strong></td>
  </tr>';
  
$rowsval='';
if (mysqli_num_rows($sql_result)>0) {
	$cc=0;
	while ($row = mysqli_fetch_assoc($sql_result)) {
		$cc++;
	  
 if($row["type_of_restriction"]==1) { $msg = 'Notified of Restriction'; } else { $msg = 'Fully Restricted'; };

$rows = '<tr>
      <td>'.$cc.'</td>
    <td>'.$row["firstname"].' '.$row["firstname"].' | '.$row["email"].'</td>
    <td>'.$msg.'</td>
    <td>'.@date("l jS \of F Y h:i A", strtotime($row["date"])).'</td>
   
  </tr>';
  $rowsval = $rowsval.$rows;

unset($result_count);
unset($row_count);
unset($count);
unset($msg);
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