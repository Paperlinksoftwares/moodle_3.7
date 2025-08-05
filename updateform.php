<?php
require_once('config.php');
global $DB;
if($_GET['success']==1)
{	
	$u1 = $DB->execute("INSERT INTO `mdl_zoho_forms_track`(`id`,`form_name` , `form_id` , `userid`) VALUES (NULL,'".$_POST['form_name']."','".$_GET['form_id']."','".$_POST['studentid']."')");            
}