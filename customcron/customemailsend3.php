<?php
require_once('../config.php');
require_once('../customfunctions.php');
include('class.phpmailer.php');
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->Host = "mail.tpg.com.au";
$mail->Port = '25';
$mail->Username = "accitpaul@tpg.com.au";
$mail->Password = "Kent0495";
$mail->SMTPDebug  = 1;
global $DB;
global $CFG;
$sql_user_logtime = "SELECT login as loginfirst , userid as uid , from_unixtime(login, '%m/%d/%Y') as logindate FROM {attendanceregister_session} as a WHERE 1 order by login ASC";
$list_user_logtime = $DB->get_records_sql($sql_user_logtime);   
$week_details = array();
foreach($list_user_logtime as $list_user_logtime)
{
    $d = $list_user_logtime->logindate;
    $u = $list_user_logtime->uid;
    $week_details[$d."|".$u] = getWeekDatesByDate($list_user_logtime->logindate); 
    unset($d);
    unset($u);
}
foreach($week_details as $key=>$val)
{
    $content_array = array();
    $explode_arr = explode("|",$key);
    $sql_logtime = "SELECT assign.userid as uid , u.email , u.firstname, u.lastname, (SUM(`duration`)/3600) as duration FROM {attendanceregister_session} as assign "
            . " JOIN {user} as u ON assign.userid = u.id "
            . " WHERE FROM_UNIXTIME(assign.login,'%Y-%m-%d') >=  '".$val[0]
            . "' AND FROM_UNIXTIME(assign.logout,'%Y-%m-%d')< '".$val[1]."' AND assign.userid = '".$explode_arr[1]."' LIMIT 0,100";
    $list_logtime = $DB->get_record_sql($sql_logtime); 
    $content_array['weekstartdate'] = $val[0];  
    $content_array['weekfinishdate'] = $val[1];  
    $content_array['duration'] = $list_logtime->duration;  
    if(intval($list_logtime->duration)<5)
    {
        $subject = "Less attendance online session";
        sendEmail($mail,$subject,$list_logtime->firstname." ".$list_logtime->lastname,'ACCIT Admin','debraj.paperlinksoftwares@gmail.com','info@accit.nsw.edu.au',$content_array);    
    }
    unset($content_array);
}

?>
